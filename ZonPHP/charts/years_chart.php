<?php
global $con, $colors, $params, $chart_options;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once "chart_support.php";

$isIndexPage = false;
$showAllInverters = true;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

// -----------------------------  get data from DB -----------------------------------------------------------------
$datum = "";
$inClause = "'" . implode("', '", PLANT_NAMES) . "'";
// load sum per month for all years --------------------------------------------------------------------------------
$sql = "SELECT SUM( Geg_Maand ) AS sum_month, year( Datum_Maand ) AS year, month( Datum_Maand ) AS month, naam, 
            count( Datum_Maand ) AS tdag_maand
        FROM " . TABLE_PREFIX . "_maand     
        WHERE naam in ($inClause) 
        GROUP BY year, month, naam";

$result = mysqli_query($con, $sql) or die("Query failed. totaal " . mysqli_error($con));
$sum_per_year = array();
$total_sum_for_all_years = 0;

if (mysqli_num_rows($result) == 0) {
    $sum_per_year[date('Y-m-d', time())] = 0;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];
        if (!isset($sum_per_year[$row['year']])) {
            $sum_per_year[$row['year']][$inverter_name] = 0;
        }
        if (!isset($sum_per_year[$row['year']][$inverter_name])) {
            $sum_per_year[$row['year']][$inverter_name] = 0;
        }
        $sum_per_year[$row['year']][$inverter_name] += $row['sum_month'];

        $days_per_month = cal_days_in_month(CAL_GREGORIAN, $row['month'], $row['year']);
    }

    foreach ($sum_per_year as $inverter_name => $val) {
        $total_sum_for_all_years += array_sum($val);
    }
}

?>
<?php
// ----------------------------- build data for chart -----------------------------------------------------------------
$myurl = HTML_PATH . "pages/year.php?date=";
$my_year = date("Y", time());

$yearcount = count($sum_per_year);
$allDataSeriesString = "";
$labels = "";
$cumData = "";
$cumSum = 0.0;
$sumAverage = 0.0;
$sumExpected = 0.0;
$myColors = colorsPerInverter();
$maxIndex = 0;
$inverterCounter = 0;
$totalsumCumArray = array();
$dataZonPHP = array();
foreach ($sum_per_year as $year => $yearSum) {
    $labels .= '"' . $year . '",';
    $totalsumCumArray[$year] = 0.0;
}

foreach (PLANT_NAMES as $inverter_name) {
    $inverterCounter++;
    $inverterExpected = $params[$inverter_name]['totalExpectedYield'];
    $dataSeriesString = "";
    $maxIndex = 0;
    $sumExpected += $inverterExpected;
    $cumSum = 0;
    $idx = 0;
    $sumMaxYear = max($sum_per_year);
    foreach ($sum_per_year as $year => $yearSum) {
        if ($yearSum >= $sumMaxYear) {
            $maxIndex = $idx + 1;
        }
        $val = 0.0;
        if (isset($yearSum[$inverter_name])) {
            $val = round($yearSum[$inverter_name], 2);
        }
        $formattedHref = sprintf("%s%02d-%02d-%04d", $myurl, 1, 1, $year);
        $dataSeriesString .= " { x: $year, y: $val, url: \"$formattedHref\"},";
        $cumSum += $val;
        $cumData .= " { x: $year, y: $cumSum},";
        $totalsumCumArray[$year] = $totalsumCumArray[$year] + $cumSum;
        $idx++;
    }
    $inverterAverage = $cumSum / count($sum_per_year);
    $sumAverage += $inverterAverage;

    $dataZonPHP[$inverter_name]['totalValue'] = $cumSum;
    $dataZonPHP[$inverter_name]['peak'] = $params[$inverter_name]["capacity"];
    $dataZonPHP[$inverter_name]['max'] = $sumMaxYear[$inverter_name];
    $dataZonPHP[$inverter_name]['avg'] = $inverterAverage;
    $dataZonPHP[$inverter_name]['ref'] = $sumExpected;

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
                    expectedValue: " . $inverterExpected . ",
                    maxIndex: " . $maxIndex . ",
                    fill: true,
                    backgroundColor: customGradientBackground,
                    yAxisID: 'y',
                    isData: true,
                    legendOrder: " . $inverterCounter . ", 
                },
    ";
    $cumData = "";
}

// average
$allDataSeriesString .= " {
                    datasetId: 'avg', 
                    label: '" . getTxt("average") . "', 
                    type: 'line',      
                    stack: 'Stack 1',                                                                 
                    data: [" . buildConstantDataString($sumAverage, count($sum_per_year)) . "],
                    fill: false,
                    borderColor: '" . $colors['color_chart_average_line'] . "',
                    borderWidth: 1,
                    pointStyle: false,   
                    yAxisID: 'y',
                    fill: false,   
                    showLine: true,
                    isData: false,               
                    legendOrder: 100, 
                },
    ";

// expected
$allDataSeriesString .= " {
                    datasetId: 'expected', 
                    label: '" . getTxt("ref") . "', 
                    type: 'line',      
                    stack: 'Stack 2',                                                                 
                    data: [" . buildConstantDataString($sumExpected, count($sum_per_year)) . "],
                    fill: false,
                    borderColor: '" . $colors['color_chart_reference_line'] . "',
                    borderWidth: 1,
                    pointStyle: false,   
                    yAxisID: 'y',
                    fill: false,   
                    showLine: true,
                    isData: false,   
                    legendOrder: 200,            
                },
    ";

//cumulative
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
                    showLine: false,
                    isData: false,   
                    legendOrder: 300,            
                },
    ";

$show_legende = "true";
if ($isIndexPage) {
    echo ' <div class = "index_chart" id="total_chart">
              <canvas id="total_chart_canvas"></canvas>
           </div>';
    $show_legende = "false";
}

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
                    max_kWp = (max / peak).toFixed(2);
                }

                return [txt["sum"] + " " + (totalValue / 1000).toFixed(0) + "MWh = " + total_kWp + "kWh/kWp",
                    txt["max"] + ":" + (max / 1000).toFixed(2) + "MWh = " + max_kWp + "kWh/kWp - " + txt["avg"] + " " + (avg / 1000).toFixed(2) + "MWh " + txt["ref"] + ": " + (ref / 1000).toFixed(2) + "MWh"];
            }

            const ctx = document.getElementById('total_chart_canvas').getContext("2d");

            Chart.defaults.color = '<?= $colors['color_chart_text_title'] ?>';
            new Chart(ctx, {
                data: {
                    labels: [<?= $labels ?>],
                    datasets: [<?= $allDataSeriesString  ?>],
                    dataZonPHP: <?= json_encode($dataZonPHP)  ?>,
                    myColors: <?= json_encode(colorsPerInverterJS()) ?>,
                    maxIndex: <?= $maxIndex ?>,
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
                                text: '<?= getTxt("year") ?> (MWh)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000).toFixed(0)
                                }
                            },
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
                            stacked: true,
                            title: {
                                display: true,
                                text: '<?= getTxt("total") ?> (MWh)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value / 1000).toFixed(0)
                                }
                            },
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
                                filter: item => !item.text.includes('line'),
                                sort: function (li0, li1, chartData) {
                                    let chart = chartData;
                                    return (chart.datasets[li0.datasetIndex].legendOrder - chart.datasets[li1.datasetIndex].legendOrder)
                                },
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
