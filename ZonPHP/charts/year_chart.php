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

$chartcurrentdate = @mktime();
$chartdate = $chartcurrentdate;

$chartdatestring = strftime("%Y-%m-%d", $chartdate);

if (isset($_GET['jaar'])) {
    $chartdatestring = html_entity_decode($_GET['jaar']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = strftime("%Y-%m-%d", $chartdate);
}
// -------------------------------------------------------------------------------------------------------------
$current_year = date('Y', $chartdate);
$current_euroval = 0.25;
if (array_key_exists($current_year, $year_euro)) {
    $current_euroval = $year_euro[$current_year];
}

$sqlverbruik = "SELECT *
	FROM " . $table_prefix . "_verbruik
	WHERE DATE_FORMAT(Datum_Verbruik,'%y')='" . date('y', $chartdate) ."'" ;

$resultverbruik = mysqli_query($con, $sqlverbruik) or die("Query failed. jaar-verbruik " . mysqli_error($con));
if (mysqli_num_rows($resultverbruik) == 0) {
    $ajaarverbruikdag[] = 0;
    $ajaarverbruiknacht[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultverbruik)) {
        $ajaarverbruikdag[date("n", strtotime($row['Datum_Verbruik']))] = $row['Geg_Verbruik_Dag'];
        $ajaarverbruiknacht[date("n", strtotime($row['Datum_Verbruik']))] = $row['Geg_Verbruik_Nacht'];
    }
}

$sqlref = "SELECT Naam, SUM(Geg_Refer) as sum_geg_refer, SUM(Dag_Refer) as sum_dag_refer, Datum_Refer
	FROM " . $table_prefix . "_refer " .
	" GROUP BY Naam, Datum_refer
	  ORDER BY Naam, Datum_Refer ASC";
//echo $sqlref;
$nfrefmaand = array();
$nfrefdagmaand = array();
$nfreftot = array();
$resultref = mysqli_query($con, $sqlref) or die("Query failed. jaar-ref " . mysqli_error($con));
if (mysqli_num_rows($resultref) == 0) {
    $frefmaand = array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
    $frefdagmaand = array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
} else {
    $frefmaand = array();
    $frefdagmaand = array();
    
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand[date("n", strtotime($row['Datum_Refer']))] = $row['sum_geg_refer'];
        $frefdagmaand[date("n", strtotime($row['Datum_Refer']))] = $row['sum_dag_refer'];
    	$nfrefmaand[date("n", strtotime($row['Datum_Refer']))][$row['Naam']] = $row['sum_geg_refer'];
        $x= $row['Naam'];
        @$nfreftot[$x] += $row['sum_geg_refer'];
        $nfrefdagmaand[date("n", strtotime($row['Datum_Refer']))][$row['Naam']] = $row['sum_dag_refer'];
    
    }
    $iyasaanpassen = (round(0.5 + max($frefmaand) / 50) * 50);
}

$nfreftot=array_values($nfreftot);
//print_r ($nfreftot);

//oude gemiddelde
$sql = "SELECT MAX( Datum_Maand ) AS maxi, SUM( Geg_Maand ) AS som, COUNT(Geg_Maand) AS aantal, naam
	FROM " . $table_prefix . "_maand
	where DATE_FORMAT(Datum_Maand,'%y')='" . date('y', $chartdate) . "'
	GROUP BY naam, month(Datum_Maand)
	ORDER BY naam ASC";
$result = mysqli_query($con, $sql) or die("Query failed. jaar " . mysqli_error($con));
$all_valarray = array();
$inveter_list = array();
if (mysqli_num_rows($result) == 0) {
    $datum = date("Y", $chartdate) . " geen data.";
    $agegevens = array();
    $fgemiddelde = 0;
    $agegaantal = array();
} else {
    $agegevens = array();
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];
        $agegevens[date("n", strtotime($row['maxi']))] = $row['som'];
        $agegaantal[date("n", strtotime($row['maxi']))] = $row['aantal'];

        $all_valarray[date("n", strtotime($row['maxi']))][$inverter_name] = $row['som'];
        if (!in_array($inverter_name, $inveter_list)){
            if (in_array($inverter_name, $sNaamSaveDatabase)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        } ;
    }
    $fgemiddelde = array_sum($agegevens) / count($agegevens);
    $datum = date("Y", $chartdate);
    $iyasaanpassen = (round(0.5 + max($agegevens) / 50) * 50);
}
//nieuwe gemiddelde
//print_r ($agegevens);
$sqlavg ="SELECT ROUND( SUM( Geg_Maand ) / ( COUNT( Geg_Maand ) /30 ) , 0 ) AS aantal
FROM " . $table_prefix . "_maand
WHERE DATE_FORMAT( Datum_Maand,  '%y' ) =  '" . date('y', $chartdate) . "'
GROUP BY naam
ORDER BY naam ASC"; 
//echo $sqlavg;
$result = mysqli_query($con, $sqlavg)or die("Query failed (gemiddelde) " . mysqli_error($con));
 while($row = mysqli_fetch_array($result)) {
   $avg_data[] = $row['aantal'];
}
//print_r ($avg_data);
$sqlmax = "SELECT maand,jaar,som, Name FROM (SELECT naam as Name, month(Datum_Maand) AS maand,year(Datum_Maand) AS jaar,sum(Geg_Maand) AS som FROM " . $table_prefix . "_maand GROUP BY naam, maand,jaar ) AS somquery JOIN (SELECT maand as tmaand, max( som ) AS maxgeg FROM ( SELECT naam, maand, jaar, som FROM ( SELECT naam, month( Datum_Maand ) AS maand, year( Datum_Maand ) AS jaar, sum( Geg_Maand ) AS som FROM " . $table_prefix . "_maand GROUP BY naam, maand, jaar ) AS somqjoin ) AS maxqjoin GROUP BY naam,tmaand )AS maandelijks ON (somquery.maand= maandelijks.tmaand AND maandelijks.maxgeg = somquery.som) ORDER BY Name, maand";
for ($i = 1; $i <= 12; $i++) {
    $maxmaand[$i] = 0;
}

$resultmax = mysqli_query($con, $sqlmax) or die("Query failed. jaar-max " . mysqli_error($con));
if (mysqli_num_rows($resultmax) == 0) {
    $maxmaand[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultmax)) {
        $maxmaand[$row['maand']] = $row['som'];
        $maxmaand_jaar[$row['maand']] = $row['jaar'];
    	$nmaxmaand[$row['maand']][$row['Name']] = $row['som'];
        $nmaxmaand_jaar[$row['maand']][$row['Name']] = $row['jaar'];
    
    }
    $iyasaanpassen = (round(0.5 + max($maxmaand) / 50) * 50);
}
//print_r ($nmaxmaand);
if (max($frefmaand) < max($maxmaand)) {
    $iyasaanpassen = (round(0.5 + max($maxmaand) / 50) * 50);
} else {
    $iyasaanpassen = (round(0.5 + max($frefmaand) / 50) * 50);
}
?>
<?php

$myColors = array();
for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
    $col1 = "color_inverter" . $k ."_chartbar_min";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k ."_chartbar_max";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['max'] = $col1;
}
$categories = "";
for ($i = 1; $i <= 12; $i++) {
    // get month names in current locale
    $categories .= '"' . ($months[$i]) . '",';
}

$categories = substr($categories, 0, -1);


$my_year = date("Y", $chartdate);
$href = "month_overview.php?maand=";
$gridlines = "";
$reflines = "";
$max_bars = "";
$expected_bars = "";
$current_bars = "";

$strdataseries = "";
foreach ($sNaamSaveDatabase as $key => $inverter_name) {
	if($key == 0) { 
        $dash = '';
    } elseif($key> 0) { 
        $dash = "dashStyle: 'shortdash',";
    }
    // build one serie per inverter
    $current_bars = "";
   
    $reflines .= "{ name: '$inverter_name ref', type:'line', $dash linkedTo: '$inverter_name', color: '#" . $colors['color_chart_reference_line'] . "',
         stacking: 'normal', data: [";
    //echo $inverter_name;echo ' ';
    
    $max_bars .= "{name: '$inverter_name max', type: 'column', zIndex: -1,  linkedTo: '$inverter_name', stack: 'max', stacking: 'normal', color: \"#" . $colors['color_chart_max_bar'] . "\" ,data: [";
    
    for ($i = 1; $i <= 12; $i++) {
    //echo $i;echo ' ';    // max bars
        //$val throws notice when database is missing months 
        @$val = round($nmaxmaand[$i][$inverter_name], 2);
        //echo $val;echo "<BR>";
        $max_bars .= "  { 
                          y:  $val, 
                          url: \"$href$my_year-$i-01\",
                          color: \"#" . $colors['color_chart_max_bar'] . "\"
                        },";

        $expected = 0.0;
        // only month with values
        if (array_key_exists($i, $agegevens)) {
            // expected bars char
           

            $myColor1 =$myColors[$inverter_name]['min'];
            $myColor2 =$myColors[$inverter_name]['max'];
            if ($agegevens[$i] == max($agegevens)) {
                $myColor1 = "'#" .$colors['color_chartbar_piek1'] . "'";
                $myColor2 = "'#" .$colors['color_chartbar_piek2'] . "'";
            }

            // normal actual  bar
            $val = round($all_valarray[$i][$inverter_name], 2);
            $current_bars .= "
                        { x: $i-1, 
                          y: $val, 
                          url: \"$href$my_year-$i-01\",
                          color: {
                            linearGradient: { x1: 0, x2: 0, y1: 1, y2: 0 },
                            stops: [
                                [0, $myColor1],
                                [1, $myColor2]
                            ]}                                                       
                        },";
        }
        // refline per bar
        $z= $nfrefmaand[$i][$inverter_name];
        $reflines .= "$z, $z, $z, null,";
    }
     
    $max_bars .="]},";
    $reflines .="]},";
    
    $current_bars = substr($current_bars, 0, -1);
    $strdataseries .= " {
                    name: '". $inverter_name. "',
                    id: '". $inverter_name. "',
                    color: { linearGradient: {x1: 0, x2: 0, y1: 0, y2: 1}, stops: [ [0, $myColor1], [1, $myColor2]] },
                    type: 'column',
                    stacking: 'normal',
                    data: [".$current_bars."]
                },
    ";
}
$max_bars = substr($max_bars, 0, -1);
//echo $max_bars;
$expected_bars = substr($expected_bars, 0, -1);
$current_bars = substr($current_bars, 0, -1);
$reflines = substr($reflines, 0, -1);
$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="year_chart_' . $inverter_id . '"></div>';
    $show_legende = "false";
};

include_once "chart_styles.php";
?>

<script type="text/javascript">
	    $(function () {
        function add(accumulator, a) {
    	return accumulator + a;
			}  
        
        
        var year = '<?php echo strftime("%Y", $chartdate) ?>';
        var avrg =<?php echo round($fgemiddelde, 2) ?>;
        var myoptions = <?php echo $chart_options ?>;
		var khhWp = [<?php echo $param['ieffectief_kwpiekst'] ?>];
        var nmbr =  khhWp.length //misused to get the inverter count
        var txt_max = '<?php echo $txt['max'] ?>';
        var totayr = 0;
        var avg = <?php echo json_encode ($avg_data, JSON_NUMERIC_CHECK) ?>;
        var ref = <?php echo json_encode ($nfreftot, JSON_NUMERIC_CHECK) ?>;
        var txt_gem = '<?php echo $txt['gem'] ?>';
        var mychart = new Highcharts.Chart('year_chart_<?php echo $inverter_id ?>', Highcharts.merge(myoptions, {

            chart: {
                events: {
                    render() {
                        // make serias public available
                        mychart = this;
                        series = this.series;
                        totayr = 0;
                        kWh =[];
                        gem =[];
                        sum =[];
                        refref=[];
                        peak = [];
                        for (i = nmbr-1; i >= 0 ; i--) {
                            if (series[i].visible) {
                                for (j = 0; j < series[i].data.length; j++) {
                                    totayr += (series[i].data[j].y) ;//Total
                                    kWh[i] = khhWp[i]; //KWH
                                    sum = series[i].data.length
                                   	refref[i]=ref[i]
                                   	gem[i] = avg[i] ;
                                    peak[i] = series[i].dataMax //PEAK
                                    //refref[i] = nref[i];
                                }
                            }
                        }
                        // alert (mychart.axes[0].categories[2])
                        TOT = totayr;
                        KWH = kWh.reduce(add, 0);
                        GEM = gem.reduce(add, 0);
                        GE2= GEM;
                        REF = refref.reduce(add, 0);
                        var AX = peak.filter(Boolean);
                        if (AX.length == 0) {PEAK = 0;}
						else {
                        PEAK = AX[0];};
                        
                        this.setSubtitle({
                            text: "<b>" + year + ": </b>" +(Highcharts.numberFormat(totayr, 0, ",", "")) + " kWh = " + (Highcharts.numberFormat((totayr / KWH) * 1000, 0, ",", "")) + " kWh/kWp = " +(Highcharts.numberFormat((totayr/REF*100), 0, ",", ""))+ " % <br/><b>" 
                                +txt_max + ": </b>" +(Highcharts.numberFormat(PEAK, 0, ",", "")) + " kWh = " +
                                (Highcharts.numberFormat((PEAK / KWH) * 1000, 0, ",", "")) + " kWh/kWp" + " <b>" +
                                txt_gem + ": </b>" +(Highcharts.numberFormat(GEM, 0, ",", "")) + " kWh" //+ REF //" <b>" +txt_ref + ": </b>" + (Highcharts.numberFormat(REF, 2, ",", "")) + " kWh"
                        }, false, false);
                        //average plotline
                        mychart.yAxis[0].addPlotLine({
    					id	: 'Average',
    					value : GEM,
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
                        
                    }
                }
            },
            
            subtitle: {
                //text: sub_title,
                style: {
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
                },
            },

            xAxis: [{
                labels: {
                    rotation: 0,
                    step: 1,
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                    },
                },
                min: 0,
                max: 11,
                categories: [<?php echo $categories ?>],
                
            }],
          
        	
            plotOptions: {
                line: {
            		pointStart: -0.250,
            		pointInterval: 0.25
        			},
                
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
        			legendItemClick: function() {
          			
          			var clickedSeries = this,
            		lineSeries = clickedSeries.chart.series.filter(series => series.type === 'line'),
            		visibleLineSeries = [];
          			
          			lineSeries.forEach(function(series) {
            // Set all series to "dot"
            		if (series.options.dashStyle === 'solid') {
              			series.update({
                		dashStyle: 'shortdash'
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
          				//console.log (visibleLineSeries)
          	// Set first visible series to "solid"
          			if (visibleLineSeries.length) {
            			visibleLineSeries[0].update({
              			dashStyle: 'solid'
          				})
          				mychart.yAxis[0].removePlotLine('Average');
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
                    //stacking: 'normal'
                },
            },
            
            
            yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function () {
                        return this.value + 'kWh';
                    },
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>',
                    },
                },
                title: {
                    text: 'Total',
                    style: {
                        color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>'
                    },
                },
                steps: 100,
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
            }],
            tooltip: {
                formatter: function () {
                
                    if (this.series.name == 'kWh/Month') {
                        return this.x + ': ' + this.y.toFixed(0) + 'kWh';
                    }
                    else {
                        if (this.series.name.indexOf("ref_") == 0)
                        {
                            return 'Ref ' + this.y.toFixed(0) + 'kWh';
                        }
                        if ((typeof(this.x) == 'undefined') && this.y == GEM ) {
                        return mychart.yAxis[0].plotLinesAndBands[0].id + ' ' + this.y.toFixed(0)  + ' kWh';
                    }
                        else
                        {
                        
                        var chart = this.series.chart,
        		x = this.x,
        		stackName = this.series.userOptions.stack,
        		contribuants = '';
        		
        		if (isNaN(x)){x= x}
        		else{x= mychart.axes[0].categories[Math.round(x)]}
        		
        		

      			chart.series.forEach(function(series) {
        		series.points.forEach(function(point) {
          		if (point.category === x && stackName === point.series.userOptions.stack ) {
            	contribuants += '<span style="color:'+ point.series.color +'">\u25CF</span>' + point.series.name + ': ' + point.y.toFixed(0) + ' kWh<br/>'
          			}
       			 })
      			})
				if (stackName === undefined) {stackName = '';}
      			return '<b>'+ x +' ' + stackName + '<br/>' + '<br/>' + contribuants + 'Total: ' + this.point.stackTotal.toFixed(0) +  ' kWh';
                        /* return this.x + ': ' + Highcharts.numberFormat(this.y, '2', ',') +  ' kWh'; */
                    }
                    }
                }
            },
			
            series: [
                <?php echo $strdataseries ?>
                <?php echo $reflines; ?>,
                <?php echo $max_bars; ?>,
            ]
        }));
		
        setInterval(function() {
  	$("#year_chart_<?php echo $inverter_id ?>").highcharts().reflow();  }, 500);
        
    });


</script>
