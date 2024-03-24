<?php
global $con, $params, $colors, $shortmonthcategories, $chart_options, $shortMonthLabels;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once "chart_support.php";

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

$chartdate = time();
$chartdatestring = date("Y-m-d", $chartdate);

if (isset($_GET['jaar'])) {
    $chartdatestring = html_entity_decode($_GET['jaar']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = date("Y-m-d", $chartdate);
}
$cur_year_month = date('Y-m', $chartdate);
$paramnw['jaar'] = date("Y", $chartdate);

$sql = "SELECT MAX( Datum_Maand ) AS maxi, YEAR(Datum_Maand) as year, ROUND(SUM( Geg_Maand ),0) AS som, naam
FROM " . TABLE_PREFIX . "_maand 
GROUP BY  naam, DATE_FORMAT( Datum_Maand,  '%y-%m' ), year
ORDER BY maxi, naam ASC";

$aTotaaljaar = array();
$result = mysqli_query($con, $sql) or die("Query failed. alle_jaren: " . mysqli_error($con));
$adatum[][] = 0;
$aTotaaljaar[] = 0;
$acdatum = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $adatum[date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = $row['som'];
        $acdatum[] = $row['year'];
        if (!isset($abdatum[$row['naam']][date("Y", strtotime($row['maxi']))])) $abdatum[$row['naam']][date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = 0;

        $abdatum[$row['naam']][date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = $row['som'];
        //$aTotaaljaar[date("Y", strtotime($row['maxi']))] += $row['som'];
        if (!isset($abTotaaljaar[$row['naam']][date("Y", strtotime($row['maxi']))])) $abTotaaljaar[$row['naam']][date("Y", strtotime($row['maxi']))] = 0;
        $abTotaaljaar[$row['naam']][date("Y", strtotime($row['maxi']))] += $row['som'];
    }
}
$acdatum = array_values(array_unique($acdatum));
if (count($acdatum) > 0) {
    $firstYear = reset($acdatum);
} else {
    $firstYear = 1970;
}

//	new reference
$totalExpectedMonth = $params['totalExpectedMonth'];

//max for all inverters
$sqlmax = "SELECT maand,jaar,som, Name FROM 
(SELECT naam as Name, month(Datum_Maand) AS maand,year(Datum_Maand) AS jaar, sum(Geg_Maand) AS som FROM " .
    TABLE_PREFIX . "_maand GROUP BY naam, maand,jaar ) AS somquery JOIN (SELECT maand as tmaand, max( som ) AS maxgeg " .
    " FROM ( SELECT naam, maand, jaar, som FROM ( SELECT naam, month( Datum_Maand ) AS maand, year( Datum_Maand ) AS jaar, sum( Geg_Maand ) AS som FROM "
    . TABLE_PREFIX . "_maand GROUP BY naam, maand, jaar ) AS somqjoin ) AS maxqjoin GROUP BY naam,tmaand )AS maandelijks " .
    "ON (somquery.maand= maandelijks.tmaand AND maandelijks.maxgeg = somquery.som) ORDER BY Name, maand";
$resultmax = mysqli_query($con, $sqlmax) or die("Query failed. ERROR: " . mysqli_error($con));

for ($i = 1; $i <= 12; $i++) {
    $maxmaand[$i] = 0;
}

if (mysqli_num_rows($resultmax) == 0) {
    $maxmaand[] = 0;
    $maxPerMonth[][] = 0;
} else {
    while ($row = mysqli_fetch_array($resultmax)) {
        $maxmaand[$row['maand']] = $row['som'];
        $maxPerMonth[$row['maand']][$row['Name']] = Round($row['som']);
    }
}
?>

<?php
// -----------------------------  build data for chart -----------------------------------------------------------------
$href = HTML_PATH . "pages/month.php?date=";

$totalsumMaxArray = array();
$totalsAvgArray = array();

for ($i = 1; $i <= 12; $i++) {
    $totalsumMaxArray[$i] = 0.0;
    $totalsAvgArray[$i] = 0.0;
}

$my_year = date("Y", $chartdate);
$allDataSeriesString = "";
$labels = $shortMonthLabels;

$maxData = "";
$avglines = "";
$inverterAverage = 0;
$colorzz = 0;

$plantNames = "";
$lastInverter = "";
$visibleInvertersJS = "";
$sumPerInverterMonth = array();
$totalAveragePerInverterMonth = array();
foreach (PLANT_NAMES as $idxPlants => $inverter_name) {
    $lastInverter = $inverter_name;
    $plantNames .= "'$inverter_name',";
    $visibleInvertersJS .= $inverter_name . ",";
    $strdata = "";
    $colorcnt = 0;
    $bdatum = array();
    $inverterAvgArray = array();
    $inverterMaxArray = array();
    $cnt_per_month = array();
    if (isset($abdatum[$inverter_name])) {
        $bdatum = $abdatum[$inverter_name];
    }

    // collect data for max bars
    for ($i = 1; $i <= 12; $i++) {
        $cnt_per_month[$i] = 0;
        if (!isset($maxPerMonth[$i][$inverter_name])) {
            $maxPerMonth[$i][$inverter_name] = 0;
        }
        $maxVal = round($maxPerMonth[$i][$inverter_name], 2);
        $inverterMaxArray[$i] = $maxVal;
        $totalsumMaxArray[$i] += $maxVal;
    }

    $firstYear = date("Y", strtotime(STARTDATE));
    foreach ($bdatum as $year => $asy) {

        if ($year <= $paramnw['jaar'] && $year >= ($firstYear)) {
            $current_bars = "";
            $my_year = 0;
            for ($i = 1; $i <= 12; $i++) {
                if (array_key_exists($i, $asy)) {
                    $curYearVal = $asy[$i];
                    $cur_max = $maxmaand[$i];
                } elseif (array_key_exists($i, $maxmaand)) {
                    $curYearVal = 0;
                    $cur_max = $maxmaand[$i];
                } else {
                    $curYearVal = 0;
                    $cur_max = 0;
                }

                $formattedHref = sprintf("%s%04d-%02d-%02d", $href, $my_year, $i, 1);
                if ($curYearVal > 0.0) {
                    $cnt_per_month[$i]++;
                }

                if (!isset($sumPerInverterMonth[$inverter_name][$i])) {
                    $sumPerInverterMonth[$inverter_name][$i] = 0;
                }
                $sumPerInverterMonth[$inverter_name][$i] += $curYearVal;

                $formattedHref = sprintf("%s%04d-%02d-%02d", $href, $year, $i, 1);
                $strdata .= " { x: $i, y: $curYearVal  },";
            }

            $allDataSeriesString .= " {
                    datasetId: '" . $inverter_name . $year . "', 
                    inverter: '" . $inverter_name . "',
                    label: '" . $inverter_name . " - " . $year . "', 
                    type: 'bar',                               
                    stack: 'Stack-" . $year . "',                    
                    data: [" . $strdata . "],                    
                    dataCUM: [],
                    dataMAX: [], 
                    dataREF: [],
                    averageValue: 0,
                    expectedValue: 0,
                    maxIndex: 0,
                    fill: true,
                    backgroundColor:  '" . $colors['color_palettes'][$colorcnt][$colorzz] . "', 
                    yAxisID: 'y',
                    xAxisID: 'x',
                    isData: true,
                    orderId: 1,
                },
            ";
            $strdata = "";
            if ($colorcnt == 4) {
                $colorcnt = 0;
            } else $colorcnt++;
        }
    }
    $averagePerInverterMonth = array();
    for ($i = 1; $i <= 12; $i++) {
        if (!isset($totalAveragePerInverterMonth[$i])) {
            $totalAveragePerInverterMonth[$i] = 0;
        }
        $averagePerInverterMonth[$inverter_name][$i] = $sumPerInverterMonth[$inverter_name][$i] / $cnt_per_month[$i];
        $totalAveragePerInverterMonth[$i] += $sumPerInverterMonth[$inverter_name][$i] / $cnt_per_month[$i];
    }

    $allDataSeriesString .= "{
                    order: 0,    
                    datasetId: '" . $inverter_name . "', 
                    inverter: '" . $inverter_name . "',
                    label: '" . $inverter_name . "', 
                    type: 'line',         
                    radius:50,
                    hoverRadius: 25,
                    pointStyle: 'line',
                    borderWidth: 4,
                    stepped: true,
                    showLine: false,                                                             
                    data: [],
                    dataAVG: [" . convertKeyValueArrayToDataString($averagePerInverterMonth[$inverter_name]) . "],
                    dataMAX: [" . convertKeyValueArrayToDataString($inverterMaxArray) . "],
                    fill: false,                    
                    borderColor: '" . $colors['color_chart_reference_line'] . "',                
                    borderWidth: 1,                    
                    yAxisID: 'y',      
                    xAxisID: 'x-axis-ref',                                     
                    isData: false,               
                },
    ";
    $inverterAvgArray = array();
    $inverterMaxArray = array();

    if ($colorzz == 3) {
        $colorzz = 0;
    } else $colorzz++;
}

// max bars
$allDataSeriesString .= " {
                    order: 5,  
                    datasetId: 'max', 
                    label: 'max', 
                    type: 'bar',                                                                                           
                    data: [" . convertValueArrayToDataString($totalsumMaxArray) . "],
                    fill: false,
                    borderColor: '" . $colors['color_chart_max_bar'] . "',
                    backgroundColor: '" . $colors['color_chart_max_bar'] . "',                                       
                    pointStyle: false,   
                    xAxisID: 'maxbar',
                    yAxisID: 'y',
                    fill: false,   
                    showLine: true,
                    isData: false,               
                },
    ";
// Average
$allDataSeriesString .= "{
                    order: 0,    
                    datasetId: 'avg', 
                    label: '" . getTxt("average") . "',                      
                    type: 'line',         
                    radius:function(context) { 
                       let width = 12;                                
                       try {
                            let chart = context.chart;                            
                            let idx = findDatasetById(chart.data.datasets, 'max');                                  
                            let meta = chart.getDatasetMeta(idx);                            
                            width = meta.data[0].width -30;
                       } catch (e) {
                       } finally {                            
                          if (width == null) width = 30;         
                       }                                                    
                       if (width < 0) width = 12;                       
                       return width;
                    },
                    hoverRadius: 25,
                    pointStyle: 'line',
                    borderWidth: 4,
                    stepped: true,
                    showLine: false,                                                             
                    data: [" . convertValueArrayToDataString($totalAveragePerInverterMonth) . "],
                    dataAVG: [" . convertValueArrayToDataString($totalsAvgArray) . "],
                    fill: false,                    
                    borderColor: '" . $colors['color_chart_average_line'] . "',                
                    borderWidth: 1,                    
                    yAxisID: 'y',      
                    xAxisID: 'x-axis-ref',                                     
                    isData: false,               
                },
    ";

$subtitle = strip($visibleInvertersJS);
$show_legende = "true";
if ($isIndexPage) {
    echo '<div class = "index_chart" id="years_chart_1">
              <canvas id="last_year_chart_canvas"></canvas>
          </div>';
    $show_legende = "false";
}


?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4/dist/chart.umd.min.js"></script>
<script src="<?= HTML_PATH ?>inc/js/chart_support.js"></script>
<script>

    $(function () {

            const lastYearLegendClickHandler = function (e, legendItem, legend) {
                let chart = legend.chart;
                Chart.defaults.plugins.legend.onClick(e, legendItem, legend);
                let data = chart.data;
                let avgSum = [];
                let maxSum = [];

                for (let i in data.datasets) {
                    let meta = chart.getDatasetMeta(i);
                    let dataset = chart.data.datasets[i];
                    let isHidden = meta.hidden === null ? false : meta.hidden;
                    if (dataset.isData && dataset.inverter === legendItem.text) {
                        if (isHidden) {
                            chart.show(i);
                            legendItem.hidden = false;
                        } else {
                            chart.hide(i);
                            legendItem.hidden = true;
                        }
                    }
                    if (dataset.inverter === dataset.datasetId) {
                        if (!isHidden) {
                            // avg
                            for (let ii in dataset.dataAVG) {
                                if (avgSum[ii] == null) avgSum[ii] = 0.0;
                                avgSum[ii] = avgSum[ii] + dataset.dataAVG[ii].y;
                            }
                            // max
                            for (let ii in dataset.dataMAX) {
                                if (maxSum[ii] == null) maxSum[ii] = 0.0;
                                maxSum[ii] = maxSum[ii] + dataset.dataMAX[ii].y;
                            }
                        }
                    }
                }
                let avgIDX = findDatasetById(data.datasets, "avg");
                if (avgIDX > 0) {
                    data.datasets[avgIDX].data = avgSum;
                }
                let maxIDX = findDatasetById(data.datasets, "max");
                if (maxIDX > 0) {
                    data.datasets[maxIDX].data = maxSum;
                }
                chart.update();
            };

            const ctx = document.getElementById('last_year_chart_canvas').getContext("2d");

            Chart.defaults.color = '<?= $colors['color_chart_text_title'] ?>';
            new Chart(ctx, {
                data: {
                    labels: [<?= $labels ?>],
                    inverters: [<?= $plantNames ?>],
                    datasets: [<?= $allDataSeriesString  ?>],
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
                                text: '<?= getTxt("total") ?> (kWh)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value.toFixed(0))
                                }
                            },
                        },
                        'maxbar': {
                            offset: true,
                            display: false,
                        },
                        'x-axis-ref': {
                            offset: true,
                            display: false,
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
                                filter: function(legendItem, data) {
                                    let idx = legendItem.datasetIndex;
                                    let isData = data.datasets[idx].isData;
                                    return !isData
                                }
                            },
                            onClick: lastYearLegendClickHandler,

                        },
                        subtitle: {
                            display: true,
                            text: '<?= $subtitle ?>',
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
