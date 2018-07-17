<?php
namespace App\Api\Tools\Excel;

use App\Api\Constants\LogConstants;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class SubmissionExcelFile
{
/**
     * @param array $propertyLabels
     * @param array $submissions
     * @param string $filename
 */
    public function export($propertyLabels, $submissions, $filename = 'Form submission')
    {
        $firstRow = collect(['No.', 'Submission Date'])->merge($propertyLabels)->all();

        $valueBinder = new SubmissionExcelValueBinder;

        /** @noinspection PhpUndefinedMethodInspection */
        Excel::setValueBinder($valueBinder);

        /** @noinspection PhpUndefinedMethodInspection */
        $content = Excel::create($filename, function ($excel) use ($filename, $firstRow, $submissions) {
            /** @noinspection PhpUndefinedMethodInspection */
            $excel->setTitle($filename)
                ->setCreator('QUIQ CMS')
                ->setCompany('QUO-Global');

            /** @noinspection PhpUndefinedMethodInspection */
            $excel->sheet('report', function ($sheet) use ($excel, $firstRow, $submissions) {
                /** @noinspection PhpUndefinedMethodInspection */
                $sheet->setAutoSize(true);
                /** @noinspection PhpUndefinedMethodInspection */
                $sheet->freezeFirstRow();
                /** @noinspection PhpUndefinedMethodInspection */
                $sheet->row(1, $firstRow);
                /** @noinspection PhpUndefinedMethodInspection */
                $sheet->row(1, function ($row) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $row->setFontWeight('bold');
                    /** @noinspection PhpUndefinedMethodInspection */
                    $row->setAlignment(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $row->setValignment(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                });

                if ( ! empty($submissions)) {
                    foreach ($submissions as $index => $submission) {
                            $no = $index + 1;
                        $rowIndex = $index + 2;
                        /** @var null|Carbon $submissionDate */
                        $submissionDate = array_key_exists(LogConstants::EXCEL_SUBMISSION_DATE, $submission)
                            ? $submission[LogConstants::EXCEL_SUBMISSION_DATE]
                            : null;
                        $rest = collect($submission)->except(LogConstants::EXCEL_SUBMISSION_DATE)->values()->all();

                        if ($submissionDate) {
                            $submissionDate = $submissionDate->format('Y-m-d H:i:s T');
                        }

                            $rowData = collect([$no, $submissionDate])->merge($rest)->all();

                        /** @noinspection PhpUndefinedMethodInspection */
                        $sheet->row($rowIndex, $rowData);

                        /** @noinspection PhpUndefinedMethodInspection */
                        $sheet->row($rowIndex, function ($row) {
                            /** @noinspection PhpUndefinedMethodInspection */
                            $row->setAlignment(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        });
                    }
                }
            });

            /** @noinspection PhpUndefinedMethodInspection */
            $excel->setActiveSheetIndex(0);
        })->string('xlsx');

        /** @noinspection PhpUndefinedMethodInspection */
        Excel::resetValueBinder();

        return $content;
    }

    /**
     * Return excel column from number
     *
     * @param $num
     * @return string
     */
    private function getExcelColumnFromNumber($num)
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getExcelColumnFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }
}
