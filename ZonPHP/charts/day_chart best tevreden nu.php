<?php
//ZonPHP8 Nieuw
//converts from UTC to local time
// Globally define the Timezone
define( 'TIMEZONE', 'UTC' );
date_default_timezone_set( TIMEZONE );
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}
$chartcurrentdate = time();
$chartdate = $chartcurrentdate;
$inverter_name = "";
$chartdatestring = date("Y-m-d", $chartdate);
if (isset($_GET['dag'])) {
    $chartdatestring = html_entity_decode($_GET['dag']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring =  date("Y-m-d", $chartdate);
}
if (isset($_GET['Schaal'])) {
    $aanpas = 1;
} else {
    $aanpas = 0;
}
$isIndexPage = false;

if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

//query for the day-curve
$valarray = array();
$all_valarray = array();
$inveter_list = array();
$sql = "SELECT SUM( Geg_Dag ) AS gem, naam, Datum_Dag, STR_TO_DATE( CONCAT( DATE( Datum_Dag ) , ' ',HOUR( Datum_Dag ) , ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" .
    $param['isorteren'] . " ) *" . $param['isorteren'] . ", 2, '0' ) , ':00' ) , '%Y-%m-%d %H:%i:%s' ) AS datumtijd " .
    " FROM " . $table_prefix . "_dag " .
    " WHERE Datum_Dag LIKE '" . date("Y-m-d", $chartdate) . "%' " .
    " GROUP BY datumtijd, naam " .
    " ORDER BY datumtijd ASC";
//echo $sql,'<BR>';

$result = mysqli_query($con, $sql) or die("Query failed. dag " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $formatter->setPattern('d LLLL yyyy');
    $datum = $txt["nodata"] . datefmt_format($formatter, $chartdate);
   
    $geengevdag = 0;
    
} else {
    $formatter->setPattern('d LLL yyyy');
    $datum = datefmt_format($formatter, $chartdate);
    $geengevdag = 1;
    
    while ($row = mysqli_fetch_array($result)) {
        
        $today_utc = $row['datumtijd'];//readable time
        //$date = new DateTime($today_utc, new DateTimeZone('UTC'));
		//$date->setTimezone(new DateTimeZone('Europe/Amsterdam'));
        //$today_dst= $date->format('Y-m-d H:i:s');
        $inverter_name = $row['naam'];
        
        $all_valarray[strtotime($today_utc)] [$inverter_name] = $row['gem'];
       
        
        if (!in_array($inverter_name, $inveter_list)) {
            if (in_array($inverter_name, $sNaamSaveDatabase)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        };
    }
}

//--------------------------------------------------------------------------------------------------
// get best day and kWh for current month (max value over all years for current month)
$sqlmaxdag = "SELECT Datum_Maand, Geg_Maand
	 FROM " . $table_prefix . "_maand
	 JOIN (SELECT month(Datum_Maand) AS maand, max(Geg_Maand) AS maxgeg FROM " . $table_prefix . "_maand WHERE 
     DATE_FORMAT(Datum_Maand,'%m')='" . date('m', $chartdate) . "' " . " GROUP BY naam, maand )AS maandelijks ON (month(" .
    $table_prefix . "_maand.Datum_Maand) = maandelijks.maand AND maandelijks.maxgeg = " . $table_prefix . "_maand.Geg_Maand) ORDER BY maandelijks.maand";
$resultmaxdag = mysqli_query($con, $sqlmaxdag) or die("Query failed. dag-max " . mysqli_error($con));
if (mysqli_num_rows($resultmaxdag) == 0) {
    $maxdag = date("m-d", time());
    $maxkwh[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultmaxdag)) {
        $maxdag = $row['Datum_Maand'];
        $maxkwh[] = round($row['Geg_Maand'], 2);
    }
}
$nice_max_date = date("Y-m-d", strtotime($maxdag));

//-----------------------------------------------------
//query for the best day
$sqlmdinv = "SELECT Geg_Dag AS gem, STR_TO_DATE( CONCAT( DATE( Datum_Dag ) ,  ' ', HOUR( Datum_Dag ) ,  ':', LPAD( FLOOR( MINUTE( Datum_Dag ) /" . $param['isorteren'] . " ) *" . $param['isorteren'] . ", 2,  '0' ) ,  ':00' ) ,  '%Y-%m-%d %H:%i:%s' ) AS datumtijd, Naam AS Name
FROM " . $table_prefix . "_dag
WHERE Datum_Dag LIKE  '" . date("Y-m-d", strtotime($maxdag)) . "%'
ORDER BY Name, datumtijd ASC";
$resultmd = mysqli_query($con, $sqlmdinv) or die("Query failed. dag-max-dag " . mysqli_error($con));
if (mysqli_num_rows($resultmd) == 0) {
    $maxdagpeak = 0;
    //$agegevensdag_max[] = 0;
} else {
    $maxdagpeak = 0;
    while ($row = mysqli_fetch_array($resultmd)) {
        $inverter_name = $row['Name'];
        $time_only = substr($row['datumtijd'],-9);
        $today_max_utc = $chartdatestring . $time_only;//readable time
        //$date = new DateTime($today_max_utc, new DateTimeZone('UTC'));
		//$date->setTimezone(new DateTimeZone('Europe/Amsterdam'));
        //$today_max_dst= $date->format('Y-m-d H:i:s');
        $all_valarraymax[strtotime($today_max_utc)] [$inverter_name] = $row['gem'];
        if ($row['gem'] > $maxdagpeak) {
            $maxdagpeak = $row['gem'];
        };
    }
}
//--------------------------------------------------------------------------------------------------
$strgegmax = "";
$strsomkw = "";
// build colors per inverter array
$myColors = array();
for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
    $col1 = "color_inverter" . $k . "_chartbar_min";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k . "_chartbar_max";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['max'] = $col1;
}
$str_dataserie = "";
$cnt = 0;
foreach ($inveter_list as $inverter_name) {
    $col1 = $myColors[$inverter_name]['min'];
    $col2 = $myColors[$inverter_name]['max'];
    $str_dataserie .= "{ name: '$inverter_name', id: '$inverter_name', type: 'area', marker: { enabled: false },  color: { linearGradient: {x1: 0, x2: 0, y1: 1, y2: 0}, stops: [ [0, $col1], [1, $col2]] },                        
    data:[";
    foreach ($all_valarray as $time => $valarray) {
        if (!isset($valarray[$inverter_name])) $valarray[$inverter_name] = 0;
            $str_dataserie .= '{x:' . ($time * 1000) . ', y:' . $valarray[$inverter_name] . ', unit: \'W\'}, ';
    }
    $str_dataserie = substr($str_dataserie, 0, -1);
    $str_dataserie .= "]}, 
                    ";
    $cnt++;
}
// day max line per inverter --------------------------------------------------------------
$str_max = "";
$cnt = 0;

foreach ($sNaamSaveDatabase as $key=>$inverter_name) {
    if($key == 0) { 
        $dash = '';
    } elseif($key> 0) { 
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
            $str_max .= '{x:' . ($time * 1000) . ', y:' . $valarraymax[$inverter_name] . ', unit: \'W\'}, ';
    }
    $str_max = substr($str_max, 0, -1);
    $str_max .= "]}, 
                    ";
    $cnt++;
}
// remember last date
$max_last_val = $time;
$str_max = substr($str_max, 0, -1);
$strgegmax = substr($strgegmax, 0, -1);
$external_sensors = isset($param['external_sensors']);
$temp_serie = "";
$temp_unit = "°C";
$val_max = 0;
$val_min = 0;
if ($external_sensors) {
    include "charts/temp_sensor_inc.php";
}

$show_legende = "true";
if ($isIndexPage == true) {
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
$maxlink = '<a href= ' . HTML_PATH . 'pages/day_overview.php' . $paramstr_day . 'dag=' . $nice_max_date . '><span style="font-family:Arial,Verdana;font-size:12px;font-weight:12px;color:' .$colors['color_chart_text_subtitle'].' ;">'. $nice_max_date .'</span></a>';
//print_r($maxlink);
include_once "chart_styles.php";
$show_temp_axis = "false";
$show_cum_axis = "true";
if (strlen($temp_serie) > 0) {
    $show_temp_axis = "true";
    $show_cum_axis = "false";
}
?>
<script type="text/javascript">
    $(function () {
        function add(accumulator, a) { return accumulator + a;	}
        
        var myoptions = <?php echo $chart_options ?>;
        var khhWp = [<?php echo $param['ieffectief_kwpiekst'] ?>];
        var nmbr =  khhWp.length //misused to get the inverter count
        var maxmax = <?php echo json_encode($maxkwh) ?>;
        
        var maxlink = '<?php echo $maxlink ?>';
        var temp_max = <?php echo $val_max ?>;
        var temp_min = <?php echo $val_min ?>;
        var txt_actueel = '<?php echo $txt["actueel"] ?>';
        var txt_totaal = '<?php echo $txt['totaal'] ?>';
        var txt_max = '<?php echo $txt['max'] ?>';
        var txt_peak = '<?php echo $txt['peak'] ?>';
        
        Highcharts.setOptions({<?php echo $chart_lang ?>
    time: {
        /**
         * Use moment-timezone.js to return the timezone offset for individual
         * timestamps, used in the X axis labels and the tooltip header.
         */
        getTimezoneOffset: function (timestamp) {
            var zone = 'Europe/Amsterdam',
                timezoneOffset = -moment.tz(timestamp, zone).utcOffset();

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
                        // construct subtitle
                        sum =[];
                        kWh =[];
                        peak = [];
                        max =[];
                        current = 0;
                        tota = 0;
                        
                        for (i = nmbr-1; i >= 0 ; i--) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    tota += (series[i].data[j].y) / 12000;//Total
                                    sum[i] = (series[i].data[series[i].data.length - 1]).y; //sum
                                    current = Highcharts.dateFormat('%H:%M', (series[i].data[series[i].data.length - 1]).x);//TIME
                                    kWh[i] = khhWp[i]; //KWH
                            		max[i] = maxmax[i]; //MAXday
                                    peak[i] = series[i].dataMax //PEAK
                                }
                            }
                        }

                        SUM = sum.reduce(add, 0);
                        KWH = kWh.reduce(add, 0);
                        MAX = max.reduce(add, 0);
                        var dataMax = mychart.yAxis[1].dataMax;
                        console.log(dataMax,KWH,tota);
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {PEAK = 0;}
						else {
                        PEAK = AX[0];}
                        this.setSubtitle({
                            text: "<b>" + txt_actueel + ": </b>" + current + " -  " + Highcharts.numberFormat(SUM, 0, ",", "") +
                                "W" + "=" + (Highcharts.numberFormat(100 * SUM / KWH, 0, ",", "")) + "%" + " - " + txt_peak + ": " + PEAK + "W <br/><b>" +
                                txt_totaal + ":</b> " + (Highcharts.numberFormat(dataMax, 2, ",", "")) + "kWh = " +
                                (Highcharts.numberFormat((dataMax / KWH) * 1000, 2, ",", "")) + "kWh/kWp" + " <b>" +
                                txt_max + ": </b>" + maxlink + " " + (Highcharts.numberFormat(MAX, 2, ",", "")) + " kWh"
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
                               if (s.type === 'area' && s.visible ) {
                                    indexOfVisibleSeries.push(s.index);
                                }
                            });
							if (checkHideForSpline) {
                                for (i = 0; i < mychart.series[0].data.length; i++) {
                                    for (h of indexOfVisibleSeries) {
                                        value += mychart.series[h].data[i].y / 12000;
                                        axis = mychart.series[h].data[i].x;
                                    }
                                    if(typeof axis !== 'undefined') {
                                        total.push([axis, value]);
                                    }
                                }
                                mychart.addSeries({
                                    data: total,
                                    name: 'Cum',
                                    
                                    yAxis: 1,
                                    unit: 'kWh',
                                    type: "spline",
                                    color: '#<?php echo $colors['color_chart_cum_line'] ?>',
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
        			legendItemClick: function() {
          			var clickedSeries = this,
            		lineSeries = clickedSeries.chart.series.filter(series => series.type === 'line'),
            		visibleLineSeries = [];
          			lineSeries.forEach(function(series) {
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
                    wordWrap:'break-word',
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
                    text: 'Power',
                    style: {
                        color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>'
                    },
                    visible: false
                },
                // min: 0,
                labels: {
                    format: '{value} kW',
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>'
                    },
                    formatter: function () {
                        return Highcharts.numberFormat(this.value / 1000, 1,',','.') + " kW";
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
                            return Highcharts.numberFormat(this.value, 1,',','.') + " kWh";
                        }
                    },
                    gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis2'] ?>',
                    opposite: true,
                    visible: <?php echo $show_cum_axis ?>
                },
                { // temperature
                    title: {
                        text: 'Temperature',
                        style: {
                            color: '#<?php echo $colors['color_chart_title_yaxis3'] ?>',
                        },
                    },
                    labels: {
                        format: '{value}<?php echo $temp_unit ?>',
                        style: {
                            color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>',
                        },
                        formatter: function () {
                            return this.value + "<?php echo $temp_unit ?>";
                        },
                    },
                    gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis3'] ?>',
                    opposite: true,
                    visible: <?php echo $show_temp_axis ?>,
                    steps: 5,
                    min: temp_min,
                    max: temp_max,
                }
            ],
            series: [
                <?php echo $str_dataserie ?>
                <?php echo $str_max ?>
                <?php echo $temp_serie ?>
            ]
        }), function (mychart) {
            mychart.forRender = true
        });
        setInterval(function() {
  		$("#mycontainer").highcharts().reflow();  }, 500);
    });
</script>