<?php

// ---Temperature SENSOR -----------------------------------------------------------------------------------------------------------

if ($external_sensors) {
    $tablename = $table_prefix . "_sensordata";
    $result = mysqli_query($con, "SHOW TABLES LIKE '" . $tablename . "'");
    if ($result->num_rows == 0) {
        $external_sensors = false;
    }
}
$sensor_available = ($external_sensors == true) || ($use_weewx == true);

$sensor_values = array();
$sensorid = 197190;
$sensortype = 1;
$temp_vals = array();
$val_min = 500;
$val_max = -500;


// init array for the hole day
// not needed any more ignore NULL values
//for ($i = 0; $i < 24; $i++) {
//    for ($j = 0; $j < 12; $j++) {
//        $sensor_values[date("H:i", strtotime($i . ":" . $j * 5))] = "";
//    }
//}

if (!isset($weewx_table_name)) $weewx_table_name = "archive";
if (!isset($weewx_temp_column)) $weewx_temp_column = "outTemp";
if (!isset($weewx_timestamp_column)) $weewx_timestamp_column = "dateTime";
if (!isset($weewx_temp_is_farenheit)) $weewx_temp_is_farenheit = true;

$sensor_success = false;
$temp_unit = "°C";

if ($sensor_available) {
    $val_avg = 0;
    if (isset($param['external_sensors_for_daychart'])) {
        // use external arexx sensors
        $sql_sensor =
            "  SELECT 
                   AVG( measurevalue ) AS val,
                   STR_TO_DATE( CONCAT( DATE( logtime ) ,  ' ',HOUR( logtime ) , ':', LPAD( FLOOR( MINUTE( logtime ) /5 ) *5, 2, '0' ) , ':00' ) ,
                       '%Y-%m-%d %H:%i:%s' ) AS nicedate 
                FROM $tablename 
                WHERE logtime  LIKE '" . $chartdatestring . "%' AND sensorid= $sensorid AND sensortype = $sensortype
                GROUP BY nicedate ORDER BY nicedate ASC";
        $result_sensor = mysqli_query($con, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
        $sensor_success = true;
    } else if ($use_weewx == true) {
        // use weewx connection and table
        $sql_sensor =
            "   SELECT 
                   AVG( $weewx_temp_column ) AS val,
                   STR_TO_DATE( CONCAT( DATE( from_unixtime($weewx_timestamp_column )) ,  ' ' ,HOUR( from_unixtime($weewx_timestamp_column) ) , ':', 
                   LPAD( FLOOR( MINUTE( from_unixtime($weewx_timestamp_column) ) /5 ) *5, 2, '0' ) , ':00' ) ,
                       '%Y-%m-%d %H:%i:%s' ) AS nicedate 
                FROM $weewx_table_name 
                WHERE from_unixtime($weewx_timestamp_column)  LIKE '" . $chartdatestring . "%'
                GROUP BY nicedate ORDER BY nicedate ASC";
        $result_sensor = mysqli_query($con_weewx, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
        $sensor_success = true;
    }

    if ($sensor_success == true && mysqli_num_rows($result_sensor) != 0) {
        while ($row = mysqli_fetch_array($result_sensor)) {
            if (isset($row['val']) && $row['val'] != 0) {
                // array time = value only if != null
                if ($weewx_temp_is_farenheit) {
                    $val = number_format(($row['val'] - 32) * 5 / 9, 1); // F --> °C
                    $temp_unit = "°C";
                } else {
                    $val = number_format($row['val'], 1); // temp is already in °C
                    $temp_unit = "°F";
                }
                $sensor_values[date("H:i", strtotime($row['nicedate']))] = $val;
                $temp_vals[strtotime($row['nicedate'])] = $val;
                if ($val > $val_max) $val_max = $val;
                if ($val < $val_min) $val_min = $val;
            }

        }

        // enlarge y-axis if needed
        $val_dif = abs($val_max - $val_min);
        if ($val_dif < 5) {
            $val_min = $val_min - 3;
            $val_max = $val_max + 3;
        };
    }


}
// ---SENSOR -----------------------------------------------------------------------------------------------------------
// temp line --------------------------------------------------------------
$str_temp_vals = "";
if ($sensor_success) {
    $str_temp_vals = "";
    foreach ($temp_vals as $time => $val) {
        if (($time > $max_first_val) && ($time < $max_last_val)) {
            if (isset($param['no_units'])) {
                $str_temp_vals .= "[" . $time * 1000 . "," . number_format($val, 1, '.', '') . "],";
            } else {
                $str_temp_vals .= "{x:" . $time * 1000 . ", y:" . number_format($val, 1, '.', '') . ", unit: '" . $temp_unit . "' },";
            }
        }
    }
    $str_temp_vals = substr($str_temp_vals, 0, -1);


    $temp_serie = "    {  name: 'Temp', id: 'Temp', type: 'spline', yAxis: 2, color: '#" . $colors['color_chart_temp_line'] . "',                       
                        data: [" . $str_temp_vals . "] } ";
}

$temp_serie = $temp_serie . "";


//$temp_serie = "    {  name: 'Temp', id: 'Temp', type: 'spline', yAxis: 2,   color: '#". $colors['color_chart_temp_line']."',
//                         data: [" . $str_temp_vals ."] } ";

?>