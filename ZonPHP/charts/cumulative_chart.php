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

$currentdate = date("Y-m-d");
$sql = "SELECT YEAR(Datum_Maand) AS DYEAR, DATEDIFF(max(Datum_Maand),min(Datum_Maand)) AS Days, ((UNIX_TIMESTAMP(DATE_FORMAT(DATE_ADD(Datum_Maand,INTERVAL (YEAR('$currentdate') - YEAR(Datum_Maand)) YEAR), '%Y-%m-%d'))*1000)+86400000) AS timestamp, IFNULL( Datum_Maand, 'TOTAAL' ) AS Datum_Maand, 
		ROUND(SUM( Geg_Maand ),2) Total, IFNULL( naam, 'ALL' ) AS naam, '0' AS 'STotal'
        FROM " . $table_prefix . "_maand
        GROUP BY Datum_Maand, naam
		WITH ROLLUP";
//echo $sql;
$result = mysqli_query($con, $sql) or die("Query failed. maand " . mysqli_error($con));
$values = array();
$names = array();
$years = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $values[] = $row;
        $names[]  = $row["naam"];
        $years[]  = date("Y", strtotime($row["Datum_Maand"]));
    }
} else {
    echo "0 results";
}
$names = array_values(array_unique($names));
$years = array_values(array_unique($years));
$strip = array_pop($years);//haalt laatste 'ROLLUP' record uit $years
$stripped = array_pop($values);//haalt laatste 'ROLLUP' record uit $values
$Grand_total = Round($stripped['Total'],0);//Total yield
$All_Days = $stripped['Days'];
$All_Avg = Round($Grand_total/($All_Days/365),0);

$subtitle = '"<b>Total yield all inverters:</b> '.$Grand_total.' kWh <br><b>Average yield per year:</b> '. $All_Avg .'   kWh"'   ;

$value = array();
$all = array();
$peryear = array();
$strdataseries = "";
$add = (!isset($_POST['add']) ? 0 : $_POST['add']);
//when 'ALL' isn't last key in $names adjust $maxkey to correct number
//this happens when historical startdates are not the same 
$maxkey=max(array_keys($names));
$_SESSION['capnum'] = ((isset($_SESSION['capnum'])) ? $_SESSION['capnum'] : $maxkey);//number reflects all
if(isset($_POST['add'])){
     $_SESSION['capnum']++;
}
if ($_SESSION['capnum']>(count($names)-1)){$_SESSION['capnum']=0;}
$title=$names[$_SESSION['capnum']];


Foreach($values as $value)
	{
		if($value['naam'] == $title )
		$all[]=$value;
	}
$strdata = "";
for($i=0; $i<count($years);$i++)
{
	$sumtotal = 0;
	$strdata = "";
	Foreach($all as $allsum)
	{	
		if($allsum['DYEAR']== $years[($i)])
		{
		 $sumtotal += $allsum['Total'];
		 $allsum['STotal'] = $sumtotal;
		 $peryear[] = $allsum;
		 $strdata .= "[ $allsum[timestamp], $allsum[STotal] ],";
		}
	}
	$strdataseries .= " { name: '". $years[($i)] . "',
							type: 'line',
							marker: { enabled: false },
							data: [".$strdata."]},
						";
}
//print "<pre>";
//print_r($peryear);
//print "</pre>";
?>
<?php
$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="universal"></div>';
    $show_legende = "false";
}
include_once "chart_styles.php";
$categories = $shortmonthcategories;
?>
<script type="text/javascript">
    $(function () {
        var myoptions = <?php echo $chart_options ?>;
        Highcharts.setOptions({<?php echo $chart_lang ?>});
        var mychart = new Highcharts.Chart('universal', Highcharts.merge(myoptions, {
            chart: {
    			events: {
      			load: function() {
        this.series.forEach(function(s) {
          s.update({
            showInLegend: s.points.length
          });
        });
      },
      			render() {
        		var ticks = this.xAxis[0].ticks,
          		ticksPositions = this.xAxis[0].tickPositions,
          		tick0x,
              	tick1x,
              	getPosition = function (tick) {
                var axis = tick.axis;
              	return Highcharts.Tick.prototype.getPosition.call(tick, axis.horiz, tick.pos, axis.tickmarkOffset);
              	};
              
          		tick0x = getPosition(ticks[ticksPositions[0]]).x;
          		tick1x = getPosition(ticks[ticksPositions[1]]).x;
          
          		this.xAxis[0].labelGroup.translate((tick1x - tick0x)/2)
							}
    					}
  					},
			plotOptions: {
	        series: { 
                	states: {
                        hover: {
                        enabled: true,
                            lineWidth: 0,
                        		},
                        inactive: {
                    		opacity: 1
                				}
                			},
			              },
	        			},
            
            subtitle: {
            	 text: <?php echo $subtitle ?>,
                style: {
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
                	},
            	},
                xAxis: [{
        		id: 0,
        		type: 'datetime',
        		lineWidth: 0,
   				minorGridLineWidth: 0,
   				lineColor: 'transparent',
        		labels: {
            	enabled: false
        		},	
            	
            	minorTickLength: 0,
   				tickLength: 0
   				},
            	{
            	id: 1,
         		type: 'categories',
                labels: {
                    rotation: 0,
                    align: 'left',
                    step: 1,
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                    },
                },
                min: -0.5,
                max: 11.5,
                categories: [<?php echo $categories ?>],
                
            }],

            	yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function () {
                        return this.value/1000 + ' MWh';
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
                
            		}],
            tooltip: {
                crosshairs: [true],
                
                formatter: function () {
                var chart = this.series.chart,
        		x = this.x,
        		stackName = this.series.userOptions.stack,
        		contribuants = '';
      			chart.series.forEach(function(series) {
        		series.points.forEach(function(point) {
          		if (point.category === x && stackName === point.series.userOptions.stack) {
            	contribuants += '<span style="color:'+ point.series.color +'">\u25CF</span>' +point.series.name + ': ' + point.y + ' kWh<br/>'
          			}
       			 })
      			})
				if (stackName === undefined) {stackName = '';}
      			return '<b>'+ Highcharts.dateFormat('%B %e', x) +' ' + stackName + '<br/>' + '<br/>' + contribuants;
                }
            },
            
            series: [
                <?php echo $strdataseries ?>

            ],
        }));
       
        setInterval(function() {
  	$("#universal").highcharts().reflow();  }, 500);
    });
</script>
