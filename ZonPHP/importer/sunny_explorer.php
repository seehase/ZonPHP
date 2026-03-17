<?php
global $con, $params;

// Loop over all plants and import each plant separately
foreach ($params['PLANTS'] as $name => $plant) {
    $lastImportDate = getLastImportDateForPlant($name, $con);
    $files_to_import = getFilesToImport($name, $lastImportDate, $plant['importPrefix'] . "-");
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
    $minkWhCounter = null;
    $lineCounter = 0;
    foreach ($lines as $line) {
        $lineCounter++;
        if ($lineCounter == 8) {
            $importDateFormat = parseImportDateTimeFormat($line, $importDateFormat);
        }
        if ($lineCounter <= 8) {
            continue;
        }

        $lineValues = explode(";", $line);
        if (count($lineValues) < 3) {
            addDebugInfo("sunny_explorer: mapLinesToDBValues: skip line $lineCounter, not enough columns: " . trim($line));
            continue;
        }

        $dateFromDB = trim($lineValues[0]);
        $kWhField = trim(str_replace(',', '.', $lineValues[1]));
        $wattField = trim(str_replace(',', '.', $lineValues[2]));

        if ($dateFromDB === "" || $kWhField === "" || $wattField === "") {
            addDebugInfo("sunny_explorer: mapLinesToDBValues: skip line $lineCounter, empty required column");
            continue;
        }

        if (!is_numeric($kWhField)) {
            addDebugInfo("sunny_explorer: mapLinesToDBValues: skip line $lineCounter, invalid kWh value: $kWhField");
            continue;
        }
        if (!is_numeric($wattField)) {
            addDebugInfo("sunny_explorer: mapLinesToDBValues: skip line $lineCounter, invalid kW value: $wattField");
            continue;
        }

        $currentkWhCounter = (float) $kWhField;
        $currentWatt = (float) $wattField * 1000;
        if ($minkWhCounter === null) {
            $minkWhCounter = $currentkWhCounter;
        }

        if ($params['importLocalDateAsUTC']) {
            $convertedDate = convertLocalDateTime($dateFromDB, $importDateFormat, true); // in UTC now
        } else {
            $convertedDate = $dateFromDB;  // keep local time
        }

        $convertedTimeStamp = convertToUnixTimestamp($convertedDate);
        if ($convertedTimeStamp === false || $convertedTimeStamp === null) {
            addDebugInfo("sunny_explorer: mapLinesToDBValues: skip line $lineCounter, invalid date: $dateFromDB");
            continue;
        }

        $currentTimeStamp = date("Y-m-d H:i:s", $convertedTimeStamp);
        $cummulatedkWh = round($currentkWhCounter - $minkWhCounter, 3);

        if ($currentWatt <= 0) {
            continue;
        }

        if ($currentTimeStamp === "") {
            addDebugInfo("sunny_explorer: mapLinesToDBValues: skip line $lineCounter, timestamp conversion failed");
            continue;
        }

        if ($lastImportDate && strtotime($currentTimeStamp) <= strtotime($lastImportDate)) {
            continue;
        }

        $dbValues[] = array(
            'name' => $name,
            'timestamp' => $currentTimeStamp,
            'watt' => $currentWatt,
            'cummulatedkWh' => $cummulatedkWh,
        );
    }

    addDebugInfo("sunny_explorer: mapLinesToDBValues: Lines read: " . count($lines) . " - imported DataRows: " . count($dbValues));
    
    return $dbValues;
}
