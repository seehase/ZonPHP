<?php
global $con, $colors, $params, $chart_options;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

$isIndexPage = false;
$showAllInverters = true;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

// -----------------------------  get data from DB -----------------------------------------------------------------
$datum = "";
$current_year = date('Y', time());

$inveter_list = array();
// load sum per month for all years --------------------------------------------------------------------------------
$sql = "SELECT SUM( Geg_Maand ) AS sum_month, year( Datum_Maand ) AS year, month( Datum_Maand ) AS month, naam, 
            count( Datum_Maand ) AS tdag_maand
        FROM " . TABLE_PREFIX . "_maand     
        GROUP BY year, month, naam";

$result = mysqli_query($con, $sql) or die("Query failed. totaal " . mysqli_error($con));
$sum_per_year = array();
$total_sum_for_all_years = 0;
$average_per_month = 0;
$missing_days_month_year = array();
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
        $missingdays = $days_per_month - $row['tdag_maand'];
        $missing_days_month_year[$row['year']][$row['month']] = $missingdays;
        if (!in_array($inverter_name, $inveter_list)) {
            if (in_array($inverter_name, PLANT_NAMES)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        }
    }

    $total_sum_for_all_years = 0;
    foreach ($sum_per_year as $inverter_name => $val) {
        $total_sum_for_all_years += array_sum($val);
    }
    $average_per_month = $total_sum_for_all_years / count($sum_per_year);
}

//new year average per inverter in clean array
$avg_data = array();
$sqltotal = "SELECT ROUND((SUM( Geg_Maand ) /  COUNT( Geg_Maand ))* 365 , 0 ) AS grand_total_average 
FROM " . TABLE_PREFIX . "_maand
GROUP BY naam";
$result = mysqli_query($con, $sqltotal) or die("Query failed (total average) " . mysqli_error($con));
while ($row = mysqli_fetch_array($result)) {
    $avg_data[] = $row['grand_total_average'];
}


$sqlgem = "SELECT month( Datum_Maand ) AS maand, AVG( Geg_Maand ) AS gem, naam
        FROM " . TABLE_PREFIX . "_maand 
        GROUP BY maand, naam";
$resultgem = mysqli_query($con, $sqlgem) or die("Query failed. totaal-ref " . mysqli_error($con));
while ($row = mysqli_fetch_array($resultgem)) {
    $agemjaar[$row['maand']] = $row['gem'];
}
?>
<?php
// ----------------------------- build data for chart -----------------------------------------------------------------
$href = HTML_PATH . "pages/year_overview.php?date=";
$my_year = date("Y", time());
$strgeg = "";
$strxas = "";
$aclickxas = array();
$first_year = 0;
$yearcount = count($sum_per_year);
$current_bars = "";
$categories = "";
$best_year = 0;
$strdataseries = "";

$myColors = colorsPerInverter();

foreach ($inveter_list as $inverter_name) {

    $current_bars = "";
    $best_year_per_inverter = 0;
    foreach ($sum_per_year as $ijaar => $fkw) {
        $categories .= '"' . $ijaar . '",';
        if ($first_year == 0) {
            $first_year = $ijaar;
        }

        $myColor1 = $myColors[$inverter_name]['min'];
        $myColor2 = $myColors[$inverter_name]['max'];
        if ($fkw >= max($sum_per_year)) {
            $myColor1 = "'" . $colors['color_chartbar_piek1'] . "'";
            $myColor2 = "'" . $colors['color_chartbar_piek2'] . "'";
            if (isset(max($sum_per_year)[$inverter_name])) {
                $best_year_per_inverter = max($sum_per_year)[$inverter_name];
            }
        }

        // normal chart, $val throws errors when missing inverter index
        @$val = round($fkw[$inverter_name], 2);
        $current_bars .= "
                    {  
                      y: $val, 
                      url: \"$href$ijaar-01-01\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 1, y2: 0 },
                        stops: [
                            [0, $myColor1],
                            [1, $myColor2]
                        ]}                                                       
                    },";


        $strxas .= '"' . $ijaar . '",';
        $aclickxas[0][] = $ijaar . "-01-01";
    }
    $best_year += $best_year_per_inverter;
    $current_bars = substr($current_bars, 0, -1);
    $strdataseries .= " {
                    name: '" . $inverter_name . "',
                    type: 'column',
                    color: " . $myColors[$inverter_name]['max'] . ",
                    stacking: 'normal',                    
                    data: [" . $current_bars . "],
                        }, 
                    ";

}
if (strlen($strdataseries) == 0) {
    $strdataseries = "{}";
}
// strip last ","
$strgeg = substr($strgeg, 0, -1);
$strxas = substr($strxas, 0, -1);
$categories = substr($categories, 0, -1);
$myKeys = array_keys($sum_per_year);

$show_legende = "true";
if ($isIndexPage) {
    echo ' <div class = "index_chart" id="total_chart"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>
<script>

    $(function () {

        function add(accumulator, a) {
            return accumulator + a;
        }

        var txt_total = '<?= getTxt("totaal") ?>';
        var khhWp = <?= json_encode($params['PLANTS_KWP']) ?>;
        var nmbr = khhWp.length //misused to get the inverter count
        var txt_max = '<?= getTxt("max") ?>';
        var txt_ref = '<?= getTxt("ref") ?>';
        var avrg = <?= round($average_per_month); ?>;
        var ref = <?= round($params['totalExpectedYield']); ?>;
        var years = <?= $yearcount ?>;
        var myoptions = <?= $chart_options ?>;
        var txt_gem = '<?= getTxt("gem") ?>';
        var avg = <?= json_encode($avg_data, JSON_NUMERIC_CHECK) ?>;
        var ref = <?= json_encode($params['expectedYield'], JSON_NUMERIC_CHECK) ?>;
        var mychart = new Highcharts.Chart('total_chart', Highcharts.merge(myoptions, {

            chart: {
                events: {
                    render() {
                        // make serias public available
                        mychart = this;
                        series = this.series;
                        totayr = 0;
                        kWh = [];
                        gem = [];
                        sum = [];
                        refref = [];
                        peak = [];
                        for (i = nmbr - 1; i >= 0; i--) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    totayr += (series[i].data[j].y);//Total
                                    kWh[i] = khhWp[i]; //KWH
                                    sum = series[i].data.length
                                    refref[i] = ref[i]
                                    gem[i] = avg[i];
                                    peak[i] = series[i].dataMax //PEAK
                                }
                            }
                        }
                        TOT = totayr;
                        KWH = kWh.reduce(add, 0);
                        GEM = gem.reduce(add, 0);
                        GE2 = GEM;
                        REF = refref.reduce(add, 0);
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {
                            PEAK = 0;
                        } else {
                            PEAK = AX[0];
                        }

                        this.setSubtitle({
                            text: "<b>" + txt_total + ": </b>" + (Highcharts.numberFormat(totayr, 0, ",", "")) + " kWh = " + (Highcharts.numberFormat((totayr / KWH) * 1000, 0, ",", "")) + " kWh/kWp " + " <br/><b>"
                                + txt_max + ": </b>" + (Highcharts.numberFormat(PEAK, 0, ",", "")) + " kWh = " +
                                (Highcharts.numberFormat((PEAK / KWH) * 1000, 0, ",", "")) + " kWh/kWp" + " <b>" +
                                txt_gem + ": </b>" + (Highcharts.numberFormat(GEM, 0, ",", "")) + " kWh" + " <b>" + txt_ref + ": </b>" + (Highcharts.numberFormat(REF, 0, ",", "")) + " kWh"
                        }, false, false);
                        mychart.yAxis[0].addPlotLine({
                            id: 'Average',
                            value: GEM,
                            color: '<?= $colors['color_chart_average_line'] ?>',
                            dashStyle: 'shortdash',
                            events: {
                                mouseover: function (e) {
                                    var series = this.axis.series[0],
                                        chart = series.chart,
                                        PointClass = series.pointClass,
                                        tooltip = chart.tooltip,
                                        point = (new PointClass()).init(
                                            series, ['Average', this.options.value]
                                        ),
                                        normalizedEvent = chart.pointer.normalize(e);
                                    point.tooltipPos = [
                                        normalizedEvent.chartX - chart.plotLeft,
                                        normalizedEvent.chartY - chart.plotTop
                                    ];
                                    tooltip.refresh(point);
                                },
                                mouseout: function (e) {
                                    this.axis.chart.tooltip.hide();
                                }
                            },
                            width: 2,
                        });
                        //average plotline
                        mychart.yAxis[0].addPlotLine({
                            id: 'Reference',
                            value: REF,
                            color: '<?= $colors['color_chart_reference_line'] ?>',
                            dashStyle: 'shortdash',
                            events: {
                                mouseover: function (e) {
                                    var series = this.axis.series[0],
                                        chart = series.chart,
                                        PointClass = series.pointClass,
                                        tooltip = chart.tooltip,
                                        point = (new PointClass()).init(
                                            series, ['Reference', this.options.value]
                                        ),
                                        normalizedEvent = chart.pointer.normalize(e);
                                    point.tooltipPos = [
                                        normalizedEvent.chartX - chart.plotLeft,
                                        normalizedEvent.chartY - chart.plotTop
                                    ];
                                    tooltip.refresh(point);
                                },
                                mouseout: function (e) {
                                    this.axis.chart.tooltip.hide();
                                }
                            },
                            width: 2,
                        });

                    }
                }
            },
            title: {
                style: {
                    opacity: 0,
                    fontWeight: 'normal',
                    fontSize: '12px'
                }
            },
            subtitle: {
                //text: sub_title,
                style: {
                    color: '<?= $colors['color_chart_text_subtitle'] ?>',
                },
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
                column: {
                    events: {
                        legendItemClick: function () {

                            var clickedSeries = this;
                            mychart.yAxis[0].removePlotLine('Average');
                            mychart.yAxis[0].removePlotLine('Reference');

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
                        //stacking: 'normal'
                    }
                }
            },

            xAxis: [{
                labels: {
                    rotation: 310,
                    step: 1,
                    style: {
                        color: '<?= $colors['color_chart_labels_xaxis1'] ?>',
                    },
                    formatter: function () {
                        return this.value;
                    },
                },
                min: 0,
                categories: [<?= $categories ?>],
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function () {
                        return this.value / 1000
                    },
                    style: {
                        color: '<?= $colors['color_chart_labels_yaxis1'] ?>',
                    },
                },
                opposite: true,
                title: {
                    text: 'Total (MWh)',
                    style: {
                        color: '<?= $colors['color_chart_title_yaxis1'] ?>'
                    },
                },
                steps: 100,
                gridLineColor: '<?= $colors['color_chart_gridline_yaxis1'] ?>',
            }],
            tooltip: {
                formatter: function () {
                    if (this.series.name == 'kWh/Year') {
                        return this.x + ': ' + this.y.toFixed(0) + 'kWh';
                    }
                    if ((typeof (this.x) == 'undefined') && this.y == GEM) {
                        return mychart.yAxis[0].plotLinesAndBands[0].id + ' ' + this.y.toFixed(0) + ' kWh';
                    }
                    if ((typeof (this.x) == 'undefined') && this.y == REF) {
                        return mychart.yAxis[0].plotLinesAndBands[1].id + ' ' + this.y.toFixed(0) + ' kWh';
                    } else {
                        {

                            var chart = this.series.chart,
                                x = this.x,
                                stackName = this.series.userOptions.stack,
                                contribuants = '';

                            chart.series.forEach(function (series) {
                                series.points.forEach(function (point) {
                                    if (point.category === x && stackName === point.series.userOptions.stack) {
                                        contribuants += '<span style="color:' + point.series.color + '">\u25CF</span>' + point.series.name + ': ' + point.y.toFixed(0) + ' kWh<br/>'
                                    }
                                })
                            })
                            if (stackName === undefined) {
                                stackName = '';
                            }
                            return '<b> ' + x + ' ' + stackName + '<br/>' + '<br/>' + contribuants + 'Total: ' + this.point.stackTotal.toFixed(0) + ' kWh';

                        }
                    }
                }
            },


            series: [
                <?= $strdataseries ?>
            ]
        }));
        setInterval(function () {
            $("#total_chart").highcharts().reflow();
        }, 500);
    });
</script>
