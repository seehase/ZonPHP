<?php
global $con, $params;

// fixme: collect values in array (date, watt, cum, name) and build insert statement later with function ...insertVal(array)

function mapLinesToDBValues(array $lines, string $name): array
{
    $dbValues = array();
    foreach ($lines as $line) {
        $lineValues = explode(";", $line);
        if (count($lineValues) > 2) {
            $currentTimeStamp = date("Y-m-d H:i:s", strtotime($lineValues[0]));
            $currentkWhCounter = str_replace(',', '.', $lineValues[1]);
            $cummulatedkWh = number_format($currentkWhCounter - $minkWhCounter, 3);
            $currentWatt = str_replace(',', '.', $lineValues[2]) * 1000;

            // insert only new data and value > 0
            if ($currentWatt > 0 && (strtotime($currentTimeStamp) > strtotime($lastImportDate)) && ($currentTimeStamp != "")) {
                $dbValues[] = array('name' => $name, 'timestamp' => $currentTimeStamp, 'watt' => $currentWatt, 'cummulatedkWh' => $cummulatedkWh);
            }
        }
    }
    return $dbValues;
}

foreach ($params['PLANTS'] as $name => $plant) {
    $lastImportDate = getLastImportDate($name, $con);
    $files_to_import = getFilesToImport($name, $lastImportDate, $plant['importPrefix']);


    //   fill Array
    //   create Statments and execute SQL

    foreach ($files_to_import as $import_filename) {
        $minkWhCounter = 0.0;
        $cummulatedkWh = 0.0;
        $dayValues = "";
        $importData = readImportFile($import_filename, 10);
        $dbValues = mapLinesToDBValues($importData, $name);

        prepareAndInsertData($dbValues);


        // execute SQL Inserts
        if ($dayValues != "") {
            $currentDate = date("Y-m-d", strtotime($currentTimeStamp));
            $dayValues = substr($dayValues, 0, -1);
            $sql_insert_day = "insert into " . TABLE_PREFIX . "_dag (IndexDag, Datum_Dag, Geg_Dag, kWh_Dag, Naam) values $dayValues";
            $del_month = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Naam ='$name' AND Datum_Maand='$currentDate'";
            $sqL_insert_month = "insert into " . TABLE_PREFIX . "_maand (IndexMaand, Datum_Maand, Geg_Maand, Naam) values ('$currentDate$name', '$currentDate', $cummulatedkWh,'$name')";

            mysqli_query($con, $del_month) or die("Query failed. ERROR1: " . $del_month . mysqli_error($con));
            mysqli_query($con, $sql_insert_day) or die("Query failed. ERROR2: " . $sql_insert_day . mysqli_error($con));
            mysqli_query($con, $sqL_insert_month) or die("Query failed. ERROR3: " . $sqL_insert_month . mysqli_error($con));
        }
    }
}

function prepareAndInsertData(array $dbValues)
{
    if (count($dbValues) > 0) {
        $currentDate = date("Y-m-d", strtotime($dbValues[0]['timestamp']));
        $minkWhCounter = $dbValues[0]['cummulatedkWh'];
        $maxkWhCounter = end($dbValues)['cummulatedkWh'];
        foreach ($dbValues as $row) {

        }

    }
}
