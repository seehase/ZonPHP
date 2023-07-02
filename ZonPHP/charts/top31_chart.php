<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/load_cache.php";

$DESC_ASC = "DESC";
$showTopFlop = "top31_chart";
$isIndexPage = false;

if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
//echo $_GET['Max_Min'];
$sql = 'SELECT db1.*
FROM ' . TABLE_PREFIX . '_maand AS db1
JOIN (SELECT Datum_Maand, sum(Geg_Maand) as mysum FROM tgeg_maand  Group by Datum_Maand ORDER BY mysum ' . $DESC_ASC . ' LIMIT 0,31) AS db2
ON db1.Datum_Maand = db2.Datum_Maand order by mysum desc'; 

$result = mysqli_query($con, $sql) or die("Query failed. de_top_31_dagen " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $datum = "Geen data";
} else {
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['Naam'];
		$adatum[] = date("Y-m-d", strtotime($row['Datum_Maand']));
        $all_valarray[ date("Y-m-d", strtotime($row['Datum_Maand']))] [$inverter_name]  = $row['Geg_Maand'];
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
$myurl = 'day_overview.php?date=';
$myMetadata = array();
$myColors = array();

for ($k = 0; $k < count(PLANTS); $k++) {
    $col1 = "color_inverter" . $k . "_chartbar_min";
    $col1 = "'" . $colors[$col1] . "'";
    $myColors[PLANTS[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k . "_chartbar_max";
    $col1 = "'" . $colors[$col1] . "'";
    $myColors[PLANTS[$k]]['max'] = $col1;
}


$dataseries = "";
$maxval_yaxis = 0;

foreach (PLANTS as $key =>$inverter_name) {
    $data = "";
    $local_max = 0;
    $myColor1 =$myColors[$inverter_name]['min'];
    $myColor2 =$myColors[$inverter_name]['max'];
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
	$dataseries .= '['. $data .'],';
}
 
$meta = implode(', ', $myMetadata);
//$strdataseries = "";
$datafin = "";
$dataseries = substr($dataseries, 0, -1);
$datafin = '['.$dataseries.']';

// echo "<pre>";
// echo $datafin;
// echo "</pre>";

$id = $showTopFlop . '';
//echo $id;
$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="' . $id . '"></div>';
    $show_legende = "false";

}
include_once "chart_styles.php";

?>

<script>

    $(function () {
    	function add(accumulator, a) {
    	return accumulator + a;    	}  
		const data = <?= $datafin ?>;
		const categories = data[0].map(d => d[0]);
        const myurl = '<?= $myurl ?>';
        series = this.series;
        const khhWp = [<?= $params['plantskWp'] ?>];
        var nmbr =  khhWp.length //misused to get the inverter count
        const kwptot = khhWp.reduce(add, 0);
        var sub_title ;
        var myoptions = <?= $chart_options ?>;
        var mychart = new Highcharts.Chart('<?= $id ?>', Highcharts.merge(myoptions, {
            subtitle: {
                text: sub_title,
                style: {
                    color: '<?= $colors['color_chart_text_subtitle'] ?>',
                },
            },
			chart: {    type: 'column', stacking: 'normal'
  			},
			plotOptions: {
			series: {
    		states: { hover: { enabled: false,lineWidth: 0,},
              inactive: {	opacity: 1 }
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
						sortFunction = function (a, b) { return b[1] - a[1] };
					
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
    				style: { color: '<?= $colors['color_chart_labels_xaxis1'] ?>'},
                    },
  					},
            yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function () {
                        return this.value + 'kWh';
                    },
                    style: {
                        color: '<?= $colors['color_chart_labels_yaxis1'] ?>',
                    },
                },
                title: {
                    text: 'Total',
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
        		var id = this.point.x+1;
        		/* console.log(this); */
        		Totalen = 0
      			chart.series.forEach(function(series,i) {
        		series.points.forEach(function(point) {
          		if (point.category === x && stackName === point.series.userOptions.stack) {
            	contribuants += '<span style="color:'+ point.series.color +'">\u25CF</span>' + point.series.name + ': ' + Highcharts.numberFormat(point.y, '2', ',') + ' kWh' + ' = ' + Highcharts.numberFormat(point.y/(0.001*khhWp[i]), '2', ',') + ' Wh/Wp<br/>',
          		Totalen += point.y
          			}
       			 })
      			})
				//console.log(this.point);
				if (stackName === undefined) {stackName = '';}
      			return '<b>'+ id +'.</b>&emsp;  &emsp;&emsp;&emsp;&emsp;&emsp; &emsp; '+ x +' ' + stackName + '<br/>' + contribuants + 'Total: ' + Highcharts.numberFormat(Totalen, '2', ',') +  ' kWh' + ' = ' + Highcharts.numberFormat(Totalen/(0.001*kwptot), '2', ',') + ' Wh/Wp';
                    }
            },
            series: [ <?= $meta ?>]
        }));
		setInterval(function() { $("#<?= $id ?>").highcharts().reflow();  }, 500);
    });
</script>
