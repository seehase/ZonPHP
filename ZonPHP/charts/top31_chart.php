<?php
global $con, $colors, $params, $chart_options;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

$showTopFlop = "top31_chart";
$isIndexPage = false;

if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

$sql2 = "SELECT distinct(YEAR(Datum_Maand))as years FROM " . TABLE_PREFIX . "_maand";
$result = $con->query($sql2);
while($row = $result->fetch_row()) {
  $years[]=$row[0];
}
$whereInYear=implode(',',$years);
$x = "'" . implode("', '", PLANT_NAMES) . "'";
$whereInClause = " where naam in ($x)";
$whereInMonth = '1,2,3,4,5,6,7,8,9,10,11,12';


if (isset($_POST['allselected']) ) {
$whereInMonth = $_POST['allselected'];
}
if (isset($_POST['allselectedtea']) ) {
$whereInYear = $_POST['allselectedtea'];
}
if (isset($_POST['sort']) ) {
$sort = $_POST['sort'];
}
else {$sort = 'desc';}

$sql = "SELECT db1.*
FROM " . TABLE_PREFIX . "_maand AS db1 
JOIN (SELECT Datum_Maand, sum(Geg_Maand) as mysum FROM " . TABLE_PREFIX . "_maand $whereInClause  AND MONTH(Datum_Maand) IN ($whereInMonth) AND YEAR(Datum_Maand) IN ($whereInYear) Group by Datum_Maand ORDER BY mysum $sort LIMIT 0,31) AS db2
ON db1.Datum_Maand = db2.Datum_Maand $whereInClause order by mysum $sort";
//echo $sql;
$result = mysqli_query($con, $sql) or die("Query failed. de_top_31_dagen " . mysqli_error($con));
$datum = "Geen data";
$adatum = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['Naam'];
        $adatum[] = date("Y-m-d", strtotime($row['Datum_Maand']));
        $all_valarray[date("Y-m-d", strtotime($row['Datum_Maand']))] [$inverter_name] = $row['Geg_Maand'];
    }
    $datum = date("M-Y", time());
}
//clean-up category array
$adatum = array_values(array_unique($adatum));
?>
<?php
// -----------------------------  build data for chart -----------------------------------------------------------------
// build colors per inverter array
//
$myurl = HTML_PATH . "pages/day_overview.php?date=";
$myMetadata = array();
$myColors = colorsPerInverter();

$dataseries = "";
$maxval_yaxis = 0;

foreach (PLANT_NAMES as $key => $inverter_name) {
    $data = "";
    $local_max = 0;
    $myColor1 = $myColors[$inverter_name]['min'];
    $myColor2 = $myColors[$inverter_name]['max'];
    $myMetadata[] = "{name: '$inverter_name', color: {linearGradient: { x1: 0, x2: 0, y1: 1, y2: 0 }, stops: [[0, $myColor1], [1, $myColor2]]}, stacking: 'normal', keys: ['name', 'y'], data: data[$key]}";

    for ($i = 0; $i <= 30; $i++) {
        $var = 0.0;
        if (isset($adatum[$i]) && isset($all_valarray[$adatum[$i]][$inverter_name])) {
            $var = round($all_valarray[$adatum[$i]][$inverter_name], 2);
            $data .= '[\'' . $adatum[$i] . '\', ' . $var . '],';
        }
    }
    $maxval_yaxis += $local_max;
    $data = substr($data, 0, -1);
    $dataseries .= '[' . $data . '],';
}

$meta = implode(', ', $myMetadata);
//$strdataseries = "";
$datafin = "";
$dataseries = substr($dataseries, 0, -1);
$datafin = '[' . $dataseries . ']';

$id = $showTopFlop;

$show_legende = "true";
if ($isIndexPage) {
    echo '<div class = "index_chart" id="' . $id . '"></div>';
    $show_legende = "false";

}
include_once "chart_styles.php";
?>
<script>
    $(function () {
        function add(accumulator, a) {
            return accumulator + a;
        }
        const data = <?= $datafin ?>;
        const categories = data[0].map(d => d[0]);
        const myurl = '<?= $myurl ?>';
        series = this.series;
        const khhWp = <?= json_encode($params['PLANTS_KWP']) ?>;
        var nmbr = khhWp.length //misused to get the inverter count
        const kwptot = khhWp.reduce(add, 0);
        var sub_title;
        var myoptions = <?= $chart_options ?>;
        var mychart = new Highcharts.Chart('<?= $id ?>', Highcharts.merge(myoptions, {
            subtitle: {
                text: sub_title,
                style: {
                    color: '<?= $colors['color_chart_text_subtitle'] ?>',
                },
            },
            title: {
    			style: {
                    opacity: 0,
      				fontWeight: 'normal',
                    fontSize: '12px'
   					 }
  					},
            chart: {
                type: 'column', stacking: 'normal'
            },
            plotOptions: {
                series: {
                    states: {
                        hover: {enabled: false, lineWidth: 0,},
                        inactive: {opacity: 1}
                    },
                    cursor: 'pointer',
                    //make bars clickable
                    point: {
                        events: {
                            click: function () {
                                location.href = myurl + this.category;
                            }
                        }
                    },
                    events: {
                        legendItemClick: function () {
                            const chart = this.chart,
                                currentSeries = this,
                                secondSeries = currentSeries === chart.series[0] ? chart.series[1] : chart.series[0],
                                sortFunction = function (a, b) {
                                    return b[1] - a[1]
                                };
                            if (!(
                                (currentSeries.visible && !secondSeries.visible)
                                || (!currentSeries.visible && secondSeries.visible)
                            )) {
                                if (!currentSeries.visible && !secondSeries.visible) {
                                    // sorting by this series
                                    let sortedData = data[currentSeries.index].sort(sortFunction),
                                        seriesCategories = sortedData.map(d => d[0]);
                                    chart.xAxis[0].update({
                                        categories: seriesCategories
                                    });
                                } else {
                                    // sorting by second one series
                                    let sortedData = data[secondSeries.index].sort(sortFunction),
                                        seriesCategories = sortedData.map(d => d[0]);
                                    chart.xAxis[0].update({
                                        categories: seriesCategories
                                    });
                                }
                            } else {
                                // sorting by both series
                                chart.xAxis[0].update({
                                    categories: categories
                                });
                            }
                        }
                    }
                }
            },
            xAxis: {
                type: 'category',
                categories: categories,
                labels: {
                    rotation: 270,
                    style: {color: '<?= $colors['color_chart_labels_xaxis1'] ?>'},
                },
            },
            yAxis: [{ // Primary yAxis
                opposite: true,
                labels: {
                    formatter: function () {
                        return this.value
                    },
                    style: {
                        color: '<?= $colors['color_chart_labels_yaxis1'] ?>',
                    },
                },
                title: {
                    text: 'Total (kWh)',
                    style: {
                        color: '<?= $colors['color_chart_title_yaxis1'] ?>'
                    },
                },
                gridLineColor: '<?= $colors['color_chart_gridline_yaxis1'] ?>',
            }],
            tooltip: {
                formatter: function () {
                    var chart = this.series.chart,
                        x = this.x,
                        stackName = this.series.userOptions.stack,
                        contribuants = '';
                    var index = this.series.data.indexOf(this.point);
                    var id = this.point.x + 1;
                    /* console.log(this); */
                    Totalen = 0
                    chart.series.forEach(function (series, i) {
                        series.points.forEach(function (point) {
                            if (point.category === x && stackName === point.series.userOptions.stack) {
                                contribuants += '<span style="color:' + point.series.color + '">\u25CF</span>' + point.series.name + ': ' + Highcharts.numberFormat(point.y, '2', ',') + ' kWh' + ' = ' + Highcharts.numberFormat(point.y / (0.001 * khhWp[i]), '2', ',') + ' Wh/Wp<br/>',
                                    Totalen += point.y
                            }
                        })
                    })
                    //console.log(this.point);
                    if (stackName === undefined) {
                        stackName = '';
                    }
                    return '<b>' + id + '.</b>&emsp;  &emsp;&emsp;&emsp;&emsp;&emsp; &emsp; ' + x + ' ' + stackName + '<br/>' + contribuants + 'Total: ' + Highcharts.numberFormat(Totalen, '2', ',') + ' kWh' + ' = ' + Highcharts.numberFormat(Totalen / (0.001 * kwptot), '2', ',') + ' Wh/Wp';
                }
            },
            series: [<?= $meta ?>]
        }));
        setInterval(function () {
            $("#<?= $id ?>").highcharts().reflow();
        }, 500);
    });
</script>