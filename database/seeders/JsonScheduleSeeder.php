<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\ClassRoom;
use App\Models\Discipline;
use App\Models\Group;
use App\Models\Institute;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class JsonScheduleSeeder extends Seeder
{
    private array $timePartitionMap = [
        '8.30-10.00'   => 1,
        '10.10-11.40'  => 2,
        '12.00-13.30'  => 3,
        '13.40-15.10'  => 4,
        '15.20-16.50'  => 5,
        '17.00-18.30'  => 6,
    ];

    private array $timeSlotCache  = [];
    private array $classRoomCache = [];
    private int   $defaultBuildingId = 1;

    public function run(): void
    {
        $building = Building::first();
        if ($building) {
            $this->defaultBuildingId = $building->id;
        }

        $jsonDir = base_path('result/json');
        $files   = glob($jsonDir . '/*.json');
        if (!$files) {
            $this->command->warn('JSON files not found in result/json/');
            return;
        }

        $total = count($files);
        $done  = 0;
        foreach ($files as $file) {
            if (str_starts_with(basename($file), 'politika')) {
                continue;
            }
            try {
                $this->processFile($file);
            } catch (\Throwable $e) {
                $this->command->warn('Skip ' . basename($file) . ': ' . $e->getMessage());
            }
            $done++;
            if ($done % 20 === 0) {
                $this->command->info("Processed {$done}/{$total}...");
            }
        }
        $this->command->info("Done: {$done}/{$total} files.");
    }

    // -------------------------------------------------------------------------

    private function processFile(string $filePath): void
    {
        $data = json_decode(file_get_contents($filePath), true);
        if (!is_array($data) || count($data) < 2) {
            return;
        }

        $keys    = array_keys($data[0]);
        $dayKey  = $keys[0];
        $timeKey = $keys[1] ?? null;
        if (!$timeKey) {
            return;
        }

        // Find the header row (column with "Часы") and collect metadata above it
        $headerIdx = -1;
        $metaText  = ($dayKey !== 'column_0') ? $dayKey : ''; // Format 1 keeps meta in key name

        foreach ($data as $idx => $row) {
            if (($row[$timeKey] ?? null) === 'Часы') {
                $headerIdx = $idx;
                break;
            }
            // Format 2: metadata is in pre-header rows
            if ($dayKey === 'column_0') {
                $val = trim((string) ($row[$dayKey] ?? ''));
                if ($val !== '') {
                    $metaText .= ' ' . $val;
                }
            }
        }

        if ($headerIdx === -1) {
            return;
        }

        $headerRow    = $data[$headerIdx];
        $allColKeys   = array_keys($headerRow);
        $groupColKeys = array_slice($allColKeys, 2); // everything after day + time columns

        // Collect group columns that have a non-empty header
        $rawGroups = [];
        foreach ($groupColKeys as $colKey) {
            $val = trim((string) ($headerRow[$colKey] ?? ''));
            if ($val !== '') {
                $rawGroups[$colKey] = $val;
            }
        }
        if (empty($rawGroups)) {
            return;
        }

        $instituteName = $this->extractInstitute($metaText);
        $defaultCourse = $this->extractCourseFromText($metaText);
        $educationForm = $this->detectEducationForm(basename($filePath) . ' ' . $metaText);

        $institute = Institute::firstOrCreate(['name' => $instituteName]);

        // Create or find group models
        $groupModels = [];
        foreach ($rawGroups as $colKey => $rawName) {
            $groupName = $this->normalizeGroupName($rawName);
            $course    = $this->extractCourseFromGroupCode($groupName) ?? $defaultCourse;
            $groupModels[$colKey] = Group::firstOrCreate(
                ['name' => $groupName],
                [
                    'course'         => $course,
                    'education_form' => $educationForm,
                    'institute_id'   => $institute->id,
                ]
            );
        }

        // Process data rows after header
        $rows          = array_slice($data, $headerIdx + 1);
        $count         = count($rows);
        $currentDay    = null;
        $currentPartId = null;

        for ($i = 0; $i < $count; $i++) {
            $row     = $rows[$i];
            $dayVal  = $row[$dayKey] ?? null;
            $timeVal = $row[$timeKey] ?? null;

            // Update current day when the day cell is non-null/non-empty
            if ($dayVal !== null && $dayVal !== '') {
                $parsed = $this->parseDay((string) $dayVal);
                if ($parsed !== null) {
                    $currentDay = $parsed;
                }
            }

            if ($currentDay === null) {
                continue;
            }

            $hasTime = ($timeVal !== null && $timeVal !== '');
            $isCont  = (!$hasTime && $dayVal === null); // continuation = lower-week row

            if ($hasTime) {
                $currentPartId = $this->parseTimePartition((string) $timeVal);
                if ($currentPartId === null) {
                    continue;
                }

                // Look ahead: next row is a continuation → current = upper week only
                $next              = $rows[$i + 1] ?? null;
                $nextIsContinuation = $next !== null
                    && ($next[$timeKey] ?? null) === null
                    && ($next[$dayKey] ?? null) === null;

                if ($nextIsContinuation) {
                    $this->saveLessons($row, $groupColKeys, $groupModels, $currentDay, $currentPartId, 'upper');
                } else {
                    // Single row for this slot → lesson repeats every week
                    $this->saveLessons($row, $groupColKeys, $groupModels, $currentDay, $currentPartId, 'upper');
                    $this->saveLessons($row, $groupColKeys, $groupModels, $currentDay, $currentPartId, 'lower');
                }
            } elseif ($isCont && $currentPartId !== null) {
                $this->saveLessons($row, $groupColKeys, $groupModels, $currentDay, $currentPartId, 'lower');
            }
        }
    }

    // -------------------------------------------------------------------------

    private function saveLessons(
        array  $row,
        array  $groupColKeys,
        array  $groupModels,
        int    $day,
        int    $partitionId,
        string $weekType
    ): void {
        $timeSlot = $this->getTimeSlot($weekType, $day, $partitionId);
        if (!$timeSlot) {
            return;
        }

        foreach ($groupColKeys as $colKey) {
            if (!isset($groupModels[$colKey])) {
                continue;
            }
            $lessonText = trim((string) ($row[$colKey] ?? ''));
            if ($lessonText === '' || $lessonText === '* * *') {
                continue;
            }

            $parsed = $this->parseLessonText($lessonText);
            if ($parsed === null) {
                continue;
            }

            $discipline = Discipline::firstOrCreate(['name' => $parsed['discipline']]);
            $teacher    = Teacher::firstOrCreate(['name' => $parsed['teacher']]);
            $discipline->teachers()->syncWithoutDetaching([$teacher->id]);
            $classRoom = $this->getClassRoom($parsed['room']);

            Lesson::firstOrCreate([
                'teacher_id'    => $teacher->id,
                'class_room_id' => $classRoom->id,
                'time_slot_id'  => $timeSlot->id,
                'discipline_id' => $discipline->id,
                'group_id'      => $groupModels[$colKey]->id,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Parsing helpers
    // -------------------------------------------------------------------------

    private function parseDay(string $value): ?int
    {
        $clean    = preg_replace('/\s+/u', '', $value);
        $reversed = implode('', array_reverse(mb_str_split($clean)));

        foreach ([$reversed, $clean] as $str) {
            $lower = mb_strtolower($str);
            if (mb_strpos($lower, 'понед') !== false) return 1;
            if (mb_strpos($lower, 'вторн') !== false) return 2;
            if (mb_strpos($lower, 'сред')  !== false) return 3;
            if (mb_strpos($lower, 'четв')  !== false) return 4;
            if (mb_strpos($lower, 'пятн')  !== false) return 5;
            if (mb_strpos($lower, 'суб')   !== false) return 6;
        }

        return null;
    }

    private function parseTimePartition(string $time): ?int
    {
        return $this->timePartitionMap[trim($time)] ?? null;
    }

    private function parseLessonText(string $text): ?array
    {
        $text = trim(str_replace(["\n", "\r"], ' ', $text));
        $text = preg_replace('/\s+/', ' ', $text);

        if ($text === '' || $text === '* * *') {
            return null;
        }
        if (preg_match('/кураторский\s+час|единый\s+кураторский/iu', $text)) {
            return null;
        }

        // Strip leading lesson type (лек., пр.з., лаб.з., лб.з., etc.)
        $stripped = preg_replace(
            '/^(?:лек(?:ция)?\.?|лаб\.?\s*з\.?|лб\.?\s*з\.?|пр\.?\s*з\.?)\s*/iu',
            '',
            $text
        );
        $stripped = trim($stripped);
        if ($stripped === '') {
            $stripped = $text;
        }

        // Find teacher surname+initials: Фамилия И.О.
        preg_match_all(
            '/\b([А-ЯЁ][а-яё]+(?:-[А-ЯЁ][а-яё]+)?\s+[А-ЯЁ]\.[А-ЯЁ]\.)\b/u',
            $stripped,
            $teacherMatches
        );

        // Find 3–4 digit room numbers
        preg_match_all('/\b(\d{3,4}[а-яА-ЯЁё]?)\b/u', $stripped, $roomMatches);

        // Discipline = text before first teacher title keyword or first teacher name
        $discipline = $stripped;
        if (preg_match(
            '/^(.*?)\s+(?:доц\.|проф\.|ст\.?\s*пр\.|асс\.|профессор\b|доцент\b|ст\.пр\b)/iu',
            $stripped,
            $m
        )) {
            $discipline = trim($m[1]);
        } elseif (!empty($teacherMatches[1])) {
            $pos = mb_strpos($stripped, $teacherMatches[1][0]);
            if ($pos !== false && $pos > 3) {
                $discipline = trim(mb_substr($stripped, 0, $pos));
            }
        }

        // Clean trailing room / building references from discipline
        $discipline = preg_replace('/\s+\d{3,4}[а-яА-ЯЁё]?\s*$/u', '', $discipline);
        $discipline = preg_replace('/\s+(?:УК|ук)\s*[№#]?\s*\d+.*$/u', '', $discipline);
        $discipline = trim($discipline);

        if ($discipline === '') {
            $discipline = $stripped;
        }

        // Normalize: trim and lower-case discipline
        $discipline = mb_strtolower(trim($discipline));

        $teacher = !empty($teacherMatches[1]) ? trim($teacherMatches[1][0]) : 'Не указан';

        // Determine room
        $room = '0';
        if (preg_match('/\bсок\b/iu', $stripped)) {
            $room = 'сок';
        } elseif (preg_match('/ауд\.?\s*(?:Большая|большая)/iu', $stripped)) {
            $room = 'Большая';
        } elseif (!empty($roomMatches[1])) {
            $room = $roomMatches[1][0];
        }

        return [
            'discipline' => $discipline,
            'teacher'    => $teacher,
            'room'       => $room,
        ];
    }

    // -------------------------------------------------------------------------
    // Entity helpers
    // -------------------------------------------------------------------------

    private function getTimeSlot(string $weekType, int $day, int $partitionId): ?TimeSlot
    {
        $key = "{$weekType}_{$day}_{$partitionId}";
        if (!array_key_exists($key, $this->timeSlotCache)) {
            $this->timeSlotCache[$key] = TimeSlot::where([
                'week_type'        => $weekType,
                'day'              => $day,
                'day_partition_id' => $partitionId,
            ])->first();
        }
        return $this->timeSlotCache[$key];
    }

    private function getClassRoom(string $roomNumber): ClassRoom
    {
        if (!array_key_exists($roomNumber, $this->classRoomCache)) {
            $room = ClassRoom::where('number', $roomNumber)->first();
            if (!$room) {
                $room = ClassRoom::firstOrCreate([
                    'number'      => $roomNumber,
                    'building_id' => $this->defaultBuildingId,
                ]);
            }
            $this->classRoomCache[$roomNumber] = $room;
        }
        return $this->classRoomCache[$roomNumber];
    }

    // -------------------------------------------------------------------------
    // Metadata extraction
    // -------------------------------------------------------------------------

    private function extractInstitute(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        if (preg_match('/институт[а]?\s+([^0-9\n]+?)(?:\s+\d|\s*$)/iu', $text, $m)) {
            return 'Институт ' . trim(rtrim($m[1], ' ,'));
        }
        return 'Неизвестный институт';
    }

    private function extractCourseFromText(string $text): int
    {
        if (preg_match('/(\d)\s+курс/iu', $text, $m)) {
            return (int) $m[1];
        }
        return 1;
    }

    private function extractCourseFromGroupCode(string $name): ?int
    {
        // Pattern: group code ends in NXX where N = course digit (101, 201, 301 …)
        if (preg_match('/[-\s]([1-5])\d{2}\b/u', $name, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    private function normalizeGroupName(string $name): string
    {
        // Remove date suffixes like "с 02.02.2026" or "02.02.2026"
        $name = preg_replace('/\s+с\s+\d{2}\.\d{2}\.?\d{0,4}\b/u', '', $name);
        $name = preg_replace('/\s+\d{2}\.\d{2}\.?\d{0,4}\b/u', '', $name);
        // Fix accidental double dashes
        $name = preg_replace('/--+/', '-', $name);
        return trim($name);
    }

    private function detectEducationForm(string $text): string
    {
        $lower = mb_strtolower($text);
        if (
            preg_match('/\bоз\b/u', $lower) ||
            mb_strpos($lower, 'заочн') !== false
        ) {
            return 'extramural';
        }
        return 'intramural';
    }
}
