<?php
global $con, $shortmonthcategories, $chart_options, $colors, $chart_lang;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

$isIndexPage = false;
$showAllInverters = true;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage"))
{
    $isIndexPage = true;
}

$currentdate = date("Y-m-d");
$inClause = "'" . implode("', '", PLANT_NAMES) . "'";
$sql = "SELECT date(`Datum_Maand`) as Date,`Geg_Maand` as Yield, YEAR(`Datum_Maand`) as Year, `Naam` as Name 
        FROM `". TABLE_PREFIX . "_maand`  
        WHERE naam in ($inClause) 
        ORDER BY `Datum_Maand`,`Naam`";

//WHERE YEAR(`Datum_Maand`) IN (2023, 2024)
//make array with values from query
$result = mysqli_query($con, $sql) or die("Query failed. maand " . mysqli_error($con));
$querydata = array();
$totaldata = array();
$names = array();
$years = array();
$array = array();
if ($result->num_rows > 0)
{
    while ($row = $result->fetch_assoc())
    {
        //echo $row['Date'],' ',$row['Name'],'  ',$row['Yield'],' ',$row['Year'],' <BR>';
        $querydata[$row['Date']][$row['Name']] = $row['Yield'];
        $names[] = $row["Name"];
        $years[] = date("Y", strtotime($row["Date"]));
    }
}

$names = array_values(array_unique($names));
$years = array_values(array_unique($years));

//make array with all dates and inverter names from start to end
//this will fill the gaps when no data available
$startDate = $years[0] . '-01-01';
$endDate = array_key_last($querydata);
$period = new DatePeriod(new DateTime($startDate) , new DateInterval('P1D') , new DateTime($endDate));
foreach ($period as $key => $value)
{
    foreach ($names as $name)
    {
        $read = $value->format('Y-m-d');
        $year = $value->format('Y');
        $yield = 0;
        if (isset($querydata[$read][$name])) {
            $yield = $querydata[$read][$name];
        }
        $totaldata[$read][$name] = $yield;
    }
}

//sort array on date
ksort($totaldata);

//flip array -> data ordered by inverter name
$mistral = [];
foreach ($totaldata as $outerkey => $outerArr)
{
    foreach ($outerArr as $key => $innerArr)
    {
        $mistral[$key][$outerkey] = $innerArr;
    }
}

//$total = array();
//running total per inverter array
$runningSum = 0;
for ($i = 0;$i < count($years);$i++)
{
    //$keys=0;
    foreach ($mistral as $keys => $sums)
    {
        $runningSum = 0;
        foreach ($sums as $key => $number)
        {
            $yearkey = substr($key, 0, 4);
            if ($years[$i] == $yearkey)
            {
                //echo $yearkey,' nb ',$number,' rs ',$runningSum,' array ',$keys, '<BR>';
                $runningSum += $number;
                $total[$keys][$key] = $runningSum;
            }
            $cumulus = $total;
        }
    }
}

//reverse flip -> data ordered on date
$foehn = [];
foreach ($total as $outerkey1 => $outerArr1)
{
    foreach ($outerArr1 as $key1 => $innerArr1)
    {
        $foehn[$key1][$outerkey1] = $innerArr1;
    }
}

$value = array();
$strdataseries = "";
$strdata = "";
$mouseover = "";
for ($i = 0;$i < count($years);$i++)
{
    $strdata = "";
    foreach ($foehn as $allsum => $value)
    {
        $yearkey = substr($allsum, 0, 4);
        foreach ($names as $name => $val)
        {
            if ($yearkey == $years[($i) ])
            {
                if (isset($value[$val])) {
                    $strdata .= "{  y: $value[$val], inverter: '$val' },";
                }
            }
        }
    }
    $strdata = substr($strdata, 0, -1);
    $strdataseries .= " year" . $years[($i) ] . ": [" . $strdata . "],";
}
$myColors = colorsPerInverter();
$strdataseries = substr($strdataseries, 0, -1);
$strseriestxt = "";
$strnametxt = "";
for ($i = 0;$i < count($years);$i++)
{
    $strseriestxt .= "{id: 'year" . $years[($i) ] . "', name: '" . $years[($i) ] . "', data:[]},";
}

$i = 0;
foreach ($names as $name)
{
    $col1 = $myColors[$name]['min'];
    $col2 = $myColors[$name]['max'];
    $line = "";
    if ($i == 0) $line = 'newLine: true,';
    $i++;
    $strnametxt .= "{" . $line . " name: '" . $name . "', legendSymbol: 'rectangle', color: { linearGradient: {x1: 0, x2: 0, y1: 1, y2: 0}, stops: [ [0, $col1], [1, $col2]] }, id: '" . $name . "'},";
    $mouseover  .= "item.name==='" . $name . "'||";
}

$strtotaaltxt = $strseriestxt . $strnametxt;
$strtotaaltxt = substr($strtotaaltxt, 0, -1);
$mouseover = substr($mouseover,0,-2);
$show_legende = "true";
if ($isIndexPage)
{
    echo '<div class = "index_chart" id="universal"></div>';
    $show_legende = "false";
}
include_once "chart_styles.php";
$categories = $shortmonthcategories;
?>

<script>
$(function () {
var myoptions = <?= $chart_options ?>;
        Highcharts.setOptions({<?= $chart_lang ?>});

//function converts Day of Year to readable date for tooltip
function getDateFromDayOfYear (year, day) {
  const locale = '<?= $locale ?>';
  const options = {day: 'numeric' , month: 'long'};
  return new Date(Date.UTC(year, 0, day)).toLocaleDateString(locale, options)
}

//function(H) creates newLine option for legend
(function(H) {
  H.wrap(H.Legend.prototype, 'layoutItem', function(proceed, item) {
    const options = this.options,
      padding = this.padding,
      horizontal = options.layout === 'horizontal',
      itemHeight = item.itemHeight,
      itemMarginBottom = this.itemMarginBottom,
      itemMarginTop = this.itemMarginTop,
      itemDistance = horizontal ? H.pick(options.itemDistance, 20) : 0,
      maxLegendWidth = this.maxLegendWidth,
      itemWidth = (options.alignColumns &&
		this.totalItemWidth > maxLegendWidth) ?
		this.maxItemWidth :
      	item.itemWidth,
      legendItem = item.legendItem || {};

    if (
	  horizontal &&
      (
	    this.itemX - padding + itemWidth > maxLegendWidth ||
        item.userOptions.newLine
	  )
	) {
      this.itemX = padding;
      if (this.lastLineHeight) {
        this.itemY += (itemMarginTop +
          this.lastLineHeight +
          itemMarginBottom);
      }
      this.lastLineHeight = 0;
    }
    this.lastItemY = itemMarginTop + this.itemY + itemMarginBottom;
    this.lastLineHeight = Math.max(
      itemHeight, this.lastLineHeight);
	  legendItem.x = this.itemX;
	  legendItem.y = this.itemY;
    if (horizontal) {
      this.itemX += itemWidth;
    } else {
      this.itemY +=
        itemMarginTop + itemHeight + itemMarginBottom;
      this.lastLineHeight = itemHeight;
    }
    this.offsetWidth = this.widthOption || Math.max((horizontal ? this.itemX - padding - (item.checkbox ?
      0 :
      itemDistance) : itemWidth) + padding, this.offsetWidth);
  })
})(Highcharts);

const data = { <?= $strdataseries ?> }

const inverterFilters = []

const updatePoints = (chart, filters) => {
  chart.series.forEach(s => {
    if (s.points) {
      const id = s.options.id,
        seriesData = data[id];
      let wasFiltered = false;
      if (seriesData) {
        const dataToUpdate = [];
        seriesData.filter(singleData => {
          if (!filters.includes(singleData.inverter)) {
            dataToUpdate.push({...singleData, y: singleData.y || null})
          } else {
            wasFiltered = true
          }
        })
        const updatedData = []
		
        if (!wasFiltered) {
          let summedValues = null
          dataToUpdate.forEach((data, index) => {
            summedValues += data.y

            if (index % 2 === 1) {
              updatedData.push({
                y: data.y === null ? null : summedValues,
              })

              summedValues = null
            }
			
          })
        }
        s.update({
          data: wasFiltered ? dataToUpdate : updatedData
        }, false)
      }

    }
  })

  chart.redraw();
	
}

Highcharts.setOptions(<?= $chart_options ?>)

const chart = Highcharts.chart('universal', {
	accessibility: {
     	  enabled: false
  	 },
  
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
	gridLineColor: '<?= $colors['color_chart_gridline_yaxis1'] ?>',
	}],    
	title: {
    	style: {
            opacity: 0,
      		fontWeight: 'normal',
            fontSize: '12px'
   				}
  	},

    subtitle: {
                
        style: {
            color: '<?= $colors['color_chart_text_subtitle'] ?>',
                },
    },
  xAxis: [{
      id: "0",
      type: 'linear',
      lineWidth: 0,
      minorGridLineWidth: 0,
      lineColor: 'transparent',
      labels: {
        enabled: false
      },
      min: 0,
      max: 365,
      minorTickLength: 0,
      tickLength: 0
    },
    {
      id: '1',
      type: 'categories',
      labels: {
        rotation: 0,
        align: 'left',
        step: 1,
        style: {
        color: '<?= $colors['color_chart_labels_xaxis1'] ?>',
        },
      },
      min: -0.5,
      max: 11.5,
      categories: [<?= $categories ?> ],

    }
  ],
  tooltip: {
    
    valueSuffix: ' kWh',
    valueDecimals: 0,
    
    split: false,
    shared: true,
    formatter: function(tooltip) {
      const points = []
      
      this.points.forEach(point => {
        if (point.y !== 0) {
        	point.point.y = point.point.y
        	if (this.x > 364){
        	point.key = getDateFromDayOfYear(2019, (this.x))
        	}
        	else {
        	point.key = getDateFromDayOfYear(2019, (this.x +1))
        	}
          points.push(point)
        }
      })
      this.points = points;
     // this.key = 'test';
      return tooltip.defaultFormatter.call(this, tooltip);
    }
  },

  plotOptions: {
    series: {
      cumulative: true,
      markers: {
        enabled: false
      },

      events: {
        legendItemClick: function() {
          const series = this,
            inverterId = series.options.id;

          if (!series.visible) {
            const index = inverterFilters.indexOf(inverterId)

            inverterFilters.splice(index, 1);
          } else {
            inverterFilters.push(inverterId)
          }

          updatePoints(series.chart, inverterFilters)
        }
      }
    }
  },

  series: [  <?= $strtotaaltxt ?>  ]

});

  chart.legend.allItems.forEach(item=>{
    if(<?= $mouseover ?>){
      const group = item.legendItem.group;
        group.on('mouseover', function (){})
        .on('mouseout', function (){})
    }
  })

updatePoints(chart, [])
})        
    
</script>
