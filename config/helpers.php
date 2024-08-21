<?php
if (!function_exists('filterDataBeforeSave')) {
    function filterDataBeforeSave($input)
    {
        $input = array_map(function ($input) {
            return trim(strip_tags($input));
        }, $input);
        return $input;
    }
}

if (!function_exists('excelDateToDate')) {
    function excelDateToDate($input)
    {
        $dateString = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($input)->getTimestamp();
        // dd(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valueN[2][2])->getTimestamp());
        // dd();
        return date('Y-m-d', $dateString);
    }
}

if (!function_exists('arrayKeyValueFlip')) {
    function arrayKeyValueFlip($array)
    {
        $headers = $array[0];
        unset($array[0]);
        $array = array_values($array);
        $newArr = array();
        foreach ($array as $key => $value) {
            $newSingleArr = [];
            foreach ($value as $key2 => $value2) {
                $newSingleArr[$headers[$key2]] = $value2;
            }
            $newArr[] = $newSingleArr;
        }
        return $newArr;
    }
}
