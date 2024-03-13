<?php
// work internally with UTC, converts values from DB if needed from localDateTime to UTC
global $params, $con, $formatter, $colors, $chart_options, $chart_lang;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once "chart_support.php";

$inverter_name = "";
if (isset($_GET['date'])) {
    $chartdatestring = html_entity_decode($_GET['date']);
    $chartdate = strtotime($chartdatestring);
} else {
    $chartdate = $_SESSION['CHARTDATE'] ?? time();
}
$chartdatestring = date("Y-m-d", $chartdate);
$dateTimeUTC = date("Y-m-d 00:00:00", $chartdate);;

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
// -----------------------------  get data from DB -----------------------------------------------------------------
// query for the day-curve
$utcDateArray = array();
$valarray = array();
$all_valarray = array();
$inveter_list = array();
$sql = "SELECT SUM( Geg_Dag ) AS gem, naam, Datum_Dag" .
    " FROM " . TABLE_PREFIX . "_dag " .
    " WHERE Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' " .
    " GROUP BY Datum_Dag, naam " .
    " ORDER BY Datum_Dag ASC";

$result = mysqli_query($con, $sql) or die("Query failed. dag " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $formatter->setPattern('d LLLL yyyy');
    $datum = getTxt("nodata") . datefmt_format($formatter, $chartdate);
} else {
    $formatter->setPattern('d LLL yyyy');
    $datum = datefmt_format($formatter, $chartdate);

    while ($row = mysqli_fetch_array($result)) {
        $db_datetime_str = $row['Datum_Dag']; // date string from DB in UTC or local DateTime
        $inverter_name = $row['naam'];
        $dateTimeUTC = convertLocalDateTime($db_datetime_str); // date converted in UCT
        $unixTimeUTC = convertToUnixTimestamp($dateTimeUTC); // unix timestamp in UTC
        $utcDateArray[] = $dateTimeUTC;
        $all_valarray[$unixTimeUTC] [$inverter_name] = $row['gem'];

        if (!in_array($inverter_name, $inveter_list)) {
            if (in_array($inverter_name, PLANT_NAMES)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        }
    }
}
// get best day for current month (max value over all years for current month)
// Charts will calculate the max kWh
// todo: filter on active plants with e.g. naam in ("SEEHASE", "TILLY") for safety
$sqlmaxdag = "
SELECT Datum_Maand, sum(Geg_Maand) as sum FROM " . TABLE_PREFIX . "_maand WHERE MONTH(Datum_Maand)='" . date('m', $chartdate) . "' " . " GROUP BY Datum_maand ORDER BY `sum` DESC limit 1";
$resultmaxdag = mysqli_query($con, $sqlmaxdag) or die("Query failed. dag-max " . mysqli_error($con));
$maxdag = date("m-d", time());
if (mysqli_num_rows($resultmaxdag) > 0) {
    while ($row = mysqli_fetch_array($resultmaxdag)) {
        $maxdag = $row['Datum_Maand'];

    }
}
$nice_max_date = date("Y-m-d", strtotime($maxdag));
//query for the best day
$all_valarraymax = array();
$sqlmdinv = "SELECT Geg_Dag AS gem, Datum_Dag, Naam FROM " . TABLE_PREFIX . "_dag WHERE Datum_Dag LIKE  '" .
    date("Y-m-d", strtotime($maxdag)) . "%' ORDER BY Datum_Dag, Naam ASC";
$resultmd = mysqli_query($con, $sqlmdinv) or die("Query failed. dag-max-dag " . mysqli_error($con));
$maxdagpeak = 0;
if (mysqli_num_rows($resultmd) != 0) {
    $maxdagpeak = 0;
    while ($row = mysqli_fetch_array($resultmd)) {
        $inverter_name = $row['Naam'];
        $time_only = substr($row['Datum_Dag'], -9);

        $today_max = $chartdatestring . $time_only; // current chart date string + max time
        $today_max_utc = convertLocalDateTime($today_max); // date in UTC
        $today_max_unix_utc = convertToUnixTimestamp($today_max_utc); // unix timestamp in UTC

        $all_valarraymax[$today_max_unix_utc] [$inverter_name] = $row['gem'];
        if ($row['gem'] > $maxdagpeak) {
            $maxdagpeak = $row['gem'];
        }
    }
}
$nice_last_date = convertToLocalDateTime($dateTimeUTC, "H:i");
// -----------------------------  build data for chart -----------------------------------------------------------------

$strgegmax = "";
$strsomkw = "";
$myColors = colorsPerInverter();
$strdataseries = "";
$max_first_val = PHP_INT_MAX;
$max_last_val = 0;
$cnt = 0;
$totalDay = 0.0;
$labels = convertValueArrayToDataString($utcDateArray);
$labels = convertValueArrayToDataString(array_keys($all_valarray));

// day max line per inverter --------------------------------------------------------------
$strdatamax = "";
$cnt = 0;
$inverterCount = 0;
$inverterAverage = 0;
$totalsumCumArray = array();

foreach (PLANT_NAMES as $key => $inverter_name) {
    $myColor1 = $myColors[$inverter_name]['min'];
    $myColor2 = $myColors[$inverter_name]['max'];
    $strdata = "";
    $cumData = "";
    $cumSum = 0;
    $inverterCount++;
    foreach ($all_valarray as $time => $valarray) {
        if (!isset($valarray[$inverter_name])) $valarray[$inverter_name] = 0;
        $timeInMillis = $time * 1000;
        $strdata .= '{x:' . $timeInMillis . ', y:' . $valarray[$inverter_name] . '},';
        $cumSum += ($valarray[$inverter_name] / 12);
        $cumData .= " { x: $timeInMillis, y: $cumSum},";
        if (!isset($totalsumCumArray[$timeInMillis])) {
            $totalsumCumArray[$timeInMillis] = 0.0;
        }
        $totalsumCumArray[$timeInMillis] = $totalsumCumArray[$timeInMillis] + $cumSum;
        $totalDay += $valarray[$inverter_name];
        // remember first and last date
        if ($max_first_val > $time) {
            $max_first_val = $time;
        }
        if ($max_last_val < $time) {
            $max_last_val = $time;
        }
    }
    // Day line
    $strdataseries .= " {
                    datasetId: '" . $inverter_name . "', 
                    label: '" . $inverter_name . "', 
                    inverter: '" . $inverter_name . "', 
                    type: 'line',                               
                    stack: 'Stack-DATA',
                    borderWidth: 1,
                    data: [" . $strdata . "],                    
                    dataCUM: [" . "$cumData" . "],
                    dataMAX: [], 
                    dataREF: [],
                    averageValue: 0,
                    expectedValue: 0,
                    maxIndex: 0,
                    fill: true,
                    pointStyle: false,                                         
                    backgroundColor: function(context) {                         
                       var gradientFill = ctx.createLinearGradient(0, 0, 0, 500);                                   
                       gradientFill.addColorStop(0, " . $myColor1 . ");
                       gradientFill.addColorStop(1, " . $myColor2 . ");
                       return gradientFill;
                    },
                    yAxisID: 'y',
                    xAxisID: 'x',
                    isData: true,
                    order: 10,
                    legendOrder: " . $inverterCount . ",
                },
    ";

    // max line per inverter
    $strdatamax = "";
    foreach ($all_valarraymax as $time => $valarraymax) {
        $cnt++;
        if ($cnt == 1) {
            // remember first date
            $max_first_val = $time;
        }
        if (!isset($valarraymax[$inverter_name])) $valarraymax[$inverter_name] = 0;
        $strdatamax .= '{x:' . ($time * 1000) . ', y:' . $valarraymax[$inverter_name] . '},';
    }
    // Max line
    $strdataseries .= " {
                    datasetId: 'max-" . $inverter_name . "', 
                    label: '" . getTxt("max") . " - " . $inverter_name . "', 
                    type: 'line',                               
                    stack: 'Stack-MAX',
                    borderWidth: 1,
                    data: [" . $strdatamax . "],                    
                    averageValue: 0,
                    expectedValue: 0,
                    maxIndex: 0,
                    fill: false,
                    pointStyle: false, 
                    borderColor: '" . $colors['color_chart_max_line'] . "',    
                    yAxisID: 'y',
                    xAxisID: 'x',
                    isData: false,
                    order: 1,
                    legendOrder: " . $inverterCount + 100 . ",
                },
    ";
}

// cumulative
$strdataseries .= " {
                    order: 10,  
                    datasetId: 'cum', 
                    label: '" . getTxt("cum") . "', 
                    type: 'line',      
                    stack: 'Stack 1',                                                                 
                    data: [" . convertKeyValueArrayToDataString($totalsumCumArray) . "],
                    fill: false,                    
                    borderColor: '" . $colors['color_chart_cum_line'] . "',                
                    borderWidth: 1,
                    pointStyle: false,   
                    yAxisID: 'y-axis-cum',      
                    xAxisID: 'x',                 
                    showLine: true,
                    isData: false,       
                    order: 2,        
                    legendOrder: " . $inverterCount + 200 . ",
                },
    ";

$str_temp_vals = "";
$temp_unit = "°C";
$val_max = 0;
$val_min = 0;
if ($params['useWeewx']) {
    include ROOT_DIR . "/charts/temp_sensor_inc.php";
}

// Temperature line if available
if (strlen($str_temp_vals) > 0) {
    $strdataseries .= " {
                    datasetId: 'temperature', 
                    label: '" . getTxt("temperature") . "', 
                    type: 'line',                                                   
                    borderWidth: 1,
                    data: [" . $str_temp_vals . "],                    
                    averageValue: 0,
                    expectedValue: 0,
                    maxIndex: 0,
                    fill: false,
                    pointStyle: false, 
                    borderColor: '" . $colors['color_chart_temp_line'] . "',    
                    yAxisID: 'y-temperature',
                    xAxisID: 'x',
                    isData: false,
                    order: 1,
                },
    ";
}

$show_legende = "true";
if ($isIndexPage) {
    echo '  <div class = "index_chart" id="mycontainer">
                <canvas id="day_chart_canvas"></canvas>
            </div>';
    $show_legende = "false";
}
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
$maxlink = '<a href= ' . HTML_PATH . 'pages/day.php' . $paramstr_day . 'date=' . $nice_max_date .
    '><span style="font-family:Arial,Verdana;font-size:12px;color:' . $colors['color_chart_text_subtitle'] .
    ' ;">' . $nice_max_date . '</span></a>';

$show_temp_axis = "false";
$show_cum_axis = "true";
if (strlen($str_temp_vals) > 0) {
    $show_temp_axis = "true";
    $show_cum_axis = "false";
}

// build subtitle ------------------------------
$lastValue = end($all_valarray);
if ($lastValue) {
    $sumLast = array_sum($lastValue);
} else {
    $sumLast = 0;
    $totalDay = 1;
}
$percent = round(100 * $sumLast / ($totalDay / 12), 0);
$sumsArray = array();
foreach ($all_valarray as $time => $valarray) {
    $sumsArray[] = array_sum($valarray);
}
if (count($sumsArray) > 0) {
    $peak = max($sumsArray);
} else {
    $peak = 0;
}
$subtitle = '["' . getTxt("today") . ": " . $nice_last_date . " - $sumLast W = $percent % - " . getTxt("peak") . ": $peak W" .
    '", "' . getTxt("total") . ":" . round(($totalDay / 12000), 1) . " kWh MAX: " . $nice_max_date . '"]';

echo "";
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="<?= HTML_PATH ?>inc/js/chart_support.js"></script>
<script>

    $(function () {

            const ctx = document.getElementById('day_chart_canvas').getContext("2d");

            Chart.defaults.color = '<?= $colors['color_chart_text_title'] ?>';
            new Chart(ctx, {
                data: {
                    labels: [],
                    datasets: [<?= $strdataseries  ?>],
                    myColors: <?= json_encode(colorsPerInverterJS()) ?>,
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            type: "time",
                            time: {
                                unit: 'hour',
                                tooltipFormat: 'yyyy-MM-dd HH:mm',
                                displayFormats: {
                                    hour: 'HH:mm'
                                }
                            },
                            ticks: {
                                stepSize: 1,
                            }
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Power (kWp)'
                            },
                            ticks: {
                                callback: function (value, index, ticks) {
                                    return (value / 1000).toFixed(1)
                                },
                                count: 5,
                            }
                        },
                        'y-temperature': {
                            stacked: false,
                            position: 'right',
                            display: <?= $show_temp_axis ?>,
                            title: {
                                display: true,
                                text: '<?= getTxt("temperature") ?> (°C)'
                            },
                            ticks: {
                                callback: function (value, index, ticks) {
                                    return value.toFixed(1) + '°C';
                                },
                                major: {
                                    enabled: true,
                                },
                                count: 5,
                            }
                        },
                        x1: {
                            offset: false,
                            display: false,
                        },
                        'y-axis-cum': {
                            type: 'linear',
                            min: 0,
                            display: <?= $show_cum_axis ?>,
                            position: 'right',
                            // grid line settings
                            grid: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                            title: {
                                display: true,
                                text: '<?= getTxt("total") ?> kWp'
                            },
                            ticks: {
                                callback: function (value, index, ticks) {
                                    return (value / 1000).toFixed(0);
                                },
                                count: 5,
                            },
                            stacked: true,

                        },
                    },
                    plugins: {
                        customCanvasBackgroundColor: {
                            color: '<?= $colors['color_chartbackground'] ?>',
                        },
                        legend: {
                            display: <?= $show_legende ?>,
                            position: 'bottom',
                            labels: {
                                filter: item => !item.text.includes('line'),
                                sort: function (li0, li1, chartData) {
                                    let chart = chartData;
                                    return (chart.datasets[li0.datasetIndex].legendOrder - chart.datasets[li1.datasetIndex].legendOrder)
                                },
                            },
                            onClick: getCustomLegendClickHandler(),
                        },
                        subtitle: {
                            display: true,
                            text: <?= $subtitle ?>,
                            padding: {top: 5, left: 0, right: 0, bottom: 3},
                        },
                    },
                    onClick: (event, elements, chart) => {
                        if (elements[0]) {
                            const i = elements[0].index;
                            const url = chart.data.datasets[0].data[i].url;
                            if (url.length > 0) {
                                location.href = url;
                            }
                        }
                    }
                },
                plugins: [getPlugin()],
            });
        }
    )

</script>
