<?php
global $con, $params;

// Loop over all plants and import each plant separately
foreach ($params['PLANTS'] as $name => $plant) {
    addDebugInfo("sunny_explorer:" . $name);
    $lastImportDate = getLastImportDateForPlant($name, $con);
    addDebugInfo("sunny_explorer: LastStartImportDate: $lastImportDate - ImportFilesCount: " . $lastImportDate);
    $files_to_import = getFilesToImport($name, $lastImportDate, $plant['importPrefix']);
    addDebugInfo("sunny_explorer: LastStartImportDate: $lastImportDate - ImportFilesCount: " . count($files_to_import));
    $importDateFormat = $plant['importDateFormat'];
    addDebugInfo("sunny_explorer: LastStartImportDate: $lastImportDate - ImportFilesCount: " . count($files_to_import));

    foreach ($files_to_import as $import_filename) {
        addDebugInfo("sunny_explorer: importFile: $import_filename");
        debugToConsole("sunny_explorer: importFile: $import_filename");
        $importData = readImportFile($import_filename, 0);
        $dbValues = mapLinesToDBValues($importData, $name, $lastImportDate, $importDateFormat);
        prepareAndInsertData($dbValues, $con);
    }
}


function mapLinesToDBValues(array $lines, string $name, $lastImportDate, $importDateFormat): array
{
    global $params;
    $dbValues = array();
    $minkWhCounter = 0.0;
    $minkWhCounter2 = 0.0;
    $lineCounter = 0;
    foreach ($lines as $line) {
        $lineCounter++;
        if ($lineCounter == 8) {
            $importDateFormat = parseImportDateTimeFormat($line, $importDateFormat);
        }
        if ($lineCounter > 9) {
            $lineValues = explode(";", $line);
            if (count($lineValues) > 4) {
                // first data row get initial $minkWhCounter value from first line
                if ($lineCounter == 10) {
                    $minkWhCounter = str_replace(',', '.', $lineValues[1]);
                    $minkWhCounter2 = str_replace(',', '.', $lineValues[3]);
                }
                $dateFromDB = $lineValues[0];
                // convert to UTC if parameter "importLocalDateAsUTC" is set to true otherwise it will remain localDate
                if ($params['importLocalDateAsUTC']) {
                    $convertedDate = convertLocalDateTime($dateFromDB, $importDateFormat, true); // in UTC now
                } else {
                    $convertedDate = $dateFromDB;  // keep local time
                }
                //
                //sep=;
                //Version CSV1|Tool SE|Linebreaks CR/LF|Delimiter semicolon|Decimalpoint comma|Precision 3
                //
                //;SN: 2100070361;SN: 2100070361;SN: 2100070559;SN: 2100070559
                //;SB 4000TL-20;SB 4000TL-20;SB 4000TL-20;SB 4000TL-20
                //;2100070361;2100070361;2100070559;2100070559
                //;Gesamtertrag;Leistung;Gesamtertrag;Leistung
                //;Counter;Analog;Counter;Analog
                //dd.MM.yyyy HH:mm:ss;kWh;kW;kWh;kW
                //28.06.2024 00:00:00; 70860,938; 0,000; 70212,784; 0,000

                $convertedTimeStamp = convertToUnixTimestamp($convertedDate);
                $currentTimeStamp = date("Y-m-d H:i:s", $convertedTimeStamp);
                $currentkWhCounter = str_replace(',', '.', $lineValues[1]);
                $cummulatedkWh = number_format($currentkWhCounter - $minkWhCounter, 3);
                $currentWatt = str_replace(',', '.', $lineValues[2]) * 1000;
                // New
                $currentkWhCounter2 = str_replace(',', '.', $lineValues[3]);
                $cummulatedkWh2 = number_format($currentkWhCounter2 - $minkWhCounter2, 3);
                if ($lineValues[4] != "") {
                    $currentWatt2 = str_replace(',', '.', $lineValues[4]) * 1000;
                } else {
                    $currentWatt2 = 0.000;
                    //debugToConsole("sunny_explorer: Fehlender lineValues[4]");
                }
                // insert only new data and value > 0
                if ($currentWatt > 0 && ($currentTimeStamp != "") && (strtotime($currentTimeStamp) > strtotime($lastImportDate))) {
                    $dbValues[] = array('name' => $name, 'timestamp' => $currentTimeStamp, 'watt' => ($currentWatt+$currentWatt2), 'cummulatedkWh' => number_format(($cummulatedkWh+$cummulatedkWh2), 3));
                }
            }
        }
    }
    addDebugInfo("sunny_explorer: mapLinesToDBValues: ImportedLines: " . count($lines) . " - DataRows: " . count($dbValues));
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

function debugToConsole($msg) { 
        echo "<script>console.log(".json_encode($msg).")</script>";
}
