<?php
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "Parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}


$isIndexPage = false;
$showAllInverters = true;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

if (isset($_GET['naam'])) {
    $inverter_id = $_GET['naam'];
    $showAllInverters = false;
} else if (isset($_POST['inverter'])) {
    $inverter_id = $_POST['inverter'];
    $showAllInverters = false;
} else {
    $inverter_id = "all";
}

$chartcurrentdate = time();
$chartdate = $chartcurrentdate;

$chartdatestring = date("Y-m-d", $chartdate);

if (isset($_GET['maand'])) {
    $chartdatestring = html_entity_decode($_GET['maand']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = date("Y-m-d", $chartdate);
}


// -----------------------------  get data from DB -----------------------------------------------------------------

$current_year = date('Y', $chartdate);
$current_month = date('m', $chartdate);
$current_year_month = "" . date('Y-m', $chartdate);

if (isset($year_euro[$current_year])) {
    $current_euroval = $year_euro[$current_year];
} else {
    $current_euroval = 0.25;
}

// get reference values
$sqlref = "SELECT *
        FROM " . $table_prefix . "_refer
        WHERE DATE_FORMAT(Datum_Refer,'%m')='" . $current_month . "'" . "
        ORDER BY Naam, Datum_Refer ASC";
$resultref = mysqli_query($con, $sqlref) or die("Query failed. maand-ref " . mysqli_error($con));

$nfrefmaand = array();
if (mysqli_num_rows($resultref) == 0) {
    $frefmaand = 1;
} else {
    while ($row = mysqli_fetch_array($resultref)) {
        
    	$nfrefmaand[] = $row['Dag_Refer'];
    }
}

$DaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

$sql = "SELECT Datum_Maand, Geg_Maand, naam
        FROM " . $table_prefix . "_maand
        where Datum_Maand like '" . $current_year_month . "%'
        GROUP BY Datum_Maand, naam
        ORDER BY Naam, Datum_Maand ASC";
$result = mysqli_query($con, $sql) or die("Query failed. maand " . mysqli_error($con));
$daycount=0;
$all_valarray = array();
$inveter_list = array();
if (mysqli_num_rows($result) == 0) {
    $agegevens[] = 0;
    //$iyasaanpassen = $frefmaand * 1.5;
    $geengevmaand = 0;
    $fgemiddelde = 0;
} else {
    $geengevmaand = 1;
    $agegevens = array();
    // fill empty days
    for ($i = 1; $i <= $DaysPerMonth; $i++) {
        $agegevens[$i] = 0;
    }
    for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
        for ($i = 1; $i <= $DaysPerMonth; $i++) {
            $all_valarray[$i][$sNaamSaveDatabase[$k]] = 0;
        }
    }

    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];
        $adatum[] = date("j", strtotime($row['Datum_Maand']));
        $agegevens[date("j", strtotime($row['Datum_Maand']))] += $row['Geg_Maand'];
        $all_valarray[ date("j", strtotime($row['Datum_Maand']))] [$inverter_name]  = $row['Geg_Maand'];
        $dmaandjaar[] = $row['Datum_Maand'];
        
    }
    $daycount=0;
    for ($i = 1; $i <= $DaysPerMonth; $i++) {
        if ($agegevens[$i] > 0) {
            $daycount++;
        }
    }
    if ($daycount == 0) {
        $daycount=1;
    }
    $fgemiddelde = array_sum($agegevens) / $daycount;
    $iyasaanpassen = round(0.5 + max($agegevens) / 5) * 5;
}

?>


<?php
// -----------------------------  build data for chart -----------------------------------------------------------------
// build colors per inverter array
$myColors = array();
for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
    $col1 = "color_inverter" . $k ."_chartbar_min";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k ."_chartbar_max";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['max'] = $col1;
}

// collect data array
$myurl = "day_overview.php?dag=";

$categories = "";
$strdataseries = "";
$maxval_yaxis = 0;

foreach ($sNaamSaveDatabase as $inverter_name) {

    $strdata = "";
    $local_max = 0;
    for ($i = 1; $i <= $DaysPerMonth; $i++) {
        $categories .= '"' . $i . '",';
        if (array_key_exists($i, $agegevens)) {

            $myColor1 =$myColors[$inverter_name]['min'];
            $myColor2 =$myColors[$inverter_name]['max'];
            
            if ($agegevens[$i] == max($agegevens)) {
                $myColor1 = "'#" . $colors['color_chartbar_piek1'] . "'";
                $myColor2 = "'#" . $colors['color_chartbar_piek2'] . "'";
            }
            $var = round($all_valarray[$i][$inverter_name], 2);
            if ($var > $local_max ) $local_max = $var;
            
            $strdata .= "
                    {
                      y: $var, 
                      url: \"$myurl$current_year_month-$i\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 1, y2: 0 },
                        stops: [
                            [0, $myColor1],
                            [1, $myColor2]
                        ]}                                                       
                    },";
        }
    else
            {$myColor1="'#FFAABB'";
            $myColor2="'#FFAABB'";}
    }
    
    
  
    
    
    $maxval_yaxis += $local_max;
    $local_max = 0;
    $strdata = substr($strdata, 0, -1);
    $strdataseries .= " {
                    name: '". $inverter_name. "',
                    color: " . $myColors[$inverter_name]['max'] . ",
                    type: 'column',
                    stacking: 'normal',
                    data: [".$strdata."]
                },
    ";
	
}
$categories = substr($categories, 0, -1);


$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="month_chart_' . $inverter_id . '"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>

<script type="text/javascript">
	
    $(function () {

        function add(accumulator, a) {
    return accumulator + a;
}  
    
		var month = '<?php echo date("M", $chartdate) ?>';
        var daycount = <?php echo $DaysPerMonth ?>;
        var daycount2 = <?php echo $daycount ?>;
        var nref = <?php echo json_encode ($nfrefmaand, JSON_NUMERIC_CHECK) ?>;
        var myoptions = <?php echo $chart_options ?>;
		var khhWp = [<?php echo $param['ieffectief_kwpiekst'] ?>];
        var nmbr =  khhWp.length //misused to get the inverter count
		var txt_max = '<?php echo $txt['max'] ?>';
		var txt_gem = '<?php echo $txt['gem'] ?>';
        var txt_ref = '<?php echo $txt['ref'] ?>';
        var gem2 ;
		var totamth = 0;
        var mychart = new Highcharts.Chart('month_chart_all', Highcharts.merge(myoptions, {
		
            chart: {
                events: {
                    render() {
                        mychart = this;
                        series = this.series;
                        gem = 0; 
                        // construct subtitle
                        sum =[];
                        kWh =[];
                        peak = [];
                        max =[];
                       	refref = [];
                       	current = 0;
                        totamth = 0;
                        for (i = nmbr-1; i >= 0 ; i--) {
                            if (series[i].visible) {
                                	kWh[i] = khhWp[i]; //KWH
                                    sum = series[i].data.length;
                                	peak[i] = series[i].dataMax; //PEAK
                                    refref[i] = nref[i];
                                for (j = 0; j < series[i].data.length; j++) {
                                    totamth += (series[i].data[j].y) ;//Total
                                   	gem = totamth/daycount2 ;
                                    
                                }
                            }
                        }
                        gem2 = gem
                        TOT = totamth;
                        KWH = kWh.reduce(add, 0);
                        MAX = max.reduce(add, 0);
                        REF = refref.reduce(add, 0);
                        //console.log(REF);
                        ref2=REF;
                        percent = 100*TOT/(REF*daycount)
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {PEAK = 0;}
						else {
                        PEAK = AX[0];};
                                                
                        this.setSubtitle({
                            text: "<b>" + month + ": </b>" +(Highcharts.numberFormat(totamth, 2, ",", "")) + " kWh = " + (Highcharts.numberFormat((totamth / KWH) * 1000, 2, ",", "")) + " kWh/kWp = " +(Highcharts.numberFormat(percent, 0, ",", ""))+ "% <br/><b>" +
                                txt_max + ": </b>" +(Highcharts.numberFormat(PEAK, 2, ",", "")) + " kWh = " +
                                (Highcharts.numberFormat((PEAK / KWH) * 1000, 2, ",", "")) + " kWh/kWp" + " <b>" +
                                txt_gem + ": </b>" +(Highcharts.numberFormat(gem, 2, ",", "")) + " kWh" + " <b>" +txt_ref + ": </b>" + (Highcharts.numberFormat(REF, 2, ",", "")) + " kWh"
                        }, false, false);
                        //average plotline
                        mychart.yAxis[0].addPlotLine({
    					id	: 'Average',
    					value : gem2,
    					color : '#<?php echo $colors['color_chart_average_line'] ?>',
    					dashStyle : 'shortdash',
    					events: {
          				mouseover: function(e) {
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
        				mouseout: function(e) {
          				this.axis.chart.tooltip.hide();
        				}
      					},
    					width : 2,
    					});
    					//reference plotline
    					mychart.yAxis[0].addPlotLine({
    					id	: 'Reference',
    					value : ref2,
    					color : '#<?php echo $colors['color_chart_reference_line'] ?>',
    					dashStyle : 'shortdash',
    					//tooltip reference line
    					events: {
          				mouseover: function(e) {
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
        				mouseout: function(e) {
          				this.axis.chart.tooltip.hide();
        				}
      					},
    					width : 2,
    					});  
                    },
                    
                } 
            
            },
			plotOptions: {
	        series: { 
                	states: {
                        hover: {
                        enabled: false,
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
                   mychart.yAxis[0].removePlotLine('Average');
                   mychart.yAxis[0].removePlotLine('Reference');
                   
								}
							},
						showInLegend: true
							}
						},
            
            subtitle: {
                style: {
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
                	},
            	},

            xAxis: [{
                labels: {
                    rotation: 270,
                    step: 1,
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                        fontSize: '0.7em'
                    },
                },
                categories: [<?php echo $categories ?>],
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function () {
                        return this.value + ' kWh';
                    },
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>',
                    },
                },
                opposite: true,
                title: {
                    text: 'Total',
                    style: {
                        color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>'
                    },
                },
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
                max: <?php echo $maxval_yaxis ?>,
            		}],
            tooltip: {
            
                formatter: function () {
                    if ((typeof(this.x) == 'undefined') && this.y == ref2 ) {
                        return mychart.yAxis[0].plotLinesAndBands[1].id + ' ' + this.y.toFixed(2) + ' kWh';
                    }
                    if ((typeof(this.x) == 'undefined') && this.y == gem2 ) {
                        return mychart.yAxis[0].plotLinesAndBands[0].id + ' ' + this.y.toFixed(2)  + ' kWh';
                    }
                                        
                    else {
                        var chart = this.series.chart,
        		x = this.x,
        		stackName = this.series.userOptions.stack,
        		contribuants = '';
        		//console.log(x);

      			chart.series.forEach(function(series) {
        		series.points.forEach(function(point) {
          		if (point.category === x && stackName === point.series.userOptions.stack) {
            	contribuants += '<span style="color:'+ point.series.color +'">\u25CF</span>' + point.series.name + ': ' + Highcharts.numberFormat(point.y, '2', ',') + ' kWh<br/>'
          			}
       			 })
      			})
				if (stackName === undefined) {stackName = '';}
      			return '<b>'+ x +' ' + stackName + '<br/>' + '<br/>' + contribuants + 'Total: ' + Highcharts.numberFormat(this.point.stackTotal, '2', ',') +  ' kWh';
                        /* return this.x + ': ' + Highcharts.numberFormat(this.y, '2', ',') +  ' kWh'; */
                    }
                }
            },
            
            series: [
                <?php echo $strdataseries ?>

            ],
			
        }));
       
        setInterval(function() {
  	$("#month_chart_<?php echo $inverter_id ?>").highcharts().reflow();  }, 500);
        
       
    });


</script>



