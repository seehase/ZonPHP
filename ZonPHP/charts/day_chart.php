<?php
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "Parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}


$id = "";
$chartdate = time();
$chartdatestring = strftime("%Y-%m-%d", $chartdate);

if (isset($_GET['dag'])) {
    $chartdatestring = html_entity_decode($_GET['dag']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = strftime("%Y-%m-%d", $chartdate);
}


if (isset($_GET['Schaal']))
    $aanpas = 1;
else
    $aanpas = 0;

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}
if (isset($_GET['naam'])) {
    $inverter = $_GET['naam'];
}


$sqlref = "SELECT *
	FROM " . $table_prefix . "_refer
	WHERE Month(Datum_Refer)='" . date("m", $chartdate) . "' AND Naam='" . $inverter . "'";


$resultref = mysqli_query($con, $sqlref) or die("Query failed. dag-ref " . mysqli_error($con));
if (mysqli_num_rows($resultref) == 0)
    $frefmaand = 1;
else {
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand = $row['Dag_Refer'];
    }
}

$valarray = array();
$sql = "SELECT AVG( Geg_Dag ) AS gem,
	 STR_TO_DATE( CONCAT( DATE( Datum_Dag ) , ' ',HOUR( Datum_Dag ) , ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" . $param['isorteren'] . " ) *" . $param['isorteren'] . ", 2, '0' ) , ':00' ) , '%Y-%m-%d %H:%i:%s' ) AS datumtijd FROM " .
    $table_prefix . "_dag where Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' AND Naam='" . $inverter . "' GROUP BY datumtijd ORDER BY datumtijd ASC";

$result = mysqli_query($con, $sql) or die("Query failed. dag " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $datum = strftime("%d %B %Y", $chartdate);

    $tlaatstetijd = time();
    $geengevdag = 0;
    $adatum[] = date("Y-m-d", $chartdate);
    $agegevens[] = 0;
    $aoplopendkwdag[] = 0;
} else {
    $geengevdag = 1;
    $fsomoplopend = 0;
    while ($row = mysqli_fetch_array($result)) {
        $tlaatstetijd = strtotime($row['datumtijd']);
        $adatum[] = $row['datumtijd'];
        $agegevens[date("H:i", strtotime($row['datumtijd']))] = $row['gem'];

        $x1 = $row['datumtijd'];
        $x2 = strtotime($row['datumtijd']);
        $x3 = $x2 * 1000;
        $x4 = new DateTime($x1);
        $x5 = $x4->getTimestamp();

        $valarray[strtotime($row['datumtijd'])] = $row['gem'];

        $fsomoplopend += $row['gem'] * 1000 / (1000 * 60 / $param['isorteren']);
        $aoplopendkwdag[strtotime($row['datumtijd'])] = $fsomoplopend;
<<<<<<< HEAD
=======
        if (!in_array($inverter_name, $inveter_list)) {
            if (in_array($inverter_name, $sNaamSaveDatabase)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        };
>>>>>>> origin/pr/37
    }

    $datum = strftime("%d %B %Y", $chartdate);
}
//--------------------------------------------------------------------------------------------------
// get best day for current month (max value over all years for current month
$sqlmaxdag = "SELECT Datum_Maand, Geg_Maand
	 FROM " . $table_prefix . "_maand
<<<<<<< HEAD
	 JOIN (SELECT month(Datum_Maand) AS maand, max(Geg_Maand) AS maxgeg FROM " . $table_prefix . "_maand WHERE Naam='" . $inverter .
    "' AND DATE_FORMAT(Datum_Maand,'%m')='" . date('m', $chartdate) . "' GROUP BY maand )AS maandelijks ON (month(" .
    $table_prefix . "_maand.Datum_Maand) = maandelijks.maand AND maandelijks.maxgeg = " . $table_prefix . "_maand.Geg_Maand) ORDER BY maandelijks.maand";

$resultmaxdag = mysqli_query($con, $sqlmaxdag) or die("Query failed. dag-max " . mysqli_error($con));
if (mysqli_num_rows($resultmaxdag) == 0) {
    $maxdag = date("y-m-d", time());
    $maxkwh = 0;
} else {
    while ($row = mysqli_fetch_array($resultmaxdag)) {
        $maxdag = $row['Datum_Maand'];
        $maxkwh = round($row['Geg_Maand'], 2);
    }
}
$maxkwh = number_format($maxkwh, 2, ',', ' ');
$nice_max_date = date("Y-m-d", strtotime($maxdag));

// select data from the best day for current month
$sqlmd = "SELECT AVG( Geg_Dag ) AS gem,
	 STR_TO_DATE( CONCAT( DATE( Datum_Dag ) , ' ',HOUR( Datum_Dag ) , ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" . $param['isorteren'] . " ) *" .
    $param['isorteren'] . ", 2, '0' ) , ':00' ) , '%Y-%m-%d %H:%i:%s' ) AS datumtijd FROM " . $table_prefix . "_dag where Datum_Dag LIKE '" .
    date("Y-m-d", strtotime($maxdag)) . "%' AND Naam='" . $inverter . "' GROUP BY datumtijd ORDER BY datumtijd ASC";

$resultmd = mysqli_query($con, $sqlmd) or die("Query failed. dag-max-dag " . mysqli_error($con));
if (mysqli_num_rows($resultmd) == 0) {
    $geenmax = 0;
    $agegevensdag_max[] = 0;
} else {
    $geenmax = 1;
    while ($row = mysqli_fetch_array($resultmd)) {
        $adatum_max[] = $row['datumtijd'];
        $agegevensdag_max[strtotime($row['datumtijd'])] = $row['gem'];
    }
}
//--------------------------------------------------------------------------------------------------


// ---SENSOR -----------------------------------------------------------------------------------------------------------
$sensor_values = array();
// init array for the hole day
for ($i = 0; $i < 24; $i++) {
    for ($j = 0; $j < 12; $j++) {
        $sensor_values[date("H:i", strtotime($i . ":" . $j * 5))] = "";
    }
}
$sensorid = 197190;
$sensortype = 1;

$temp_vals = array();

$tablename = $table_prefix . "_sensordata";
$result = mysqli_query($con, "SHOW TABLES LIKE '" . $tablename . "'");
$sensor_available = ($result->num_rows == 1) || ($use_weewx == true);

$val_min = 500;
$val_max = -500;

if ($sensor_available) {
    $val_avg = 0;
    $sensor_success = false;
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
                   AVG( outTemp ) AS val,
                   STR_TO_DATE( CONCAT( DATE( from_unixtime(dateTime) ) ,  ' ',HOUR( from_unixtime(dateTime) ) , ':', 
                   LPAD( FLOOR( MINUTE( from_unixtime(dateTime) ) /5 ) *5, 2, '0' ) , ':00' ) ,
                       '%Y-%m-%d %H:%i:%s' ) AS nicedate 
                FROM archive 
                WHERE from_unixtime(dateTime)  LIKE '" . $chartdatestring . "%'
                GROUP BY nicedate ORDER BY nicedate ASC";
        $result_sensor = mysqli_query($con_weewx, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
        $sensor_success = true;
    }

    if ($sensor_success == true && mysqli_num_rows($result_sensor) != 0) {
        while ($row = mysqli_fetch_array($result_sensor)) {
            // array time = value
            $val = number_format(($row['val'] - 32) * 5 / 9, 1); // F --> °C
            $sensor_values[date("H:i", strtotime($row['nicedate']))] = $val;
            $temp_vals[strtotime($row['nicedate'])] = $val;
            if ($val > $val_max) $val_max = $val;
            if ($val < $val_min) $val_min = $val;
        }

        // enlarge y-axis if needed
        $val_dif = abs($val_max - $val_min);
        if ($val_dif < 5) {
            $val_min = $val_min - 3;
            $val_max = $val_max + 3;
=======
	 JOIN (SELECT month(Datum_Maand) AS maand, max(Geg_Maand) AS maxgeg FROM " . $table_prefix . "_maand WHERE 
     DATE_FORMAT(Datum_Maand,'%m')='" . date('m', $chartdate) . "' " . " GROUP BY maand )AS maandelijks ON (month(" .
    $table_prefix . "_maand.Datum_Maand) = maandelijks.maand AND maandelijks.maxgeg = " . $table_prefix . "_maand.Geg_Maand) ORDER BY maandelijks.maand";
$resultmaxdag = mysqli_query($con, $sqlmaxdag) or die("Query failed. dag-max " . mysqli_error($con));
if (mysqli_num_rows($resultmaxdag) == 0) {
    $maxdag = date("y-m-d", time());
///     $maxkwh = 0;
} else {
    while ($row = mysqli_fetch_array($resultmaxdag)) {
        $maxdag = $row['Datum_Maand'];
///        $maxkwh = round($row['Geg_Maand'], 2);
    }
}
// Query maxkwh to get array with max value for all inverters
$sqlmaxkwh = "SELECT Geg_Maand, Naam
	 FROM " . $table_prefix . "_maand
	 WHERE Datum_Maand LIKE  '" . date("Y-m-d", strtotime($maxdag)) . "%' 
	 ORDER BY Naam ASC ";
$resultmaxkwh = mysqli_query($con, $sqlmaxkwh) or die("Query failed. kwh-max " . mysqli_error($con));
if (mysqli_num_rows($resultmaxkwh) == 0) {
    $maxkwh[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultmaxkwh)) {
        $maxkwh[] = round($row['Geg_Maand'], 2);
    }
}
/// $maxkwh = number_format($maxkwh, 2, ',', ' ');
$nice_max_date = date("Y-m-d", strtotime($maxdag));
// select data from the best day for current month
$sqlmdinv = "SELECT Geg_Dag AS gem, STR_TO_DATE( CONCAT( DATE( Datum_Dag ) ,  ' ', HOUR( Datum_Dag ) ,  ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" . $param['isorteren'] . " ) *" . $param['isorteren'] . ", 2,  '0' ) ,  ':00' ) ,  '%Y-%m-%d %H:%i:%s' ) AS datumtijd, Naam AS Name
FROM " . $table_prefix . "_dag
WHERE Datum_Dag LIKE  '" . date("Y-m-d", strtotime($maxdag)) . "%'
ORDER BY Name, datumtijd ASC";
$resultmd = mysqli_query($con, $sqlmdinv) or die("Query failed. dag-max-dag " . mysqli_error($con));
if (mysqli_num_rows($resultmd) == 0) {
    $maxdagpeak = 0;
    $agegevensdag_max[] = 0;
} else {
    $maxdagpeak = 0;
    while ($row = mysqli_fetch_array($resultmd)) {
        $inverter_name = $row['Name'];
        $valarraymax[strtotime($row['datumtijd'])] = $row['gem'];
        $all_valarraymax[strtotime($row['datumtijd'])] [$inverter_name] = $row['gem'];
        $adatum_max[] = $row['datumtijd'];
        $agegevensdag_max[strtotime($row['datumtijd'])] = $row['gem'];
        if ($row['gem'] > $maxdagpeak) {
            $maxdagpeak = $row['gem'];
>>>>>>> origin/pr/37
        };
    }




// ---SENSOR -----------------------------------------------------------------------------------------------------------
}


$strgegmax = "";
$strsomkw = "";
$str_dataserie = "";
<<<<<<< HEAD

foreach ($valarray as $time => $val) {
    if (isset($param['no_units'])) {
        $str_dataserie .= '[' . ($time * 1000) . ',' . $val . '] ,';
    } else {
        $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $val . ' , unit: \'W\'} ,';
=======
$cnt = 0;
foreach ($inveter_list as $inverter_name) {
    $col1 = $myColors[$inverter_name]['min'];
    $col2 = $myColors[$inverter_name]['max'];
    $series_isVisible = "false";
    if ($showAllInverters) {
        $series_isVisible = "true";
    } else if ($inverter_id == $inverter_name) {
        $series_isVisible = "true";
    };
    $str_dataserie .= "{ name: '$inverter_name', id: '$inverter_name', type: 'area', marker: { enabled: false }, visible: $series_isVisible, color: { linearGradient: {x1: 0, x2: 0, y1: 1, y2: 0}, stops: [ [0, $col1], [1, $col2]] },                        
    data:[";
    foreach ($all_valarray as $time => $valarray) {
        if (!isset($valarray[$inverter_name])) $valarray[$inverter_name] = 0;
        if (isset($param['no_units'])) {
            $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name] . '}, ';
        } else {
            $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name] . ', unit: \'W\'}, ';
        }
>>>>>>> origin/pr/37
    }
}
// strip last ","
if (strlen($str_dataserie > 0)) {
    $str_dataserie = substr($str_dataserie, 0, -1);
}

// day max line --------------------------------------------------------------
$str_max = "";
$cnt = 0;

foreach ($agegevensdag_max as $time => $fw) {

    $cnt++;
    // hier in time ist die Ursprüngliche Zeit... muss auf heute geändert werden
    $orginal_date = date($time);
    $hour = intval(date('G', $orginal_date));
    $minutes = intval(date('i', $orginal_date));
    $newDate = mktime($hour, $minutes, 0, intval(date('m', $chartdate)), intval(date('j', $chartdate)), intval(date('Y', $chartdate)));
    if ($cnt == 1) {
        // remember first date
        $max_first_val = $newDate;
    }
<<<<<<< HEAD

    if (isset($param['no_units'])) {
        $strtemp = "[" . ($newDate * 1000) . ", " . $fw . "] ";
    } else {
        $strtemp = "{x:" . ($newDate * 1000) . ", y:" . $fw . ", unit: 'W'} ";
    }

    $str_max .= $strtemp . ",";
    $strgegmax .= $fw . ",";
=======
    $str_max .= "{ name: '$inverter_name max',  color : '#15ff24', linkedTo: '$inverter_name', lineWidth: 1,  $dash  type: 'line',  stacking: 'normal', marker: { enabled: false },                           
    data:[";
    
    //echo $key;
    foreach ($all_valarraymax as $time => $valarraymax) {
        $cnt++;
        // hier in time ist die Ursprüngliche Zeit... muss auf heute geändert werden
        $orginal_date = date($time);
        $hour = intval(date('G', $orginal_date));
        $minutes = intval(date('i', $orginal_date));
        $newDate = mktime($hour, $minutes, 0, intval(date('m', $chartdate)), intval(date('j', $chartdate)), intval(date('Y', $chartdate)));
        if ($cnt == 1) {
            // remember first date
            $max_first_val = $newDate;
        }
        if (!isset($valarraymax[$inverter_name])) $valarraymax[$inverter_name] = 0;
        if (isset($param['no_units'])) {
            $str_max .= '{x:' . ($newDate * 1000) . ', y:' . $valarraymax[$inverter_name] . '}, ';
        } else {
            $str_max .= '{x:' . ($newDate * 1000) . ', y:' . $valarraymax[$inverter_name] . ', unit: \'W\'}, ';
        }
    }
    $str_max = substr($str_max, 0, -1);
    $str_max .= "]}, 
                    ";
    $cnt++;
>>>>>>> origin/pr/37
}
// remember last date
$max_last_val = $newDate;
$str_max = substr($str_max, 0, -1);
$strgegmax = substr($strgegmax, 0, -1);

// temp line --------------------------------------------------------------
$str_temp_vals = "";
if ($sensor_available) {
    $str_temp_vals = "";
    foreach ($temp_vals as $time => $val) {
        if (($time > $max_first_val) && ($time < $max_last_val)) {
            if (isset($param['no_units'])) {
                $str_temp_vals .= "[" . $time * 1000 . "," . number_format($val, 1, '.', '') . "],";
            } else {
                $str_temp_vals .= "{x:" . $time * 1000 . ", y:" . number_format($val, 1, '.', '') . ", unit: '°C' },";
            }
        }
    }
    $str_temp_vals = substr($str_temp_vals, 0, -1);
}

// cumulative line --------------------------------------------------------------
<<<<<<< HEAD
$str_cum = "";
$cnt = 0;
$cum_max_value = 0;
foreach ($aoplopendkwdag as $tuur => $fkw) {
    $cnt++;
    if (isset($param['no_units'])) {
        $strtemp = "[" . ($tuur * 1000) . ", " . number_format($fkw, 1, '.', '') . "]";
    } else {
        $strtemp = "{x:" . ($tuur * 1000) . ", y:" . number_format($fkw, 1, '.', '') . ", unit: 'Wh' }";
    }

    $str_cum .= $strtemp . ",";
    $strsomkw .= $fkw . ",";
    $cum_max_value = $fkw;
}
$str_cum = substr($str_cum, 0, -1);
if (strlen($str_dataserie) == 0) $str_cum = "";

$strsomkw = substr($strsomkw, 0, -1);


if (max($aoplopendkwdag) < 2) $aoplopendkwdag1 = 2;
else $aoplopendkwdag1 = round((max($aoplopendkwdag) + 0.5), 0);


=======
// $str_cum = "";
// $cnt = 0;
// $cum_max_value = 0;
// foreach ($aoplopendkwdag as $tuur => $fkw) {
//     $cnt++;
//     $fkw = $fkw / 1000;
//     if (isset($param['no_units'])) {
//         $strtemp = "[" . ($tuur * 1000) . ", " . number_format($fkw, 1, '.', '') . "]";
//     } else {
//         $strtemp = "{x:" . ($tuur * 1000) . ", y:" . number_format($fkw, 1, '.', '') . ", unit: 'kWh' }";
//     }
//     $str_cum .= $strtemp . ",";
//     $strsomkw .= $fkw . ",";
//     $cum_max_value = $fkw;
// }
// $str_cum = substr($str_cum, 0, -1);
// if (strlen($str_dataserie) == 0) $str_cum = "";
// $strsomkw = substr($strsomkw, 0, -1);
// if (max($aoplopendkwdag) < 2) $aoplopendkwdag1 = 2;
// else $aoplopendkwdag1 = round((max($aoplopendkwdag) + 0.5), 0);
>>>>>>> origin/pr/37
$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="mycontainer"></div>';
    $show_legende = "false";
}
<<<<<<< HEAD

// --------------
$subtitle = '"<b>' . $txt['actueel'] . ": <\/b> " . date("H:i", $tlaatstetijd) . "  " . number_format(end($agegevens), 0, ',', '.') . "W="
    . number_format(100 * end($agegevens) / $ieffectiefkwpiek, 0, ',', '.') . "%  ";
if ($isIndexPage) {
    $subtitle .= "<br >";
}
$subtitle .= "<b>" . $txt['totaal'] . ": <\/b>" . number_format((end($aoplopendkwdag) / 1000), 2, ',', '.') . "kWh="
    . number_format(end($aoplopendkwdag) / ($ieffectiefkwpiek / 1000), 1, ',', '.') . "kWhp="
    . number_format((100 * end($aoplopendkwdag) / $frefmaand / 1000), 0, ',', '.') . "% ";
if ($isIndexPage) {
    $subtitle .= "<br >";
}
$subtitle .= "<b>" . $txt['max'] . ": <\/b>" . number_format(max($agegevens), 0, ",", ".") . "W="
    . number_format(100 * max($agegevens) / $ieffectiefkwpiek, 0, ",", ".") . "%" . '"';

//--------------------
=======
// get query parameters
$paramstr_day = "";
if (sizeof($_GET) > 0) {
    foreach ($_GET as $key => $value) {
        if ($key != "dag") {
            $paramstr_day .= $key . "=" . $value . "&";
        }
    }
}
if (strpos($paramstr_day, "?") == 0) {
    $paramstr_day = '?' . $paramstr_day;
}
$maxlink = '<a href=\"day_overview.php' . $paramstr_day . 'dag=' . $nice_max_date . '\">' . $nice_max_date . '</a>';

>>>>>>> origin/pr/37
include_once "chart_styles.php";

$show_temp_axis = "false";
$show_cum_axis = "true";
if (strlen($str_temp_vals) > 0) {
    $show_temp_axis = "true";
    $show_cum_axis = "false";
}
<<<<<<< HEAD

=======
>>>>>>> origin/pr/37
?>


<script type="text/javascript">

    $(function () {

        (function (H) {
            var Axis = H.Axis,
                inArray = H.inArray,
                wrap = H.wrap;

            wrap(Axis.prototype, 'adjustTickAmount', function (proceed) {
                var chart = this.chart,
                    primaryAxis = chart[this.coll][0],
                    primaryThreshold,
                    primaryIndex,
                    index,
                    newTickPos,
                    threshold;

                // Find the index and return boolean result
                function isAligned(axis) {
                    index = inArray(threshold, axis.tickPositions); // used in while-loop
                    return axis.tickPositions.length === axis.tickAmount && index === primaryIndex;
                }

                if (chart.options.chart.alignThresholds && this !== primaryAxis) {
                    primaryThreshold = (primaryAxis.series[0] && primaryAxis.series[0].options.threshold) || 0;
                    threshold = (this.series[0] && this.series[0].options.threshold) || 0;

                    primaryIndex = primaryAxis.tickPositions && inArray(primaryThreshold, primaryAxis.tickPositions);

                    if (this.tickPositions && this.tickPositions.length &&
                        primaryIndex > 0 &&
                        primaryIndex < primaryAxis.tickPositions.length - 1 &&
                        this.tickAmount) {

                        // Add tick positions to the top or bottom in order to align the threshold
                        // to the primary axis threshold
                        while (!isAligned(this)) {

                            if (index < primaryIndex) {
                                newTickPos = this.tickPositions[0] - this.tickInterval;
                                this.tickPositions.unshift(newTickPos);
                                this.min = newTickPos;
                            } else {
                                newTickPos = this.tickPositions[this.tickPositions.length - 1] + this.tickInterval;
                                this.tickPositions.push(newTickPos);
                                this.max = newTickPos;
                            }
                            proceed.call(this);
                        }
                    }

                } else {
                    proceed.call(this);
                }
            });
        }(Highcharts));


        var myoptions = <?php echo $chart_options ?>;
<<<<<<< HEAD

        var data_series = [<?php echo $str_dataserie ?>];
        var max_serie = [<?php echo $str_max ?>];
        var cum_serie = [<?php echo $str_cum ?>];
        var temp_serie = [<?php echo $str_temp_vals ?>];
=======
        var khhWp = [<?php echo $param['ieffectief_kwpiekst'] ?>];
        var nmbr =  khhWp.length //misused to get the inverter count
        var maxmax = <?php echo json_encode($maxkwh) ?>;
        var maxlink = '<?php echo $maxlink ?>';
>>>>>>> origin/pr/37
        var temp_max = <?php echo $val_max ?>;
        var temp_min = <?php echo $val_min ?>;


        var col1 = '#<?php echo $colors['color_chartbar1'] ?>';
        var col2 = '#<?php echo $colors['color_chartbar2'] ?>';

        Highcharts.setOptions({<?php echo $chart_lang ?>});
<<<<<<< HEAD

        var mychart = new Highcharts.chart('mycontainer', Highcharts.merge(myoptions, {
=======
        var mychart = new Highcharts.chart('mycontainer_<?php echo $inverter_id ?>', Highcharts.merge(myoptions, {
            chart: {
                events: {
                    render() {
                        mychart = this;
                        series = this.series;
                        // construct subtitle
                        sum =[];
                        kWh =[];
                        peak = [];
                        max =[];
                        current = 0;
                        tota = 0;
                        
                        for (i = nmbr-1; i >= 0 ; i--) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    tota += (series[i].data[j].y) / 12000;//Total
                                    sum[i] = (series[i].data[series[i].data.length - 1]).y; //sum
                                    current = Highcharts.dateFormat('%H:%M', (series[i].data[series[i].data.length - 1]).x);//TIME
                                    kWh[i] = khhWp[i]; //KWH
                            		max[i] = maxmax[i]; //MAXday
                                    peak[i] = series[i].dataMax //PEAK
                                }
                            }
                        }

                        SUM = sum.reduce(add, 0);
                        KWH = kWh.reduce(add, 0);
                        MAX = max.reduce(add, 0);
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {PEAK = 0;}
						else {
                        PEAK = AX[0];}
                        
                        this.setSubtitle({
                            text: "<b>" + txt_actueel + ": </b>" + current + " -  " + Highcharts.numberFormat(SUM, 0, ",", "") +
                                "W" + "=" + (Highcharts.numberFormat(100 * SUM / KWH, 0, ",", "")) + "%" + " - " + txt_peak + ": " + PEAK + "W <br/><b>" +
                                txt_totaal + ":</b> " + (Highcharts.numberFormat(tota, 2, ",", "")) + "kWh = " +
                                (Highcharts.numberFormat((tota / KWH) * 1000, 2, ",", "")) + "kWh/kWp" + " <b>" +
                                txt_max + ": </b>" + maxlink + " " + (Highcharts.numberFormat(MAX, 2, ",", "")) + " kWh"
                        }, false, false);
                        
                        //construct chart
                        total = [];
                        value = 0;
                      
                        indexOfVisibleSeries = [];
                        checkHideForSpline = 1;
                        if (mychart.forRender) {
                            mychart.forRender = false;
                            //function to check amount of visible series and to destroy old spline series
                            mychart.series.forEach(s => {
                                if (s.type === 'spline' && s.visible === true && s.name != 'Temp') {
                                    s.destroy()
                                } else if (s.type === 'spline' && s.visible === false) {
                                    checkHideForSpline = 0
                                }
                               if (s.type === 'area' && s.visible ) {
                                    indexOfVisibleSeries.push(s.index);
                                }
                            });
							if (checkHideForSpline) {
                                for (i = 0; i < mychart.series[0].data.length; i++) {
                                    for (h of indexOfVisibleSeries) {
                                        value += mychart.series[h].data[i].y / 12000;
                                        axis = mychart.series[h].data[i].x;
                                    }
                                    if(typeof axis !== 'undefined') {
                                        total.push([axis, value]);
                                    }
                                }
                                mychart.addSeries({
                                    data: total,
                                    name: 'Cum',
                                    
                                    yAxis: 1,
                                    unit: 'kWh',
                                    type: "spline",
                                    color: '#<?php echo $colors['color_chart_cum_line'] ?>',
                               		})
                            	}
                        	}
                      	mychart.forRender = true
                    }
                }
            },
            tooltip: {
                crosshairs: [true],
                shared: true,
                pointFormatter: function () {
                    unit = this.unit;
                    value = this.y;
                    //if unit is undefined (added series) set unit to 'kWh' and value to two decimals
                    if (!unit) {
                        unit = 'kWh';
                        value = Highcharts.numberFormat(this.y, '2', ',');
                    }
                    return `<span style="color:${this.color}">\u25CF<\/span> ${this.series.name}: <b>${value} ${unit}<\/b><br/>`;
                }
            },
            plotOptions: {
                series: { 
                	states: {
                        hover: {
                            lineWidth: 0,
                        		},
                        inactive: {
                    		opacity: 1
                				}
                			},
			              },
                area: {
                   events: {
        			legendItemClick: function() {
          			var clickedSeries = this,
            		lineSeries = clickedSeries.chart.series.filter(series => series.type === 'line'),
            		visibleLineSeries = [];
          			lineSeries.forEach(function(series) {
            // Set all series to "dot"
            		if (series.options.dashStyle === 'solid') {
              			series.update({
                		dashStyle: 'dash'
              			})
            			}
            // Push all visible series to an array except the one that was clicked
            		if (series.visible && series.index !== clickedSeries.index + nmbr) {
              			visibleLineSeries.push(series)
            			}
            		if (!series.visible && series.index === clickedSeries.index + nmbr) {
              			visibleLineSeries.push(series)
            			}
          				})
          	// Set first visible series to "solid"
          			if (visibleLineSeries.length) {
            			visibleLineSeries[0].update({
              			dashStyle: 'solid'
          				})
          				}
        			  }
      				}, 
                    marker: {
                        radius: 2,
                        enabled: false
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 0
                        },
                        inactive: {
                    opacity: 1
                }
                    },
                    threshold: 0,
                    stacking: 'normal'
                },
            },
            subtitle: {
                style: {
                    wordWrap:'break-word',
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'
                }
            },
            xAxis: {
                type: 'datetime',
                labels: {
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>'
                    }
                }
            },
            yAxis: [{ // Watt
>>>>>>> origin/pr/37
                title: {
                    text: '',
                    style: {
                        color: '#<?php echo $colors['color_chart_text_title'] ?>',
                    },
                },
                subtitle: {
                    text: <?php echo $subtitle ?>,
                    style: {
                        color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
                    },
                },
                xAxis: {
                    type: 'datetime',
                    labels: {
                        style: {
                            color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                        },
                    },
                },
                yAxis: [
                    { // Watt
                        title: {
                            text: 'Watt',
                            style: {
                                color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>',
                            },
                            visible: false,
                        },
                        // min: 0,
                        labels: {
                            format: '{value}kW',
                            style: {
                                color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>',
                            },
                            formatter: function () {
                                return this.value / 1000 + "kW";
                            }
                        },
                        gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',

                    },
                    { // cum kWh
                        title: {
                            text: 'Total',
                            style: {
                                color: '#<?php echo $colors['color_chart_title_yaxis2'] ?>',
                            },
                        },
                        labels: {
                            format: '{value} kWh',
                            style: {
                                color: '#<?php echo $colors['color_chart_labels_yaxis2'] ?>',
                            },
                            formatter: function () {
                                return this.value / 1000 + "kWh";
                            },
                        },
                        gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis2'] ?>',
                        opposite: true,
                        visible: <?php echo $show_cum_axis ?>,
                    },
                    { // temperature
                        title: {
                            text: 'Temperature',
                            style: {
                                color: '#<?php echo $colors['color_chart_title_yaxis3'] ?>',
                            },
                        },
                        labels: {
                            format: '{value}°C',
                            style: {
                                color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>',
                            },
                            formatter: function () {
                                return this.value + "°C";
                            },
                        },
                        gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis3'] ?>',
                        opposite: true,
                        visible: <?php echo $show_temp_axis ?>,
                        steps: 5,
                        min: temp_min,
                        max: temp_max,
                    }
                ],

                plotOptions: {
                    area: {
                        fillColor: {
                            linearGradient: [0, 0, 0, 300],
                            stops: [
                                [0, col1],
                                [1, col2],
                            ]
                        },
                        marker: {
                            radius: 2,
                            enabled: false,
                        },
                        lineWidth: 1,
                        states: {
                            hover: {
                                lineWidth: 1
                            }
                        },
                        threshold: 0,
                    },

                    series: {}
                },
                tooltip: {
                    crosshairs: [true],
                    shared: true,
                    pointFormat: '<span style="color:{point.color}">\u25CF<\/span> {series.name}: <b>{point.y} {point.unit}<\/b><br/>',
                },
                series: [
                    {
                        type: 'area',
                        name: 'Watt',
                        data: data_series,
                        color: '#0909D6',

                    },
                    {
                        type: 'spline',
                        name: 'Max',
                        data: max_serie,
                        color: '#<?php echo $colors['color_chart_max_line'] ?>',
                    },
                    {
                        type: 'spline',
                        name: 'Cum',
                        data: cum_serie,
                        yAxis: 1,
                        color: '#<?php echo $colors['color_chart_cum_line'] ?>',
                    },
                    {
                        type: 'spline',
                        name: 'Temp',
                        data: temp_serie,
                        yAxis: 2,
                        color: '#<?php echo $colors['color_chart_temp_line'] ?>',
                    }
                ]
            }))
        ;

        $("#mycontainer").resize(function () {
            mychart.reflow();
        });


    })
    ;


</script>


