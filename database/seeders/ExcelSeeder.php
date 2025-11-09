<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Seeder;
use App\Models\SheetName;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Discipline;
use App\Enums\EducationForm;
class ExcelSeeder extends Seeder
{

    private function readSheetData($worksheet)
    {
        $educationalFormCellAddress = 'B' . 2;
        $educationalFormCellValue = $worksheet->getCell($educationalFormCellAddress)->getValue();
        $highestRow = $worksheet->getHighestRow() - 2;

        $data = [];
        $courses = [];

        for ($row = 9; $row <= $highestRow; $row++) {
            $courseCellValue = $worksheet->getCell('A' . $row)->getValue();
            $disciplineCellValue = $worksheet->getCell('B' . $row)->getValue();
            $teacherCellValue = $worksheet->getCell('C' . $row)->getValue();

            if (!empty($courseCellValue) && !in_array($courseCellValue, $courses)) {
                $courses[] = $courseCellValue;
            }

            if (!empty($disciplineCellValue) && !empty($teacherCellValue)) {
                $pair = [$disciplineCellValue, $teacherCellValue];

                $exists = false;
                foreach ($data as $existingPair) {
                    if ($existingPair[0] === $pair[0] && $existingPair[1] === $pair[1]) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $data[] = $pair;
                }
            }
        }

        return [
            'educational_form' => $educationalFormCellValue == 'оч' ? EducationForm::Intramural : EducationForm::Extramural,
            'disciplines_teachers' => $data,
            'courses' => $courses
        ];
    }
    public function run()
    {
        $excelFile = base_path('schedule.xlsm');


        if (!file_exists($excelFile)) {
            $this->command->error("Excel файл не найден: {$excelFile}");
            return;
        }

        try {
            $reader = IOFactory::createReaderForFile($excelFile);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($excelFile);

            $sheetNames = $spreadsheet->getSheetNames();

            foreach ($sheetNames as $index => $sheetName) {
                $worksheet = $spreadsheet->getSheetByName($sheetName);

                if (!$worksheet) {
                    $this->command->error("Лист '{$sheetName}' не найден!");
                    return;
                }

                $data = $this->readSheetData($worksheet);

                $institute = Institute::firstOrCreate([
                    "name" => 'Сгау',
                ]);

                foreach ($data['courses'] as $course) {
                    Group::firstOrCreate([
                        'name' => $sheetName . ' - курс ' . $course,
                        'course' => $course,
                    ], [
                        'institute_id' => $institute->id,
                        'education_form' => $data['educational_form'],
                    ]);
                }

                foreach ($data['disciplines_teachers'] as $key => $value) {
                    $discipline = Discipline::firstOrCreate(['name' => $value[0]]);

                    $teacher = Teacher::firstOrCreate(['name' => $value[1]]);

                    $discipline->teachers()->syncWithoutDetaching([$teacher->id]);
                }
            }
        } catch (\Exception $e) {
            $this->command->error("Ошибка при чтении Excel файла: " . $e->getMessage());
        }
    }
}