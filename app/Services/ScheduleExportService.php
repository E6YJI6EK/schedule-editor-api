<?php

namespace App\Services;

use App\Enums\WeekType;
use App\Models\Lesson;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleExportService
{
    private const DAYS = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
    ];

    private const TIMES = [
        1 => '08:30–10:00',
        2 => '10:10–11:40',
        3 => '12:00–13:30',
        4 => '13:40–15:10',
        5 => '15:20–16:50',
        6 => '17:00–18:30',
    ];

    public function buildScheduleMatrix(array $groupIds, WeekType $weekType): array
    {
        $lessons = Lesson::whereIn('group_id', $groupIds)
            ->whereHas('timeSlot', fn($q) => $q->where('week_type', $weekType))
            ->with(['teacher', 'classRoom.building', 'timeSlot.dayPartition', 'discipline', 'group'])
            ->get();

        $matrix = [];
        foreach ($lessons as $lesson) {
            $day = $lesson->timeSlot->day->value;
            $partitionId = $lesson->timeSlot->dayPartition?->id;
            $groupId = $lesson->group?->id;

            if (!$partitionId || !$groupId) {
                continue;
            }

            $matrix[$day][$partitionId][$groupId] = [
                'subject'  => $lesson->discipline?->name ?? '',
                'teacher'  => $lesson->teacher?->name ?? '',
                'room'     => $lesson->classRoom?->number ?? '',
                'building' => $lesson->classRoom?->building?->short_name ?? '',
            ];
        }

        return $matrix;
    }

    public function getGroupNames(array $groupIds): array
    {
        $all = Lesson::whereIn('group_id', $groupIds)
            ->with('group')
            ->get()
            ->pluck('group')
            ->filter()
            ->unique('id')
            ->keyBy('id');

        return collect($groupIds)
            ->map(fn($id) => $all->get($id)?->name)
            ->filter()
            ->values()
            ->toArray();
    }

    public function exportExcel(array $groupIds): string
    {
        $groups = $this->getGroupNames($groupIds);
        $upperMatrix = $this->buildScheduleMatrix($groupIds, WeekType::Upper);
        $lowerMatrix = $this->buildScheduleMatrix($groupIds, WeekType::Lower);

        $spreadsheet = new Spreadsheet();

        $this->fillSheet(
            $spreadsheet->getActiveSheet()->setTitle('Верхняя неделя'),
            $upperMatrix,
            $groups,
            $groupIds
        );

        $this->fillSheet(
            $spreadsheet->createSheet()->setTitle('Нижняя неделя'),
            $lowerMatrix,
            $groups,
            $groupIds
        );

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $path = tempnam(sys_get_temp_dir(), 'schedule_') . '.xlsx';
        $writer->save($path);

        return $path;
    }

    private function col(int $index): string
    {
        return Coordinate::stringFromColumnIndex($index);
    }

    private function range(int $c1, int $r1, int $c2, int $r2): string
    {
        return $this->col($c1) . $r1 . ':' . $this->col($c2) . $r2;
    }

    private function fillSheet($sheet, array $matrix, array $groups, array $groupIds): void
    {
        $groupCount = count($groups);
        $totalCols = 1 + 6 * $groupCount;

        // --- Header row 1: days ---
        $sheet->setCellValue([1, 1], 'Время');
        $col = 2;
        foreach (self::DAYS as $dayName) {
            $sheet->setCellValue([$col, 1], $dayName);
            if ($groupCount > 1) {
                $sheet->mergeCells($this->range($col, 1, $col + $groupCount - 1, 1));
            }
            $col += $groupCount;
        }

        // --- Header row 2: group names ---
        $sheet->setCellValue([1, 2], '');
        $col = 2;
        for ($d = 0; $d < 6; $d++) {
            foreach ($groups as $groupName) {
                $sheet->setCellValue([$col, 2], $groupName);
                $col++;
            }
        }

        // --- Data rows ---
        $row = 3;
        foreach (self::TIMES as $partitionId => $time) {
            $sheet->setCellValue([1, $row], $time);
            $col = 2;
            foreach (array_keys(self::DAYS) as $dayNumber) {
                foreach ($groupIds as $groupId) {
                    $cell = $matrix[$dayNumber][$partitionId][$groupId] ?? null;
                    if ($cell && $cell['subject']) {
                        $text = implode("\n", array_filter([
                            $cell['subject'],
                            $cell['teacher'],
                            trim("{$cell['room']} ({$cell['building']})"),
                        ]));
                        $sheet->setCellValue([$col, $row], $text);
                        $sheet->getStyle($this->col($col) . $row)
                            ->getAlignment()->setWrapText(true);
                    }
                    $col++;
                }
            }
            $row++;
        }

        // --- Column widths ---
        $sheet->getColumnDimension($this->col(1))->setWidth(16);
        for ($c = 2; $c <= $totalCols; $c++) {
            $sheet->getColumnDimension($this->col($c))->setWidth(22);
        }

        // --- Row heights ---
        for ($r = 3; $r < $row; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(50);
        }

        // --- Styles: header ---
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '428BCA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($this->range(1, 1, $totalCols, 2))->applyFromArray($headerStyle);

        // --- Styles: time column ---
        $sheet->getStyle($this->range(1, 1, 1, $row - 1))->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // --- Styles: data cells ---
        if ($row > 3) {
            $sheet->getStyle($this->range(2, 3, $totalCols, $row - 1))->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
    }

    public function buildViewData(array $groupIds): array
    {
        return [
            'groups'       => $this->getGroupNames($groupIds),
            'groupIds'     => $groupIds,
            'days'         => self::DAYS,
            'times'        => self::TIMES,
            'upperMatrix'  => $this->buildScheduleMatrix($groupIds, WeekType::Upper),
            'lowerMatrix'  => $this->buildScheduleMatrix($groupIds, WeekType::Lower),
        ];
    }
}
