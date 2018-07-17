<?php
namespace App\Api\Tools\Excel;

use PHPExcel_Cell;

class SubmissionExcelValueBinder extends \PHPExcel_Cell_DefaultValueBinder implements \PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, \PHPExcel_Cell_DataType::TYPE_NUMERIC);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}