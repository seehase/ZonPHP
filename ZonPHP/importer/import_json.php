<?php
global $con, $params;

// Loop over all plants and import each plant separately
foreach ($params['PLANTS'] as $name => $plant) {
    $lastImportDate = getLastImportDateForPlant($name, $con);
    $files_to_import = getFilesToImport($name, $lastImportDate, $plant['importPrefix'], "ymd", ".js");
    $importDateFormat = $plant['importDateFormat'];
    addDebugInfo("sunny_explorer: LastStartImportDate: $lastImportDate - ImportFilesCount: " . count($files_to_import));

    foreach ($files_to_import as $import_filename) {
        addDebugInfo("sunny_explorer: importFile: $import_filename");
        $importData = readImportFile($import_filename, 0);
        $dbValues = mapLinesToDBValues($importData, $name, $lastImportDate, $importDateFormat);
        prepareAndInsertData($dbValues, $con);
    }
}

function mapLinesToDBValues(array $lines, string $name, $lastImportDate, $importDateFormat): array
{
    global $params;
    $dbValues = array();
    $lineCounter = 0;
    foreach ($lines as $line) {
        $lineCounter++;
        if ($lineCounter > 1) {
            $val1 = substr($line, 9, -1);
            $valdate = substr($val1, 0, 17);
            $valValues = substr($val1, 18);
            $lineValues = explode(";", $valValues);
            if (count($lineValues) > 4) {

                // convert date from format 'd.m.y H:i:s' -> 'Y-m-d H:i:s'
                $newDateTime = DateTime::createFromFormat('d.m.y H:i:s', $valdate);
                $formattedDate = $newDateTime->format('Y-m-d H:i:s');
                $dateFromDB = $formattedDate;

                // convert to UTC if parameter "importLocalDateAsUTC" is set to true otherwise it will remain localDate
                if ($params['importLocalDateAsUTC']) {
                    $convertedDate = convertLocalDateTime($dateFromDB, $importDateFormat, true); // in UTC now
                } else {
                    $convertedDate = $dateFromDB;  // keep local time
                }
                $convertedTimeStamp = convertToUnixTimestamp($convertedDate);
                $currentTimeStamp = date("Y-m-d H:i:s", $convertedTimeStamp);

                $currentkWhCounter = str_replace(',', '.', $lineValues[2]);
                $cummulatedkWh = $currentkWhCounter / 1000;
                $currentWatt = 0;
                $currentWattStr = trim(str_replace(',', '.', $lineValues[0]));
                if (strlen($currentWattStr) > 0 && is_numeric($currentWattStr)) {
                    $currentWatt = $currentWattStr * 1;
                }
                // insert only new data and value > 0
                if ($currentWatt > 0 && ($currentTimeStamp != "") && (strtotime($currentTimeStamp) > strtotime($lastImportDate))) {
                    $dbValues[] = array('name' => $name, 'timestamp' => $currentTimeStamp, 'watt' => $currentWatt, 'cummulatedkWh' => round($cummulatedkWh, 3));
                }
            }
        }
    }
    addDebugInfo("sunny_explorer: mapLinesToDBValues: ImportedLines: " . count($lines) . " - DataRows: " . count($dbValues));
    return $dbValues;
}
