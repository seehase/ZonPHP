<?php
global $con, $params, $formatter, $colors, $chart_options;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once "chart_support.php";

if (isset($_GET['date'])) {
    $chartdatestring = html_entity_decode($_GET['date']);
    $chartdate = strtotime($chartdatestring);
} else {
    $chartdate = $_SESSION['CHARTDATE'] ?? time();
}
$chartdatestring = date("Y-m-d", $chartdate);

$maxMonthDay = 0;
$isIndexPage = false;
$showAllInverters = true;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

// -----------------------------  get data from DB -----------------------------------------------------------------
$current_year = date('Y', $chartdate);
$current_month = intval(date('m', $chartdate));
$current_year_month = date('Y-m', $chartdate);

// get reference values
$refValuePerMonth = array();
foreach (PLANT_NAMES as $plant) {
    $refValuePerMonth[$plant] = $params[$plant]['expectedYield'][$current_month - 1] / 30;
}

$DaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

$sql = "SELECT Datum_Maand, Geg_Maand, naam
        FROM " . TABLE_PREFIX . "_maand
        WHERE Datum_Maand >= '" . $current_year_month . "-01 00:00:00'
        AND Datum_Maand <= '" . $current_year_month . "-" . sprintf('%02d', $DaysPerMonth) . " 23:59:00'
        GROUP BY Naam, Datum_Maand, Geg_Maand
        ORDER BY Naam, Datum_Maand ASC";
$result = mysqli_query($con, $sql) or die("Query failed. maand " . mysqli_error($con));

$allValuesPerInverter = array();
$monthTotal = 0.0;
$formatter->setPattern('LLLL yyyy');
if (mysqli_num_rows($result) == 0) {
    $datum = getTxt("nodata") . datefmt_format($formatter, $chartdate);
} else {
    $datum = datefmt_format($formatter, $chartdate);

    for ($k = 0; $k < count(PLANT_NAMES); $k++) {
        for ($i = 1; $i <= $DaysPerMonth; $i++) {
            $allValuesPerInverter[PLANT_NAMES[$k]][$i] = 0;
        }
    }
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];
        $allValuesPerInverter[$inverter_name][date("j", strtotime($row['Datum_Maand']))] = $row['Geg_Maand'];
        $monthTotal += $row['Geg_Maand'];
    }
}

?>

<?php
// -----------------------------  build data for chart -----------------------------------------------------------------
$myColors = colorsPerInverter();
$myurl = HTML_PATH . "pages/day.php?date=";
$allDataSeriesString = "";
$labels = "";
$totalsumCumArray = array();
$dataZonPHP = array();
$sumAverage = 0.0;
$sumExpected = 0.0;
for ($i = 1; $i <= $DaysPerMonth; $i++) {
    $labels .= '"' . $i . '",';
    $totalsumCumArray[$i] = 0.0;
}

foreach (PLANT_NAMES as $inverter_name) {
    $dataSeriesString = "";
    $cumData = "";
    $cumSum = 0;
    $maxMonthValue = 0;
    $maxMonthDay = 0;
    $inverterExpected = $refValuePerMonth[$inverter_name];
    $sumExpected += $inverterExpected;
    $lastDayWithValues = 1;
    $inverterAverage = 0;

    if (isset($allValuesPerInverter[$inverter_name])) {
        $valuesPerMonth = $allValuesPerInverter[$inverter_name];
        $maxMonthValue = round(max($valuesPerMonth), 2);
        for ($i = 1; $i <= $DaysPerMonth; $i++) {
            if (isset($valuesPerMonth[$i])) {
                $val = round($valuesPerMonth[$i], 2);
            } else {
                $val = 0;
            }
            $formattedHref = sprintf("%s%04d-%02d-%02d", $myurl, $current_year, $current_month, $i);
            $dataSeriesString .= " { x: $i, y: $val, url: \"$formattedHref\"},";
            $cumSum += $val;
            $cumData .= " { x: $i, y: $cumSum},";
            $totalsumCumArray[$i] = $totalsumCumArray[$i] + $cumSum;
            if ($val == $maxMonthValue) {
                $maxMonthDay = $i;
            }
            if ($val > 0) {
                $lastDayWithValues = $i;
            }
        }
        $inverterAverage = array_sum($valuesPerMonth) / $lastDayWithValues;
        $sumAverage += $inverterAverage;
    }

    $dataZonPHP["date"] = $datum;
    $dataZonPHP[$inverter_name]['totalValue'] = $cumSum;
    $dataZonPHP[$inverter_name]['peak'] = intval($params[$inverter_name]["capacity"]);
    $dataZonPHP[$inverter_name]['max'] = $maxMonthValue;
    $dataZonPHP[$inverter_name]['avg'] = $inverterAverage;
    $dataZonPHP[$inverter_name]['ref'] = $refValuePerMonth[$inverter_name];

    $dataSeriesString = substr($dataSeriesString, 0, -1);
    $allDataSeriesString .= " {
                    datasetId: '" . $inverter_name . "', 
                    label: '" . $inverter_name . "', 
                    inverter: '" . $inverter_name . "',
                    type: 'bar',                               
                    stack: 'Stack 0',
                    borderWidth: 1,
                    data: [" . $dataSeriesString . "],                    
                    dataCUM: [" . $cumData . "],
                    dataMAX: [], 
                    dataREF: [],
                    averageValue: " . $inverterAverage . ",
                    expectedValue: " . $refValuePerMonth[$inverter_name] . ",
                    maxIndex: " . $maxMonthDay . ",
                    fill: true,
                    backgroundColor: customGradientBackground,
                    yAxisID: 'y',
                    xAxisID: 'x',
                    isData: true,
                },
    ";
}

// average
$allDataSeriesString .= " {
                    datasetId: 'avg', 
                    label: '" . getTxt("average") . "', 
                    type: 'line',      
                    stack: 'Stack 1',                                                                 
                    data: [" . buildConstantDataString($sumAverage, $DaysPerMonth) . "],
                    fill: false,
                    borderColor: '" . $colors['color_chart_average_line'] . "',
                    borderWidth: 1,
                    pointStyle: false,   
                    yAxisID: 'y',
                    xAxisID: 'x1',
                    fill: false,   
                    showLine: true,
                    isData: false,               
                },
    ";

// expected
$allDataSeriesString .= " {
                    datasetId: 'expected', 
                    label: '" . getTxt("ref") . "', 
                    type: 'line',      
                    stack: 'Stack 2',                                                                 
                    data: [" . buildConstantDataString($sumExpected, $DaysPerMonth) . "],
                    fill: false,
                    borderColor: '" . $colors['color_chart_reference_line'] . "',
                    borderWidth: 1,
                    pointStyle: false,   
                    yAxisID: 'y',
                    xAxisID: 'x1',
                    fill: false,   
                    showLine: true,
                    isData: false,               
                },
    ";

// cumulative
$allDataSeriesString .= " {
                    datasetId: 'cum', 
                    label: '" . getTxt("cum") . "', 
                    type: 'line',      
                    stack: 'Stack 1',                                                                 
                    data: [" . convertValueArrayToDataString($totalsumCumArray) . "],
                    fill: true,                    
                    backgroundColor: '" . $colors['color_chart_cum_fill'] . "',                
                    borderWidth: 1,
                    pointStyle: false,   
                    yAxisID: 'y1',      
                    xAxisID: 'x1',                 
                    showLine: false,
                    isData: false,               
                },
    ";

$show_legende = "true";
if ($isIndexPage) {
    echo '<div class = "index_chart" id="month_chart" style="background-color: ' . $colors['color_chartbackground'] . '">
              <canvas id="month_chart_canvas"></canvas>
          </div>';
    $show_legende = "false";
}
$monthTotal = round($monthTotal, 2);

?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-crosshair@2"></script>
<script src="<?= HTML_PATH ?>inc/js/chart_support.js"></script>
<script>
    $(function () {
            function buildSubtitle(ctx) {
                let chart = ctx.chart;
                let data = ctx.chart.data;
                let dataZonPHP = data.dataZonPHP;
                let txt = data.txt;
                let totalValue = 0;
                let peak = 0;
                let max = 0;
                let avg = 0;
                let ref = 0;
                let datetxt = dataZonPHP['date'];
                for (let i in data.datasets) {
                    let meta = chart.getDatasetMeta(i);
                    let dataset = chart.data.datasets[i];
                    let inverter = dataset.inverter;
                    let isHidden = meta.hidden === null ? false : meta.hidden;
                    if (dataset.isData && !isHidden) {
                        totalValue += parseInt(dataZonPHP[inverter].totalValue);
                        peak += parseInt(dataZonPHP[inverter].peak);
                        max += parseFloat(dataZonPHP[inverter].max);
                        avg += parseFloat(dataZonPHP[inverter].avg);
                        ref += parseFloat(dataZonPHP[inverter].ref);
                    }
                }
                let total_kWp;
                let max_kWp;
                if (peak === 0) {
                    total_kWp = totalValue;
                    max_kWp = max;
                } else {
                    total_kWp = (totalValue / peak).toFixed(2);
                    max_kWp = (max * 1000 / peak).toFixed(2);
                }

                return [datetxt + ": " + txt["sum"] + " " + totalValue + "kWh = " + total_kWp + "kWh/kWp",
                    txt["max"] + ":" + max.toFixed(2) + "kWh = " + max_kWp + "kWh/kWp - " + txt["avg"] + " " + avg.toFixed(2) + "kWh " + txt["ref"] + ": " + ref.toFixed(2) + "kWh"];
            }

            const ctx = document.getElementById('month_chart_canvas').getContext("2d");

            Chart.defaults.color = '<?= $colors['color_chart_text_title'] ?>';
            new Chart(ctx, {
                data: {
                    labels: [<?= $labels ?>],
                    datasets: [<?= $allDataSeriesString  ?>],
                    dataZonPHP: <?= json_encode($dataZonPHP)  ?>,
                    myColors: <?= json_encode(colorsPerInverterJS()) ?>,
                    maxIndex: <?= $maxMonthDay ?>,
                    txt: <?= json_encode($_SESSION['txt']); ?>
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: '<?= getTxt("day") ?> (kWh)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value
                                }
                            }
                        },
                        x1: {
                            offset: false,
                            display: false,
                        },
                        y1: {
                            type: 'linear',
                            min: 0,
                            display: true,
                            position: 'right',
                            // grid line settings
                            grid: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                            title: {
                                display: true,
                                text: '<?= getTxt("total") ?> (kWh)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value
                                }
                            },
                            stacked: true,
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        customCanvasBackgroundColor: {
                            color: '<?= $colors['color_chartbackground'] ?>',
                        },
                        legend: {
                            display: <?= $show_legende ?>,
                            position: 'bottom',
                            labels: {
                                filter: item => !item.text.includes('line')
                            },
                            onClick: getCustomLegendClickHandler()
                        },
                        subtitle: {
                            display: true,
                            text: function (ctx) {
                                return buildSubtitle(ctx)
                            },
                            padding: {top: 5, left: 0, right: 0, bottom: 3},
                        },
                        crosshair: {
                            line: {
                                color: '#F66',  // crosshair line color
                                width: 1        // crosshair line width
                            },
                            zoom: {
                                enabled: false, // disable zooming
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
