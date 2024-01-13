<?php
// work internally with UTC, converts values from DB if needed from localDateTime to UTC
global $params, $con, $formatter, $colors, $chart_options, $chart_lang;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

$inverter_name = "";
if (isset($_GET['date'])) {
    $chartdatestring = html_entity_decode($_GET['date']);
    $chartdate = strtotime($chartdatestring);
} else {
    $chartdate = $_SESSION['CHARTDATE'] ?? time();
}
$chartdatestring = date("Y-m-d", $chartdate);

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
// -----------------------------  get data from DB -----------------------------------------------------------------
// query for the day-curve
$valarray = array();
$all_valarray = array();
$inveter_list = array();
$sql = "SELECT SUM( Geg_Dag ) AS gem, naam, Datum_Dag" .
    " FROM " . TABLE_PREFIX . "_dag " .
    " WHERE Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' " .
    " GROUP BY naam, Datum_Dag " .
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
// Highcharts will calculate the max kWh
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
$sqlmdinv = "SELECT Geg_Dag AS gem, Datum_Dag, Naam AS Name FROM " . TABLE_PREFIX . "_dag WHERE Datum_Dag LIKE  '" .
    date("Y-m-d", strtotime($maxdag)) . "%' ORDER BY Name, Datum_Dag ASC";
$resultmd = mysqli_query($con, $sqlmdinv) or die("Query failed. dag-max-dag " . mysqli_error($con));
$maxdagpeak = 0;
if (mysqli_num_rows($resultmd) != 0) {
    $maxdagpeak = 0;
    while ($row = mysqli_fetch_array($resultmd)) {
        $inverter_name = $row['Name'];
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
$strgegmax = "";
$strsomkw = "";
$myColors = colorsPerInverter();
$str_dataserie = "";
$max_first_val = PHP_INT_MAX;
$max_last_val = 0;
$cnt = 0;
$totalDay = 0.0;
foreach ($inveter_list as $inverter_name) {
    $col1 = $myColors[$inverter_name]['min'];
    $col2 = $myColors[$inverter_name]['max'];
    $str_dataserie .= "{ name: '$inverter_name', id: '$inverter_name', type: 'area', marker: { enabled: false },  color: { linearGradient: {x1: 0, x2: 0, y1: 1, y2: 0}, stops: [ [0, $col1], [1, $col2]] },                        
    data:[";
    foreach ($all_valarray as $time => $valarray) {
        if (!isset($valarray[$inverter_name])) $valarray[$inverter_name] = 0;
        $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name] . ', unit: \'W\'},';
        $totalDay += $valarray[$inverter_name];
        // remember first and last date
        if ($max_first_val > $time) {
            $max_first_val = $time;
        }
        if ($max_last_val < $time) {
            $max_last_val = $time;
        }
    }
    $str_dataserie = substr($str_dataserie, 0, -1);
    $str_dataserie .= "]}, 
                    ";
    $cnt++;
}
// day max line per inverter --------------------------------------------------------------
$str_max = "";
$cnt = 0;

foreach (PLANT_NAMES as $key => $inverter_name) {
    if ($key == 0) {
        $dash = '';
    } else {
        $dash = "dashStyle: 'dash',";
    }
    $str_max .= "{ name: '$inverter_name max',  color : '#15ff24', linkedTo: '$inverter_name', lineWidth: 1,  $dash  type: 'line',  stacking: 'normal', marker: { enabled: false },                           
    data:[";

    foreach ($all_valarraymax as $time => $valarraymax) {
        $cnt++;
        if ($cnt == 1) {
            // remember first date
            $max_first_val = $time;
        }
        if (!isset($valarraymax[$inverter_name])) $valarraymax[$inverter_name] = 0;
        $str_max .= '{x:' . ($time * 1000) . ', y:' . $valarraymax[$inverter_name] . ', unit: \'W\'},';
    }
    if (count($all_valarraymax) > 0) {
        $str_max = substr($str_max, 0, -1);
        $str_max .= "]}, 
                    ";
    } else {
        $str_max .= "]},";
    }
    $cnt++;
}
// remember last date
$str_max = substr($str_max, 0, -1);
$strgegmax = substr($strgegmax, 0, -1);

$temp_serie = "";
$temp_unit = "°C";
$val_max = 0;
$val_min = 0;
if ($params['useWeewx']) {
    include ROOT_DIR . "/charts/temp_sensor_inc.php";
}

$show_legende = "true";
if ($isIndexPage) {
    echo '<div class = "index_chart" id="mycontainer"></div>';
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
$maxlink = '<a href= ' . HTML_PATH . 'pages/day_overview.php' . $paramstr_day . 'date=' . $nice_max_date . '><span style="font-family:Arial,Verdana;font-size:12px;font-weight:12px;color:' . $colors['color_chart_text_subtitle'] . ' ;">' . $nice_max_date . '</span></a>';
//print_r($maxlink);
include_once "chart_styles.php";
$show_temp_axis = "false";
$show_cum_axis = "true";
if (strlen($temp_serie) > 0) {
    $show_temp_axis = "true";
    $show_cum_axis = "false";
}

?>
<script>
    $(function () {
        function add(accumulator, a) {
            return accumulator + a;
        }

        var myoptions = <?= $chart_options ?>;
        var khhWp = <?= json_encode($params['PLANTS_KWP']) ?>;
        var nmbr = khhWp.length //misused to get the inverter count
        var maxlink = '<?= $maxlink ?>';
        var temp_max = <?= $val_max ?>;
        var temp_min = <?= $val_min ?>;
        var txt_today = '<?= getTxt("today") ?>';
        var txt_totaal = '<?= getTxt('totaal') ?>';
        var txt_max = '<?= getTxt('max') ?>';
        var txt_peak = '<?= getTxt('peak') ?>';
        Highcharts.setOptions({
            <?= $chart_lang ?>
            time: {
                /**
                 * Use moment-timezone.js to return the timezone offset for individual
                 * timestamps, used in the X axis labels and the tooltip header.
                 */
                getTimezoneOffset: function (timestamp) {
                    const zone = '<?= $params['timeZone'] ?>';
                    const timezoneOffset = -moment.tz(timestamp, zone).utcOffset();
                    return timezoneOffset;
                }
            }
        });
        var mychart = new Highcharts.Chart('mycontainer', Highcharts.merge(myoptions, {
            chart: {
                events: {
                    render() {
                        mychart = this;
                        series = this.series;
                        var sum = [];
                        var kWh = [];
                        var peak = [];
                        var current = 0;
                        var maxkwhtotal = 0;
                        for (i = nmbr - 1; i >= 0; i--) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    maxkwhtotal += (series[i].data[j].y) / 12000; // Total
                                    kWh[i] = khhWp[i]; // KWH
                                }
                            }
                        }
                        for (i = 2 * nmbr - 1; i >= nmbr; i--) {
                            if (series.length >= 2 * nmbr && series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    sum[i] = (series[i].data[j]).y; // sum
                                    current = Highcharts.dateFormat('%H:%M', (series[i].data[series[i].data.length - 1]).x);
                                    peak[i] = series[i].dataMax // PEAK
                                }
                            }
                        }
                        SUM = sum.reduce(add, 0);
                        KWH = kWh.reduce(add, 0);
                        var dataMax = mychart.yAxis[1].dataMax;
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {
                            PEAK = 0;
                        } else {
                            PEAK = AX[0];
                        }
                        this.setSubtitle({
                            text: "<b>" + txt_today + ": </b>" + current + " -  " + Highcharts.numberFormat(SUM, 0, ",", "") +
                                "W" + "=" + (Highcharts.numberFormat(100 * SUM / KWH, 0, ",", "")) + "%" + " - " + txt_peak + ": " + PEAK + "W <br/><b>" +
                                txt_totaal + ":</b> " + (Highcharts.numberFormat(dataMax, 2, ",", "")) + "kWh = " +
                                (Highcharts.numberFormat((dataMax / KWH) * 1000, 2, ",", "")) + "kWh/kWp" + " <b>" +
                                txt_max + ": </b>" + maxlink + " " + (Highcharts.numberFormat(maxkwhtotal, 2, ",", "")) + " kWh"
                        }, false, false);
                        this.setTitle({
                            text: "<b>" +
                                txt_totaal + ":</b> " + (Highcharts.numberFormat(dataMax, 2, ",", "")) + "kWh = " +
                                (Highcharts.numberFormat((dataMax / KWH) * 1000, 2, ",", "")) + "kWh/kWp"
                        }, false, false);

                        // construct chart
                        total = [];
                        value = 0;
                        no_series = 0;
                        indexOfVisibleSeries = [];
                        checkHideForSpline = 1;
                        if (mychart.forRender) {
                            mychart.forRender = false;
                            // function to check amount of visible series and to destroy old spline series
                            mychart.series.forEach(s => {
                                if (s.type === 'spline' && s.visible === true && s.name != 'Temp') {
                                    s.destroy()
                                } else if (s.type === 'spline' && s.visible === false) {
                                    checkHideForSpline = 0
                                }
                                if (s.type === 'area' && s.visible) {
                                    indexOfVisibleSeries.push(s.index);
                                    no_series = nmbr;
                                }
                            });
                            // console.log(no_series);
                            if (checkHideForSpline) {
                                for (i = 0; i < mychart.series[no_series].data.length; i++) {
                                    for (h of indexOfVisibleSeries) {
                                        // throws javascript error when no data available

                                        value += mychart.series[h].data[i].y / 12000;
                                        axis = mychart.series[h].data[i].x;
                                    }
                                    if (typeof axis !== 'undefined') {
                                        total.push([axis, value]);

                                    }


                                }
                                if (series.length >= 2 * nmbr) {
                                    mychart.addSeries({
                                        data: total,
                                        name: 'Cum',
                                        yAxis: 1,
                                        unit: 'kWh',
                                        type: "spline",
                                        color: '<?= $colors['color_chart_cum_line'] ?>',
                                    })
                                }
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
                    // if unit is undefined (added series) set unit to 'kWh' and value to two decimals
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
                        legendItemClick: function () {
                            var clickedSeries = this,
                                lineSeries = clickedSeries.chart.series.filter(series => series.type === 'line'),
                                visibleLineSeries = [];
                            lineSeries.forEach(function (series) {
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
                    wordWrap: 'break-word',
                    color: '<?= $colors['color_chart_text_subtitle'] ?>'
                }
            },
            title: {
                style: {
                    wordWrap: 'break-word',
                    fontWeight: 'normal',
                    fontSize: '12px',
                    color: '<?= $colors['color_chart_text_subtitle'] ?>'
                }
            },
            xAxis: {
                type: 'datetime',
                labels: {
                    style: {
                        color: '<?= $colors['color_chart_labels_xaxis1'] ?>'
                    }
                }
            },
            yAxis: [{ // Watt
                title: {
                    text: 'Power (kW)',
                    style: {
                        color: '<?= $colors['color_chart_title_yaxis1'] ?>'
                    },
                    visible: false
                },
                // min: 0,
                labels: {
                    format: '{value} kW',
                    style: {
                        color: '<?= $colors['color_chart_labels_yaxis1'] ?>'
                    },
                    formatter: function () {
                        return Highcharts.numberFormat(this.value / 1000, 1, ',', '.')
                    }
                },
                gridLineColor: '<?= $colors['color_chart_gridline_yaxis1'] ?>'
            },
                { // cum kWh
                    title: {
                        text: 'Total (kWh)',
                        style: {
                            color: '<?= $colors['color_chart_title_yaxis2'] ?>'
                        }
                    },
                    labels: {
                        format: '{value} kWh',
                        style: {
                            color: '<?= $colors['color_chart_labels_yaxis2'] ?>'
                        },
                        formatter: function () {
                            return Highcharts.numberFormat(this.value, 1, ',', '.')
                        }
                    },
                    gridLineColor: '<?= $colors['color_chart_gridline_yaxis2'] ?>',
                    opposite: true,
                    visible: <?= $show_cum_axis ?>
                },
                { // temperature
                    title: {
                        text: 'Temperature',
                        style: {
                            color: '<?= $colors['color_chart_title_yaxis3'] ?>',
                        },
                    },
                    labels: {
                        format: '{value}<?= $temp_unit ?>',
                        style: {
                            color: '<?= $colors['color_chart_labels_yaxis1'] ?>',
                        },
                        formatter: function () {
                            return this.value + "<?= $temp_unit ?>";
                        },
                    },
                    gridLineColor: '<?= $colors['color_chart_gridline_yaxis3'] ?>',
                    opposite: true,
                    visible: <?= $show_temp_axis ?>,
                    steps: 5,
                    min: temp_min,
                    max: temp_max,
                }
            ],
            series: [
                <?= $str_max . $str_dataserie . $temp_serie?>
            ]
        }), function (mychart) {
            mychart.forRender = true
        });
        setInterval(function () {
            $("#mycontainer").highcharts().reflow();
        }, 500);
    });
</script>
