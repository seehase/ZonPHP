<?php
global $con, $params;

// Loop over all plants and import each plant separately
foreach ($params['PLANTS'] as $name => $plant) {
    $lastImportDate = getLastImportDateForPlant($name, $con);
    $files_to_import = getFilesToImport($name, $lastImportDate, $plant['importPrefix']);

    foreach ($files_to_import as $import_filename) {
        $importData = readImportFile($import_filename, 10);
        $dbValues = mapLinesToDBValues($importData, $name, $lastImportDate);
        prepareAndInsertData($dbValues, $con);
    }
}

function mapLinesToDBValues(array $lines, string $name, $lastImportDate): array
{
    global $params;
    $dbValues = array();
    $minkWhCounter = 0.0;
    if (count($lines) > 0) {
        // get initial counter value from first line
        $lineValues = explode(";", $lines[0]);
        $minkWhCounter = str_replace(',', '.', $lineValues[1]);
    }
    foreach ($lines as $line) {
        $lineValues = explode(";", $line);
        if (count($lineValues) > 2) {
            $dateFromDB = $lineValues[0];
            // convert to UTC if parameter "importLocalDateAsUTC" is set to true otherwise it will remain localDate
            $convertedDate = convertLocalDateTime($dateFromDB, $params['importLocalDateAsUTC']);
            $convertedTimeStamp = convertToUnixTimestamp($convertedDate);
            $currentTimeStamp = date("Y-m-d H:i:s", $convertedTimeStamp);
            $currentkWhCounter = str_replace(',', '.', $lineValues[1]);
            $cummulatedkWh = number_format($currentkWhCounter - $minkWhCounter, 3);
            $currentWatt = str_replace(',', '.', $lineValues[2]) * 1000;

            // insert only new data and value > 0
            if ($currentWatt > 0 && ($currentTimeStamp != "") && (strtotime($currentTimeStamp) > strtotime($lastImportDate))) {
                $dbValues[] = array('name' => $name, 'timestamp' => $currentTimeStamp, 'watt' => $currentWatt, 'cummulatedkWh' => number_format($cummulatedkWh, 3));
            }
        }
    }
    return $dbValues;
}
