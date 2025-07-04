<?php
global $con, $params;

// Loop over all plants and import each plant separately
foreach ($params['PLANTS'] as $name => $plant) {
    $lastImportDate = getLastImportDateForPlant($name, $con);
    $files_to_import = getFilesToImport($name, $lastImportDate, "", "Y-m-d");
    $importDateFormat = $plant['importDateFormat'];
    addDebugInfo("sunny_webbox_csv: LastStartImportDate: $lastImportDate - ImportFilesCount: " . count($files_to_import));

    foreach ($files_to_import as $import_filename) {
        addDebugInfo("sunny_webbox_csv: importFile: $import_filename");
        $importData = readImportFile($import_filename, 0);
        $dbValues = mapLinesToDBValues($importData, $name, $lastImportDate, $importDateFormat);
        prepareAndInsertData($dbValues, $con);
    }
}

function mapLinesToDBValues(array $lines, string $name, $lastImportDate, $importDateFormat): array
{
    $CurrentWattColumn = 23; // Column index for Watt value in CSV (0-based index)
    $totalkWhColumn = 15; // Column index for total kWh value in CSV (0-based index)
    global $params;
    $dbValues = array();
    $minkWhCounter = PHP_INT_MAX; // Initialize to a very high value to ensure we get the minimum kWh counter
    $lineCounter = 0;
    foreach ($lines as $line) {
        $lineCounter++;
        if ($lineCounter == 8) {
            $importDateFormat = parseImportDateTimeFormat($line, $importDateFormat);
        }
        if ($lineCounter > 8) {
            $lineValues = explode(";", $line);
            if (count($lineValues) > 2) {
                // first data row get initial $minkWhCounter value from first line
                $currentkWhCounter = str_replace(',', '.', $lineValues[$totalkWhColumn]);
                if (strlen($currentkWhCounter) > 0 && is_numeric($currentkWhCounter)) {
                    if ($currentkWhCounter < $minkWhCounter) {
                        $minkWhCounter = $currentkWhCounter; // set initial minimum kWh counter
                    }
                } else {
                    continue; // skip this line if kWh value is invalid
                }
                $dateFromDB = $lineValues[0];
                // convert to UTC if parameter "importLocalDateAsUTC" is set to true otherwise it will remain localDate
                if ($params['importLocalDateAsUTC']) {
                    $convertedDate = convertLocalDateTime($dateFromDB, $importDateFormat, true); // in UTC now
                } else {
                    $convertedDate = $dateFromDB;  // keep local time
                }
                $convertedTimeStamp = convertToUnixTimestamp($convertedDate);
                $currentTimeStamp = date("Y-m-d H:i:s", $convertedTimeStamp);
                $cummulatedkWh = round($currentkWhCounter - $minkWhCounter, 3);
                $currentWatt = 0;
                $currentWattStr = trim(str_replace(',', '.', $lineValues[$CurrentWattColumn]));
                if (strlen($currentWattStr) > 0 && is_numeric($currentWattStr)) {
                    $currentWatt = $currentWattStr;
                }
                // insert only new data and value > 0
                if ($currentWatt > 0 && ($currentTimeStamp != "") && (strtotime($currentTimeStamp) > strtotime($lastImportDate))) {
                    $dbValues[] = array('name' => $name, 'timestamp' => $currentTimeStamp, 'watt' => $currentWatt, 'cummulatedkWh' => round($cummulatedkWh, 3));
                }
            }
        }
    }
    addDebugInfo("sunny_webbox_csv: mapLinesToDBValues: ImportedLines: " . count($lines) . " - DataRows: " . count($dbValues));
    return $dbValues;
}
