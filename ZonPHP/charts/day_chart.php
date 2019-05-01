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

$showAllInverters = false;
$inverter_id = $inverter;
$inverter_clause = " AND Naam='" . $inverter . "' ";
if ((isset($_POST['type']) && ($_POST['type'] == "all")) ||
    (isset($_GET['type']) && ($_GET['type'] == "all"))) {
    $showAllInverters = true;
    $inverter_id = "all";
    $inverter_clause = " ";
}


$sqlref = "SELECT *
	FROM " . $table_prefix . "_refer
	WHERE Month(Datum_Refer)='" . date("m", $chartdate) . "'" . $inverter_clause;


$resultref = mysqli_query($con, $sqlref) or die("Query failed. dag-ref " . mysqli_error($con));
if (mysqli_num_rows($resultref) == 0)
    $frefmaand = 1;
else {
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand = $row['Dag_Refer'];
    }
}

$valarray = array();
$all_valarray = array();
$inveter_list = array();


$sql = "SELECT SUM( Geg_Dag ) AS gem, naam, 
	 STR_TO_DATE( CONCAT( DATE( Datum_Dag ) , ' ',HOUR( Datum_Dag ) , ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" . $param['isorteren'] . " ) *" . $param['isorteren'] . ", 2, '0' ) , ':00' ) , '%Y-%m-%d %H:%i:%s' ) AS datumtijd ".
    " FROM " .  $table_prefix . "_dag ".
    " WHERE Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' ". $inverter_clause .
    " GROUP BY datumtijd, naam ".
    " ORDER BY datumtijd ASC";

$result = mysqli_query($con, $sql) or die("Query failed. dag " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $datum = strftime("%d %B %Y", $chartdate);

    $tlaatstetijd = time();
    $geengevdag = 0;
    $agegevens[] = 0;
    $aoplopendkwdag[] = 0;
} else {
    $geengevdag = 1;
    $fsomoplopend = 0;
    while ($row = mysqli_fetch_array($result)) {

        $inverter_name = $row['naam'];
        $tlaatstetijd = strtotime($row['datumtijd']);


        $agegevens[date("H:i", strtotime($row['datumtijd']))] = $row['gem'];

        $valarray[strtotime($row['datumtijd'])] = $row['gem'];
        $all_valarray[ strtotime($row['datumtijd'])] [$inverter_name]  = $row['gem'];
        $fsomoplopend += $row['gem'] * 1000 / (1000 * 60 / $param['isorteren']);
        $aoplopendkwdag[strtotime($row['datumtijd'])] = $fsomoplopend;

        if (!in_array($inverter_name, $inveter_list)){
            $inveter_list[] = $inverter_name;
        } ;
    }

    $datum = strftime("%d %B %Y", $chartdate);
}
//--------------------------------------------------------------------------------------------------
// get best day for current month (max value over all years for current month
$sqlmaxdag = "SELECT Datum_Maand, Geg_Maand
	 FROM " . $table_prefix . "_maand
	 JOIN (SELECT month(Datum_Maand) AS maand, max(Geg_Maand) AS maxgeg FROM " . $table_prefix . "_maand WHERE 
     DATE_FORMAT(Datum_Maand,'%m')='" . date('m', $chartdate) . "' ".  $inverter_clause . " GROUP BY maand )AS maandelijks ON (month(" .
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
	 WHERE Datum_Maand LIKE  '" .date("Y-m-d", strtotime($maxdag)) . "%' 
	 ORDER BY Naam ASC ";
//echo $sqlmaxkwh;

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
WHERE Datum_Dag LIKE  '" .date("Y-m-d", strtotime($maxdag)) . "%'
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
        $all_valarraymax[ strtotime($row['datumtijd'])] [$inverter_name]  = $row['gem'];
        $adatum_max[] = $row['datumtijd'];
        $agegevensdag_max[strtotime($row['datumtijd'])] = $row['gem'];
        if ($row['gem'] > $maxdagpeak) {
            $maxdagpeak =  $row['gem'];
        };
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

$external_sensors = isset($param['external_sensors']);

if ($external_sensors) {
    $tablename = $table_prefix . "_sensordata";
    $result = mysqli_query($con, "SHOW TABLES LIKE '" . $tablename . "'");
    if ($result->num_rows == 0) {
        $external_sensors = false;
    }
}
$sensor_available = ($external_sensors == true) || ($use_weewx == true);

$val_min = 500;
$val_max = -500;

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
            // array time = value
            if ($weewx_temp_is_farenheit) {
                $val = number_format(($row['val'] - 32) * 5 / 9, 1); // F --> °C
                $temp_unit = "°C";
            } else {
                $val = number_format($row['val'] , 1); // temp is already in °C
                $temp_unit = "°F";
            }
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
        };
    }

// ---SENSOR -----------------------------------------------------------------------------------------------------------
}


$strgegmax = "";
$strsomkw = "";
$str_dataserie = "";

foreach ($valarray as $time => $val) {
    if (isset($param['no_units'])) {
        $str_dataserie .= '[' . ($time * 1000) . ',' . $val . '] ,';
    } else {
        $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $val . ' , unit: \'W\'} ,';
    }
}
// strip last ","
if (strlen($str_dataserie > 0)) {
    $str_dataserie = substr($str_dataserie, 0, -1);
}

// build colors per inverter array
$myColors = array();
for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
    $col1 = "color_inverter" . $k ."_chartbar_min";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k ."_chartbar_max";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['max'] = $col1;
}


$strgeg = "";
$cnt = 0;
foreach ($inveter_list as $inverter_name)
{
    $col1 =$myColors[$inverter_name]['min'];
    $col2 =$myColors[$inverter_name]['max'];

    $strgeg .= "{ name: '$inverter_name', id: '$inverter_name', type: 'area', marker: { enabled: false }, color: { linearGradient: {x1: 0, x2: 0, y1: 0, y2: 1}, stops: [ [0, $col1], [1, $col2]] },                        
    data:[";

    foreach ($all_valarray as $time => $valarray) {

        if (!isset($valarray[$inverter_name])) $valarray[$inverter_name] = 0;

        if (isset($param['no_units'])) {
            $strgeg .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name].'}, ';
        } else {
            $strgeg .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name] . ', unit: \'W\'}, ';
        }
    }
    $strgeg=substr($strgeg,0,-1);
    $strgeg.="]}, 
                    ";
    $cnt++;
}

$str_dataserie = $strgeg;


// day max line per inverter --------------------------------------------------------------
$str_max = "";
$cnt = 0;


foreach ($sNaamSaveDatabase as $inverter_name)
{
    $str_max .= "{ name: '$inverter_name max', linkedTo: '$inverter_name', color : '#15ff24', lineWidth: 1,  type: 'line', marker: { enabled: false },                           
    data:[";

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
            $str_max .= '{x:' . ($newDate * 1000) . ', y:' . $valarraymax[$inverter_name].'}, ';
        } else {
            $str_max .= '{x:' . ($newDate * 1000) . ', y:' . $valarraymax[$inverter_name] . ', unit: \'W\'}, ';
        }
    }
    $str_max=substr($str_max,0,-1);

    $str_max.="]}, 
                    ";
    $cnt++;
}
// remember last date
$max_last_val = $newDate;
$str_max = substr($str_max, 0, -1);
$strgegmax = substr($strgegmax, 0, -1);

// temp line --------------------------------------------------------------
$str_temp_vals = "";
if ($sensor_success) {
    $str_temp_vals = "";
    foreach ($temp_vals as $time => $val) {
        if (($time > $max_first_val) && ($time < $max_last_val)) {
            if (isset($param['no_units'])) {
                $str_temp_vals .= "[" . $time * 1000 . "," . number_format($val, 1, '.', '') . "],";
            } else {
                $str_temp_vals .= "{x:" . $time * 1000 . ", y:" . number_format($val, 1, '.', '') . ", unit: '".$temp_unit."' },";
            }
        }
    }
    $str_temp_vals = substr($str_temp_vals, 0, -1);
}

// cumulative line --------------------------------------------------------------
$str_cum = "";
$cnt = 0;
$cum_max_value = 0;
foreach ($aoplopendkwdag as $tuur => $fkw) {
    $cnt++;
    $fkw = $fkw / 1000;
    if (isset($param['no_units'])) {
        $strtemp = "[" . ($tuur * 1000) . ", " . number_format($fkw, 1, '.', '') . "]";
    } else {
        $strtemp = "{x:" . ($tuur * 1000) . ", y:" . number_format($fkw, 1, '.', '') . ", unit: 'kWh' }";
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


$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="mycontainer_' . $inverter_id . '"></div>';
    $show_legende = "false";
}

// get query parameters
$paramstr_day = "";
if (sizeof($_GET) > 0){
    foreach ($_GET as $key => $value) {
        if ( $key != "dag") {
            $paramstr_day .= $key . "=" . $value . "&";
        }
    }
}
if (strpos($paramstr_day, "?") == 0) {
    $paramstr_day = '?' . $paramstr_day;
}
$maxlink = '<a href=\"day_overview.php' . $paramstr_day . 'dag=' . $nice_max_date . '\">' . $nice_max_date . '</a>';

// --------------
$subtitle = '"<b>' . $txt['actueel'] . ": <\/b> " . date("H:i", $tlaatstetijd) . " - " . number_format(end($agegevens), 0, ',', '.') . "W="
    . number_format(100 * end($agegevens) / $ieffectiefkwpiek, 0, ',', '.') . "% - "
    . $txt['peak'] . ": ". number_format(max($agegevens), 0, ",", ".") . "W";
if ($isIndexPage) {
    $subtitle .= "<br >";
}
$subtitle .= "<b> " . $txt['totaal'] . ": <\/b>" . number_format((end($aoplopendkwdag) / 1000), 2, ',', '.') . "kWh="
    . number_format(end($aoplopendkwdag) / ($ieffectiefkwpiek / 1), 1, ',', '.') . "kWh/kWp ";

if ($isIndexPage) {
    $subtitle .= "<br >";
}
/// $subtitle .= "     <b>" . $txt['max'] .": <\/b>" .$maxlink. " --> ". $maxkwh. "kWh - ". $txt['peak'] .": " . number_format(max($agegevensdag_max), 0, ",", ".") . "W" .'' . '"';

//--------------------
include_once "chart_styles.php";

$show_temp_axis = "false";
$show_cum_axis = "true";
if (strlen($str_temp_vals) > 0) {
    $show_temp_axis = "true";
    $show_cum_axis = "false";
}

?>


<script type="text/javascript">
    $(function () {

        var myoptions = <?php echo $chart_options ?>;
        var khhWp = [<?php echo $param['ieffectief_kwpiekst'] ?>];
        var maxmax = <?php echo json_encode($maxkwh) ?>;
        var maxlink = '<?php echo $maxlink ?>';
        var txt_actueel = '<?php echo $txt['actueel'] ?>';
        var txt_totaal = '<?php echo $txt['totaal'] ?>';
        var txt_max = '<?php echo $txt['max'] ?>';
        var txt_peak = '<?php echo $txt['peak'] ?>';

        Highcharts.setOptions({<?php echo $chart_lang ?>});

        var mychart = new Highcharts.chart('mycontainer_<?php echo $inverter_id ?>', Highcharts.merge(myoptions, {

            chart: {

                events: {
                    render() {
                        mychart = this;
                        series = this.series;
                        sum1 = 0;
                        sum2 = 0;
                        kWh1 = 0;
                        kWh2 = 0;
                        ax1 = 0;
                        ax2 = 0;
                        max1 = 0;
                        max2 = 0;
                        current = 0;
                        tota = 0;
                        // subtitle cumulatief
                        for (i = 0; i < 2; i++) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    tota += (series[i].data[j].y) / 12000;
                                }
                            }
                        }
                        //	subtitle current per inverter, highest inverter number first
                        i = 1;
                        if (series[i].visible) {
                            sum2 = (series[i].data[series[i].data.length - 1]).y;
                            current = Highcharts.dateFormat('%H:%M', (series[i].data[series[i].data.length - 1]).x);
                            kWh2 = khhWp[i];
                            max2 = maxmax[i];
                            ax2 = series[i].dataMax;
                        }

                        i = 0;
                        if (series[i].visible) {
                            sum1 = (series[i].data[series[i].data.length - 1]).y;
                            current = Highcharts.dateFormat('%H:%M', (series[i].data[series[i].data.length - 1]).x);
                            kWh1 = khhWp[i];
                            max1 = maxmax[i];
                            ax1 = series[i].dataMax - ax2;
                        }

                        sum = sum1 + sum2;
                        KWH = kWh1 + kWh2;
                        AXI = ax1 + ax2;
                        MAX = max1 + max2;
                        this.setSubtitle({
                            text: "<b>" + txt_actueel + ": </b>" + current + " -  " + Highcharts.numberFormat(sum, 0, ",", "") +
                                "W" + "=" + (Highcharts.numberFormat(100 * sum / KWH, 0, ",", "")) + "%" + " - " + txt_peak + ": " + AXI + "W <b>" +
                                txt_totaal + ":</b> " + (Highcharts.numberFormat(tota, 2, ",", "")) + "kWh = " +
                                (Highcharts.numberFormat((tota / KWH) * 1000, 2, ",", "")) + "kWh/kWp" + " <b>" +
                                txt_max + ": </b>" + maxlink + "--> " + (Highcharts.numberFormat(MAX, 2, ",", "")) + " kWh"
                        }, false, false);

                        total = [];
                        value = 0;
                        indexOfVisibleSeries = [];
                        checkHideForSpline = 1;

                        if (mychart.forRender) {
                            mychart.forRender = false;

                            //function to check amount of visible series and to destroy old spline series
                            mychart.series.forEach(s => {
                                if (s.type === 'spline' && s.visible === true) {
                                    s.destroy()
                                } else if (s.type === 'spline' && s.visible === false) {
                                    checkHideForSpline = 0
                                }
                            if (s.type === 'area' && s.visible) {
                                indexOfVisibleSeries.push(s.index);
                            }
                            });

                            if (checkHideForSpline) {
                                for (i = 0; i < mychart.series[0].data.length; i++) {
                                    for (j of indexOfVisibleSeries) {
                                        value += mychart.series[j].data[i].y / 12000;
                                        axis = mychart.series[j].data[i].x;
                                    }
                                    total.push([axis, value])
                                }

                                mychart.addSeries({
                                    data: total,
                                    name: 'Cum',
                                    yAxis: 1,
                                    unit: 'kWh',
                                    type: "spline",
                                    color: '#<?php echo $colors['color_chart_cum_line'] ?>'
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
                pointFormatter() {
                    unit = this.unit;
                    value = this.y;
                    //if unit is undefined (added series) set unit to 'kWh' and value to two decimals
                    if (!unit) {
                        unit = 'kWh';
                        value = Highcharts.numberFormat(this.y, '2', ',');
                    }
                    ;

                    return `<span style="color:${this.color}">\u25CF<\/span> ${this.series.name}: <b>${value} ${unit}<\/b><br/>`;
                }
            },
            plotOptions: {
                line: {stacking: 'normal'},
                area: {
                    marker: {
                        radius: 2,
                        enabled: false
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: 0,
                    stacking: 'normal'
                }

            },
            subtitle: {
                style: {
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
                title: {
                    text: 'Watt',
                    style: {
                        color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>'
                    },
                    visible: false
                },
                // min: 0,
                labels: {
                    format: '{value}kW',
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>'
                    },
                    formatter: function () {
                        return this.value / 1000 + "kW";
                    }
                },
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>'
            },
                { // cum kWh
                    title: {
                        text: 'Total',
                        style: {
                            color: '#<?php echo $colors['color_chart_title_yaxis2'] ?>'
                        }
                    },
                    labels: {
                        format: '{value} kWh',
                        style: {
                            color: '#<?php echo $colors['color_chart_labels_yaxis2'] ?>'
                        },
                        formatter: function () {
                            return this.value + "kWh";
                        }
                    },
                    gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis2'] ?>',
                    opposite: true,
                    visible: <?php echo $show_cum_axis ?>
                }
            ],

            series: [ <?php echo $str_dataserie ?> <?php echo $str_max ?> ]

        }), function(mychart) {
            mychart.forRender = true
        });

        $('#mycontainer_<?php echo $inverter_id ?>').resize(function () {
            mychart.reflow();
        });
    });
</script>


<script type="text/javascript">

</script>