<?php
global $con, $params;

// Loop over all plants and import each plant separately
foreach ($params['PLANTS'] as $name => $plant) {
    $lastImportDate = getLastImportDateForPlant($name, $con);
    $files_to_import = getFilesToImport($name, $lastImportDate, $plant['importPrefix'] . "-");
    $importDateFormat = $plant['importDateFormat'];
    addDebugInfo("sunny_explorer_multi_cumulated: LastStartImportDate: $lastImportDate - ImportFilesCount: " . count($files_to_import));
    foreach ($files_to_import as $import_filename) {
        addDebugInfo("sunny_explorer_multi_cumulated: importFile: $import_filename");
        $importData = readImportFile($import_filename, 0);
        $dbValues = mapLinesToDBValues($importData, $name, $lastImportDate, $importDateFormat);
        prepareAndInsertData($dbValues, $con);
    }
}

function mapLinesToDBValues(array $lines, string $name, $lastImportDate, $importDateFormat): array
{
    global $params;
    $dbValues = array();
    $sumMinkWhCounter = 0.0;
    $lineCounter = 0;
    $isFirstValueLine = true;
    $converterCounter = 1;
    foreach ($lines as $line) {
        $lineCounter++;
        if ($lineCounter == 8) {
            $importDateFormat = parseImportDateTimeFormat($line, $importDateFormat);
            $lineValues = explode(";", $line);
            // determine how many converters are available
            $valueCount = count($lineValues) -1;
            $converterCounter = intdiv($valueCount , 2);
        }
        if ($lineCounter > 8) {
            $lineValues = explode(";", $line);
            if (hasAllValidValues($lineValues, $converterCounter)) {
                // first data row get initial $minkWhCounter value from first line
                if ( $isFirstValueLine ) {
                    $isFirstValueLine = false;
                    for ($i = 0; $i < $converterCounter; $i++) {
                        $minkWhCounter = str_replace(',', '.', $lineValues[($i * 2) + 1]);
                        $sumMinkWhCounter += round($minkWhCounter, 3);
                    }
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
                $sumCurrentkWhCounter = 0.0;
                for ($i = 0; $i < $converterCounter; $i++) {
                    $currentkWhCounter = trim(str_replace(',', '.', $lineValues[($i * 2) + 1]));
                    if (strlen($currentkWhCounter) > 0 && is_numeric($currentkWhCounter)) {
                        $sumCurrentkWhCounter += $currentkWhCounter;
                    }
                }
                $cummulatedkWh = round($sumCurrentkWhCounter - $sumMinkWhCounter, 3);
                $sumCurrentWatt = 0;
                for ($i = 0; $i <$converterCounter; $i++) {
                    $wattVal = trim(str_replace(',', '.', $lineValues[($i * 2) + 2]));
                    if (strlen($wattVal) > 0 && is_numeric($wattVal)) {
                        $sumCurrentWatt += $wattVal;
                    }
                }
                $sumCurrentWatt = $sumCurrentWatt * 1000;

                // insert only new data and value > 0
                if ($sumCurrentWatt > 0 && ($currentTimeStamp != "") && (strtotime($currentTimeStamp) > strtotime($lastImportDate))) {
                    $dbValues[] = array('name' => $name, 'timestamp' => $currentTimeStamp, 'watt' => $sumCurrentWatt, 'cummulatedkWh' => round($cummulatedkWh, 3));
                }

            }
        }
    }
    addDebugInfo("sunny_explorer_multi_cumulated: mapLinesToDBValues: ImportedLines: " . count($lines) . " - DataRows: " . count($dbValues));
    return $dbValues;
}

/**
 * Try to parse dateformat from CSV wich is normaly written in line 9
 * dd.MM.yyyy HH:mm:ss;kWh;kW
 * -->  "d.m.Y H:i:s"
 */
function parseImportDateTimeFormat(string $line, string $default): string
{
    $parts = explode(";", $line);
    if (count($parts) > 0) {
        $format = $parts[0];
        $format = str_replace("dd", "d", $format);
        $format = str_replace("MM", "m", $format);
        $format = str_replace("yyyy", "Y", $format);
        $format = str_replace("HH", "H", $format);
        $format = str_replace("mm", "i", $format);
        $format = str_replace("ss", "s", $format);
        addDebugInfo("parseImportDateTimeFormat: parsed import format from CSV: $line -> $format");
        return $format;
    } else {
        addDebugInfo("parseImportDateTimeFormat: cannot parse import format from CSV: $line using default: $default override in [plant][importDateFormat] if needed");
        return $default;
    }

}
