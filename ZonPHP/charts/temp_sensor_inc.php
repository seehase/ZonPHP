<?php

// ---Temperature SENSOR -----------------------------------------------------------------------------------------------------------

global $params, $chartdatestring, $con, $con_weewx, $max_first_val, $max_last_val, $colors;
$sensor_available = ($params['useWeewx'] == true);

$sensorid = 197190;
$sensortype = 1;
$temp_vals = array();

$weewx_table_name = $params['weewx']['tableName'];
$weewx_temp_column = $params['weewx']['tempColumn'];
$weewx_timestamp_column = $params['weewx']['timestampColumn'];
$weewx_temp_is_farenheit = $params['weewx']['tempInFahrenheit'];

$sensor_success = false;
$temp_unit = "°C";

if ($sensor_available) {
    $val_avg = 0;
    $result_sensor = 0;

    // get start and end of chartDateString in UnixTimeStamp
    $startUnixTimestamp = getMinUnixTimestamp($chartdatestring);
    $endUnixTimestamp = getMaxUnixTimestamp($chartdatestring);

    // init array for the hole day
    for ($i = $startUnixTimestamp; $i <= $endUnixTimestamp; $i += 300) {
        $temp_vals[$i] = "NaN";
    }

    if ($params['useWeewx']) {
        // use weewx connection and table
        $sql_sensor =
            "   SELECT 
                   dateTime,
                   $weewx_temp_column  AS val,
                   from_unixtime(dateTime) as nicedate 
                FROM $weewx_table_name 
                WHERE $weewx_timestamp_column > $startUnixTimestamp and $weewx_timestamp_column < $endUnixTimestamp
                ORDER BY dateTime ASC";
        $result_sensor = mysqli_query($con_weewx, $sql_sensor) or die("Query failed. $sql_sensor " . mysqli_error($con));
        $sensor_success = true;
    }

    if ($sensor_success && mysqli_num_rows($result_sensor) != 0) {
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
                $temp_vals[$row['dateTime']] = $val;
            }
        }
    } else {
        // no data found
        $sensor_success = false;
    }
}
// ---SENSOR -----------------------------------------------------------------------------------------------------------
// Temperature line --------------------------------------------------------------
$str_temp_vals = "";
$temp_serie = "";
if ($sensor_success) {
    foreach ($temp_vals as $time => $val) {
        if (($time >= $max_first_val) && ($time <= $max_last_val)) {
            $formatedVal = "NaN";
            if (is_numeric($val)) {
                $formatedVal = number_format($val, 1, '.', '');
                if($val < $minTemperature ){
                    $minTemperature = $val;
                }
                if($val > $maxTemperature){
                    $maxTemperature = $val;
                }
            }
            $str_temp_vals .= "{x:" . $time * 1000 . ", y: " . $formatedVal . " },";
        }
    }
    if ($maxTemperature-$minTemperature < 6 ) {
        $minTemperature = $minTemperature - 3;
        $maxTemperature = $maxTemperature + 3;
    }
}
