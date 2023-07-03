<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/load_cache.php";

$chartcurrentdate = time();
$chartdate = $chartcurrentdate;
$chartdatestring = date("Y-m-d", $chartdate);
if (isset($_GET['date'])) {
    $chartdatestring = html_entity_decode($_GET['date']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = date("Y-m-d", $chartdate);
}
if (isset($_GET['Schaal'])) {
    $aanpas = 1;
} else {
    $aanpas = 0;
}
$isIndexPage = false;
$showAllInverters = true;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

$valarray = array();
$all_valarray = array();
$inveter_list = array();
$sql = "SELECT SUM( Geg_Dag ) AS gem, naam, STR_TO_DATE( CONCAT( DATE( Datum_Dag ) , ' ',HOUR( Datum_Dag ) , ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" .
    $params['displayInterval'] . " ) *" . $params['displayInterval'] . ", 2, '0' ) , ':00' ) , '%Y-%m-%d %H:%i:%s' ) AS datumtijd " .
    " FROM " . TABLE_PREFIX . "_dag " .
    " WHERE Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' " .
    " GROUP BY datumtijd, naam " .
    " ORDER BY datumtijd ASC";
$result = mysqli_query($con, $sql) or die("Query failed. dag " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $formatter->setPattern('d LLLL yyyy');
    $datum = getTxt("nodata") . datefmt_format($formatter, $chartdate);
    $tlaatstetijd = time();
    $geengevdag = 0;
    $agegevens[] = 0;
    $aoplopendkwdag[] = 0;
} else {
    $formatter->setPattern('d LLL yyyy');
    $datum = datefmt_format($formatter, $chartdate);
    $geengevdag = 1;
    $fsomoplopend = 0;
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];
        $tlaatstetijd = strtotime($row['datumtijd']);
        $agegevens[date("H:i", strtotime($row['datumtijd']))] = $row['gem'];
        $valarray[strtotime($row['datumtijd'])] = $row['gem'];
        $all_valarray[strtotime($row['datumtijd'])] [$inverter_name] = $row['gem'];
        $fsomoplopend += $row['gem'] * 1000 / (1000 * 60 / $params['displayInterval']);
        $aoplopendkwdag[strtotime($row['datumtijd'])] = $fsomoplopend;
        if (!in_array($inverter_name, $inveter_list)) {
            if (in_array($inverter_name, PLANTS)) {
                // add to list only if it configured (ignore db entries) and it has values for the current day
                $inveter_list[] = $inverter_name;
            }
        };
    }
}
//--------------------------------------------------------------------------------------------------
// get best day for current month (max value over all years for current month) per each inverter
$maxdays = array();
foreach ($inveter_list as $key => $inverter) {
    $sqlmaxdag = "SELECT Datum_Maand, Geg_Maand
	 FROM " . TABLE_PREFIX . "_maand
	 JOIN (SELECT month(Datum_Maand) AS maand, max(Geg_Maand) AS maxgeg FROM " . TABLE_PREFIX . "_maand 
	 WHERE DATE_FORMAT(Datum_Maand,'%m')='" . date('m', $chartdate) . "' " . "
	   AND naam = '" . $inverter . "'  
     GROUP BY maand )AS maandelijks ON (month(" .
        TABLE_PREFIX . "_maand.Datum_Maand) = maandelijks.maand AND maandelijks.maxgeg = " . TABLE_PREFIX . "_maand.Geg_Maand) ORDER BY maandelijks.maand";
    $resultmaxdag = mysqli_query($con, $sqlmaxdag) or die("Query failed. dag-max " . mysqli_error($con));
    if (mysqli_num_rows($resultmaxdag) == 0) {
        $maxdays[$inverter] = date("y-m-d", time());
    } else {
        while ($row = mysqli_fetch_array($resultmaxdag)) {
            $maxdays[$inverter] = $row['Datum_Maand'];
        }
    }
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

$maxlinks = '[';
$maxkwh = array();
foreach ($maxdays as $inverter => $maxday) {
    // Query maxkwh to get array with max value for all inverters
    $sqlmaxkwh = "SELECT Geg_Maand, Naam
         FROM " . TABLE_PREFIX . "_maand
         WHERE Datum_Maand LIKE  '" . date("Y-m-d", strtotime($maxday)) . "%'
         ORDER BY Naam ASC ";
    $resultmaxkwh = mysqli_query($con, $sqlmaxkwh) or die("Query failed. kwh-max " . mysqli_error($con));
    if (mysqli_num_rows($resultmaxkwh) == 0) {
        $maxval = 0;
    } else {
        while ($row = mysqli_fetch_array($resultmaxkwh)) {
            $maxval = round($row['Geg_Maand'], 2);

        }
    }
    $maxkwh[] = $maxval;

    $nice_max_date = date("Y-m-d", strtotime($maxday));
    $maxlinks .= '\'<a href="day_overview.php?naam=' . $inverter . '&dag=' . $nice_max_date . '">' . getTxt("max") . " " . $inverter . ": " . $nice_max_date . " - " . $maxval . 'kWh</a>\',';
}
$maxlinks .= ']';

// select data from the best day for current month per inverter
$all_valarraymax = array();
foreach ($maxdays as $inverter => $maxday) {
    $sqlmdinv = "SELECT Geg_Dag AS gem, STR_TO_DATE( CONCAT( DATE( Datum_Dag ) ,  ' ', HOUR( Datum_Dag ) ,  ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" . $params['displayInterval'] . " ) *" . $params['displayInterval'] . ", 2,  '0' ) ,  ':00' ) ,  '%Y-%m-%d %H:%i:%s' ) AS datumtijd, Naam AS Name
                   FROM " . TABLE_PREFIX . "_dag
                  WHERE Datum_Dag LIKE  '" . date("Y-m-d", strtotime($maxday)) . "%'
                    AND naam = '" . $inverter . "'
                  ORDER BY Name, datumtijd ASC";
    $resultmd = mysqli_query($con, $sqlmdinv) or die("Query failed. dag-max-dag " . mysqli_error($con));
    if (mysqli_num_rows($resultmd) == 0) {
        $maxdagpeak = 0;
    } else {
        $maxdagpeak = 0;
        while ($row = mysqli_fetch_array($resultmd)) {
            $inverter_name = $row['Name'];

            // take original date and convert it to the current chartdate
            $orginal_date = strtotime($row['datumtijd']);
            $hour = intval(date('G', $orginal_date));
            $minutes = intval(date('i', $orginal_date));
            $newDate = mktime($hour, $minutes, 0, intval(date('m', $chartdate)), intval(date('j', $chartdate)), intval(date('Y', $chartdate)));

            $valarraymax[$newDate] = $row['gem'];
            $all_valarraymax[$newDate] [$inverter_name] = $row['gem'];

            if ($row['gem'] > $maxdagpeak) {
                $maxdagpeak = $row['gem'];
            };
        }
    }
}
//--------------------------------------------------------------------------------------------------
$strgegmax = "";
$strsomkw = "";
// build colors per inverter array
$myColors = array();
for ($k = 0; $k < count(PLANTS); $k++) {
    $col1 = "color_inverter" . $k . "_chartbar_min";
    $col1 = "'" . $colors[$col1] . "'";
    $myColors[PLANTS[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k . "_chartbar_max";
    $col1 = "'" . $colors[$col1] . "'";
    $myColors[PLANTS[$k]]['max'] = $col1;
}
$str_dataserie = "";
$max_first_val = PHP_INT_MAX;
$max_last_val = 0;
foreach ($inveter_list as $inverter_name) {
    $col1 = $myColors[$inverter_name]['min'];
    $col2 = $myColors[$inverter_name]['max'];
    $series_isVisible = "false";
    if ($showAllInverters) {
        $series_isVisible = "true";
    };
    $str_dataserie .= "{ name: '$inverter_name', id: '$inverter_name', type: 'area', marker: { enabled: false }, visible: $series_isVisible, color: { linearGradient: {x1: 0, x2: 0, y1: 1, y2: 0}, stops: [ [0, $col1], [1, $col2]] },                        
    data:[";
    foreach ($all_valarray as $time => $valarray) {

        if (!isset($valarray[$inverter_name])) $valarray[$inverter_name] = 0;
        $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name] . ', unit: \'W\'}, ';


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

}
// day max line per inverter --------------------------------------------------------------
$str_max = "";
foreach (PLANTS as $key => $inverter_name) {
    if ($key == 0) {
        $dash = '';
    } elseif ($key > 0) {
        $dash = "dashStyle: 'dash',";
    }
    $str_max .= "{ name: '$inverter_name max',  color : '#15ff24', linkedTo: '$inverter_name', lineWidth: 1,  $dash  type: 'line',  stacking: 'normal', marker: { enabled: false },                           
    data:[";

    foreach ($all_valarraymax as $time => $valarraymax) {
        $newDate = date($time);
        if (!isset($valarraymax[$inverter_name])) {
            $valarraymax[$inverter_name] = 0;
        }

        $str_max .= '{x:' . ($newDate * 1000) . ', y:' . $valarraymax[$inverter_name] . ', unit: \'W\'}, ';

        // remember first and last date get max/min of all
        if ($max_first_val > $time) {
            $max_first_val = $time;
        }
        if ($max_last_val < $time) {
            $max_last_val = $time;
        }
    }
    if (count($all_valarraymax) > 0) {
        $str_max = substr($str_max, 0, -1);
        $str_max .= "]}, 
                    ";
    } else {
        $str_max .= "]},";
    }

}
$str_max = substr($str_max, 0, -1);
$strgegmax = substr($strgegmax, 0, -1);
$temp_serie = "";
$temp_unit = "°C";
$val_max = 0;
$val_min = 0;
if ($params['useWeewx'] == true) {
    include ROOT_DIR . "/charts/temp_sensor_inc.php";
}
// cumulative line --------------------------------------------------------------
$str_cum = "";
$cnt = 0;
$cum_max_value = 0;
foreach ($aoplopendkwdag as $tuur => $fkw) {
    $cnt++;
    $fkw = $fkw / 1000;
    $strtemp = "{x:" . ($tuur * 1000) . ", y:" . number_format($fkw, 1, '.', '') . ", unit: 'kWh' }";
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
    echo '<div class = "index_chart" id="mycontainer"></div>';
    $show_legende = "false";
}


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
        var khhWp = [<?= $params['plantskWp'] ?>];
        var nmbr = khhWp.length //misused to get the inverter count

        /// to be removed2345
        var maxmax = <?= json_encode($maxkwh) ?>;
        var maxlinks = <?= $maxlinks ?>;

        /// to be removed

        var temp_max = <?= $val_max ?>;
        var temp_min = <?= $val_min ?>;
        var txt_actueel = '<?= getTxt("actueel") ?>';
        var txt_totaal = '<?= getTxt("totaal") ?>';
        var txt_max = '<?= getTxt("max") ?>';
        var txt_peak = '<?= getTxt("peak") ?>';

        Highcharts.setOptions({<?= $chart_lang ?>});
        var mychart = new Highcharts.Chart('mycontainer', Highcharts.merge(myoptions, {
            chart: {
                events: {
                    render() {
                        mychart = this;
                        series = this.series;
                        // construct subtitle
                        sum = [];
                        kWh = [];
                        peak = [];
                        max = [];
                        current = 0;
                        tota = 0;
                        links = "";
                        for (i = nmbr - 1; i >= 0; i--) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    tota += (series[i].data[j].y) / 12000;//Total
                                    sum[i] = (series[i].data[series[i].data.length - 1]).y; //sum
                                    current = Highcharts.dateFormat('%H:%M', (series[i].data[series[i].data.length - 1]).x);//TIME
                                }
                                kWh[i] = khhWp[i]; //KWH
                                max[i] = maxmax[i]; //MAXday
                                peak[i] = series[i].dataMax //PEAK
                                links = links + maxlinks[i] + "        ";
                            }
                        }

                        SUM = sum.reduce(add, 0);
                        KWH = kWh.reduce(add, 0);
                        MAX = max.reduce(add, 0);
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {
                            PEAK = 0;
                        } else {
                            PEAK = AX[0];
                        }

                        this.setSubtitle({
                            text: "<b>" + txt_actueel + ": </b>" + current + " -  " + Highcharts.numberFormat(SUM, 0, ",", "") +
                                "W" + "=" + (Highcharts.numberFormat(100 * SUM / KWH, 0, ",", "")) + "%" + " - " + txt_peak + ": " + PEAK + "W <br/><b>" +
                                txt_totaal + ":</b> " + (Highcharts.numberFormat(tota, 2, ",", "")) + "kWh = " +
                                (Highcharts.numberFormat((tota / KWH) * 1000, 2, ",", "")) + "kWh/kWp" + " <b>" +
                                txt_max + ": </b>" + (Highcharts.numberFormat(MAX, 2, ",", "")) + " kWh <br/>" + links
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
                                if (s.type === 'area' && s.visible) {
                                    indexOfVisibleSeries.push(s.index);
                                }
                            });
                            if (checkHideForSpline) {
                                for (i = 0; i < mychart.series[0].data.length; i++) {
                                    for (h of indexOfVisibleSeries) {
                                        value += mychart.series[h].data[i].y / 12000;
                                        axis = mychart.series[h].data[i].x;
                                    }
                                    if (typeof axis !== 'undefined') {
                                        total.push([axis, value]);
                                    }
                                }
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
            xAxis: {
                type: 'datetime',
                labels: {
                    style: {
                        color: '<?= $colors['color_chart_labels_xaxis1'] ?>',
                        fontSize: '0.7em'
                    }
                }
            },
            yAxis: [{ // Watt
                title: {
                    text: 'Power',
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
                        return Highcharts.numberFormat(this.value / 1000, 1, ',', '.') + " kW";
                    }
                },
                gridLineColor: '<?= $colors['color_chart_gridline_yaxis1'] ?>'
            },
                { // cum kWh
                    title: {
                        text: 'Total',
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
                            return Highcharts.numberFormat(this.value, 1, ',', '.') + " kWh";
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
                <?= $str_dataserie ?>
                <?= $str_max ?>
                <?= $temp_serie ?>
            ]
        }), function (mychart) {
            mychart.forRender = true
        });
        setInterval(function () {
            $("#mycontainer").highcharts().reflow();
        }, 500);
    });
</script>
