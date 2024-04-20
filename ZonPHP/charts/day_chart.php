<?php
// work internally with UTC, converts values from DB if needed from localDateTime to UTC
global $params, $con, $formatter, $colors, $chart_options, $chart_lang, $plantNames;
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
$dateTimeUTC = date("Y-m-d 00:00:00", $chartdate);

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
// -----------------------------  get data from DB -----------------------------------------------------------------
$max_first_val = PHP_INT_MAX;
$max_last_val = 0;
// query for the day-curve
$utcDateArray = array();
$allValuesPerInverter = array();
$dataZonPHP = array();

$sql = "SELECT SUM( Geg_Dag ) AS gem, naam, Datum_Dag" .
    " FROM " . TABLE_PREFIX . "_dag " .
    " WHERE Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' " .
    " GROUP BY Datum_Dag, naam " .
    " ORDER BY Datum_Dag ASC";
// todo: filter on active plants with e.g. naam in ("SEEHASE", "TILLY") for safety

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
        $utcDateArray[] = $unixTimeUTC;
        $allValuesPerInverter[$inverter_name][$unixTimeUTC] = $row['gem'];
        // remember first and last date
        if ($max_first_val > $unixTimeUTC) {
            $max_first_val = $unixTimeUTC;
        }
        if ($max_last_val < $unixTimeUTC) {
            $max_last_val = $unixTimeUTC;
        }
    }
}

// get best day for current month (max value over all years for current month)
// Charts will calculate the max kWh
$sqlmaxdag = "
SELECT Datum_Maand, sum(Geg_Maand) as sum FROM " . TABLE_PREFIX . "_maand WHERE MONTH(Datum_Maand)='" . date('m', $chartdate) . "' " . " GROUP BY Datum_maand ORDER BY `sum` DESC limit 1";
$resultmaxdag = mysqli_query($con, $sqlmaxdag) or die("Query failed. dag-max " . mysqli_error($con));
$maxdag = date("m-d", time());
if (mysqli_num_rows($resultmaxdag) > 0) {
    while ($row = mysqli_fetch_array($resultmaxdag)) {
        $maxdag = $row['Datum_Maand'];
    }
}
//query for the best day
$nice_max_date = date("Y-m-d", strtotime($maxdag));
$allValuesMaxDay = array();
$sqlmdinv = "SELECT Geg_Dag AS gem, Datum_Dag, Naam FROM " . TABLE_PREFIX . "_dag WHERE Datum_Dag LIKE  '" .
    date("Y-m-d", strtotime($maxdag)) . "%' ORDER BY Datum_Dag, Naam ASC";
$resultmd = mysqli_query($con, $sqlmdinv) or die("Query failed. dag-max-dag " . mysqli_error($con));

if (mysqli_num_rows($resultmd) != 0) {
    while ($row = mysqli_fetch_array($resultmd)) {
        $inverter_name = $row['Naam'];
        $time_only = substr($row['Datum_Dag'], -9);
        $today_max = $chartdatestring . $time_only; // current chart date string + max time
        $today_max_utc = convertLocalDateTime($today_max); // date in UTC
        $today_max_unix_utc = convertToUnixTimestamp($today_max_utc); // unix timestamp in UTC
        $allValuesMaxDay[$inverter_name][$today_max_unix_utc] = intval($row['gem']);
        // remember first and last date
        if ($max_first_val > $today_max_unix_utc) {
            $max_first_val = $today_max_unix_utc;
        }
        if ($max_last_val < $today_max_unix_utc) {
            $max_last_val = $today_max_unix_utc;
        }
    }
}

// -----------------------------  build data for chart -----------------------------------------------------------------
$myColors = colorsPerInverter();
$utcDateArray = array_unique($utcDateArray);
$labels = convertValueArrayToDataString($utcDateArray);

// day line per inverter --------------------------------------------------------------
$inverterCount = 0;
$totalsumCumArray = array();
$allDataSeriesString = "";
$plantNames = "";
foreach (PLANT_NAMES as $key => $inverter_name) {
    $plantNames .= "'$inverter_name',";
    $myColor1 = $myColors[$inverter_name]['min'];
    $myColor2 = $myColors[$inverter_name]['max'];
    $dataSeriesString = "";
    $cumDataString = "";
    $cumSum = 0;
    $inverterCount++;

    // all values for current inverter if set
    if (isset($allValuesPerInverter[$inverter_name])) {
        $inverterValues = $allValuesPerInverter[$inverter_name];

        // loop over all times from all inverters from min to max
       for ($time = $max_first_val; $time <= $max_last_val; $time += 300) {
            $currentInverterVal = "NaN";
            $timeInMillis = $time * 1000;
            if (isset($inverterValues[$time])) {
                $currentInverterVal = $inverterValues[$time];
                $cumSum += ($currentInverterVal / 12);
                $cumDataString .= "{x: $timeInMillis, y: $cumSum},";
            }
            $dataSeriesString .= '{x:' . $timeInMillis . ', y: ' . $currentInverterVal . '},';
            if (!isset($totalsumCumArray[$timeInMillis])) {
                $totalsumCumArray[$timeInMillis] = 0;
            }
            $totalsumCumArray[$timeInMillis] += $cumSum;
        }

        $dataZonPHP[$inverter_name]['totalValue'] = $cumSum;
        $dataZonPHP[$inverter_name]['peak'] = $params[$inverter_name]["capacity"];
        $dataZonPHP[$inverter_name]['lastDate'] = convertDateTimeToLocalDateTime(array_key_last($inverterValues), "H:i");
        $dataZonPHP[$inverter_name]['lastValue'] = end($inverterValues);
        $dataZonPHP[$inverter_name]['todayMax'] = max($inverterValues);
    }

    // Day line
    $allDataSeriesString .= " {
                    datasetId: '" . $inverter_name . "', 
                    label: '" . $inverter_name . "', 
                    inverter: '" . $inverter_name . "', 
                    type: 'line',                               
                    stack: 'Stack-DATA',
                    borderWidth: 1,
                    data: [" . $dataSeriesString . "],                    
                    dataCUM: [" . "$cumDataString" . "],
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
    $dataSeriesMaxString = "";
    $inverterMaxDayCumSum = 0;
    if (isset($allValuesMaxDay[$inverter_name])) {
        $inverterMaxValues = $allValuesMaxDay[$inverter_name];
        foreach ($inverterMaxValues as $time => $currentVal) {
            $dataSeriesMaxString .= '{x:' . ($time * 1000) . ', y:' . $currentVal . '},';
            $inverterMaxDayCumSum += $currentVal / 12;
        }
    }
    $dataZonPHP[$inverter_name]['maxDay'] = $nice_max_date;
    $dataZonPHP[$inverter_name]['maxDayValue'] = $inverterMaxDayCumSum;
    $allDataSeriesString .= " {
                    datasetId: 'max-" . $inverter_name . "', 
                    label: 'max-" . $inverter_name . "', 
                    type: 'line',                               
                    stack: 'Stack-MAX',
                    borderWidth: 1,
                    data: [" . $dataSeriesMaxString . "],                    
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
$allDataSeriesString .= " {
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
if ($params['useWeewx']) {
    include ROOT_DIR . "/charts/temp_sensor_inc.php";
}

// Temperature line if available
if (strlen($str_temp_vals) > 0) {
    $allDataSeriesString .= " {
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
$zoomEnabled = "true";
if ($isIndexPage) {
    echo '  <div class = "index_chart" id="mycontainer">
                <canvas id="day_chart_canvas"></canvas>
            </div>';
    $show_legende = "false";
    $zoomEnabled = "false";
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
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-crosshair@2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="<?= HTML_PATH ?>inc/js/chart_support.js"></script>
<script>


    $(function () {

            function buildSubtitle(ctx) {
                let chart = ctx.chart;
                let data = ctx.chart.data;
                let dataZonPHP = data.dataZonPHP;
                let txt = data.txt;
                let totalValue = 0;
                let lastDate = Date.now();
                let lastValue = 0;
                let todayMax = 0;
                let maxDay = Date.now();
                let maxDayValue = 0;
                let peak = 0;
                for (let i in data.datasets) {
                    let meta = chart.getDatasetMeta(i);
                    let dataset = chart.data.datasets[i];
                    let inverter = dataset.inverter;
                    let isHidden = meta.hidden === null ? false : meta.hidden;
                    if (dataset.isData && !isHidden) {
                        totalValue += parseInt(dataZonPHP[inverter].totalValue);
                        peak += parseInt(dataZonPHP[inverter].peak);
                        lastDate = dataZonPHP[inverter].lastDate;
                        lastValue += parseInt(dataZonPHP[inverter].lastValue);
                        todayMax += parseInt(dataZonPHP[inverter].todayMax);
                        maxDay = dataZonPHP[inverter].maxDay;
                        maxDayValue += parseInt(dataZonPHP[inverter].maxDayValue);
                    }
                }
                let kWp;
                if (peak === 0) {
                    kWp = totalValue;
                } else {
                    kWp = (totalValue / peak).toFixed(2);
                }

                return [txt["today"] + " " + lastDate + " - " + lastValue + "W - " + txt["peak"] + ": " + todayMax + "W",
                    txt["sum"] + " " + (totalValue / 1000).toFixed(2) + "kWh = " + kWp + "kWh/kWp  - MAX: " + maxDay + " - " + (maxDayValue / 1000).toFixed(2) + "kWh"];
            }

            function customDayLegendClick(e, legendItem, legend) {
                // myTest();
                let chart = legend.chart;
                Chart.defaults.plugins.legend.onClick(e, legendItem, legend);
                let data = chart.data;
                let cumSum = []
                for (let i in data.datasets) {
                    let meta = chart.getDatasetMeta(i);
                    let dataset = data.datasets[i];
                    let inverter = dataset.inverter;
                    let isHidden = meta.hidden === null ? false : meta.hidden;
                    if (dataset.isData) {
                        if (!isHidden) {
                            if (cumSum.length === 0) {
                                cumSum = cloneAndResetY(dataset.dataCUM)
                            }
                            for (let ii in dataset.data) {

                                // cum
                                if (dataset.dataCUM[ii].y != null) {
                                    cumSum[ii].y = cumSum[ii].y + dataset.dataCUM[ii].y;
                                }
                            }
                            let maxIDX = findDatasetById(data.datasets, "max-" + inverter);
                            if (maxIDX >= 0) {
                                chart.setDatasetVisibility(maxIDX, true);
                            }
                        } else {
                            // hide maxBar for invisible inverters
                            let maxIDX = findDatasetById(data.datasets, "max-" + inverter);
                            if (maxIDX >= 0) {
                                chart.setDatasetVisibility(maxIDX, false);
                            }
                        }
                    }
                }

                let cumIDX = findDatasetById(data.datasets, "cum");
                if (cumIDX > 0) {
                    data.datasets[cumIDX].data = cumSum;
                }

                chart.options.plugins.subtitle = {
                    text: buildSubtitle(legend),
                    display: true,
                };
                chart.update();
            }

            const zoomEnabled = <?= $zoomEnabled ?>;
            const ctx = document.getElementById('day_chart_canvas').getContext("2d");

            Chart.defaults.color = '<?= $colors['color_chart_text_title'] ?>';
            window.myChart = new Chart(ctx, {
                data: {
                    labels: [],
                    datasets: [<?= $allDataSeriesString  ?>],
                    dataZonPHP: <?= json_encode($dataZonPHP)  ?>,
                    inverters: [<?= $plantNames ?>],
                    myColors: <?= json_encode(colorsPerInverterJS()) ?>,
                    txt: <?= json_encode($_SESSION['txt']); ?>
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            offset: false,
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
                                text: 'Power (kW)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000).toFixed(1)
                                },
                                count: 5,
                            },
                            grid: {
                                drawOnChartArea: true,
                                offset: true
                            },
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
                                callback: function (value) {
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
                                text: '<?= getTxt("total") ?> kWh'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000).toFixed(2);
                                },
                                count: 5,
                            },
                            stacked: true,

                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                        axis: 'x'
                    },
                    plugins: {
                        customCanvasBackgroundColor: {
                            color: '<?= $colors['color_chartbackground'] ?>',
                        },
                        legend: {
                            display: <?= $show_legende ?>,
                            position: 'bottom',
                            labels: {
                                filter: item => !item.text.includes('max-'),
                                sort: function (li0, li1, chartData) {
                                    let chart = chartData;
                                    return (chart.datasets[li0.datasetIndex].legendOrder - chart.datasets[li1.datasetIndex].legendOrder)
                                },
                            },
                            onClick: customDayLegendClick,
                        },
                        subtitle: {
                            display: true,
                            text: function (ctx) {
                                return buildSubtitle(ctx)
                            },
                            padding: {top: 5, left: 0, right: 0, bottom: 3},
                        },

                        zoom: {
                            enabled: zoomEnabled,
                            pan: {
                                enabled: zoomEnabled,
                                modifierKey: 'shift',
                                mode: 'xy',
                            },
                            zoom: {
                                wheel: {
                                    enabled: false,
                                },
                                pinch: {
                                    enabled: zoomEnabled
                                },
                                mode: 'xy',
                                drag: {
                                    enabled: zoomEnabled,
                                    borderColor: 'rgb(54, 162, 235)',
                                    borderWidth: 1,
                                    backgroundColor: 'rgba(54, 162, 235, 0.3)'
                                }
                            }
                        },
                        crosshair: {
                            line: {
                                color: '#F66',  // crosshair line color
                                width: 1        // crosshair line width
                            },
                            zoom: {
                                enabled: false,  // disable crosshair zooming
                            },
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
