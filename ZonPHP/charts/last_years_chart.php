<?php
global $con, $params, $colors, $shortmonthcategories, $chart_options;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
$inverter = PLANT_NAMES[0];
if (isset($_GET['naam'])) {
    $inverter = $_GET['naam'];
}

$chartdate = time();
$chartdatestring = date("Y-m-d", $chartdate);

if (isset($_GET['jaar'])) {
    $chartdatestring = html_entity_decode($_GET['jaar']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = date("Y-m-d", $chartdate);
}
$cur_year_month = date('Y-m', $chartdate);
$paramnw['jaar'] = date("Y", $chartdate);

$sql = "SELECT MAX( Datum_Maand ) AS maxi, YEAR(Datum_Maand) as year, ROUND(SUM( Geg_Maand ),0) AS som, naam
FROM " . TABLE_PREFIX . "_maand 
GROUP BY  naam, DATE_FORMAT( Datum_Maand,  '%y-%m' ), year
ORDER BY maxi, naam ASC";
//echo $sql;
$aTotaaljaar = array();
$result = mysqli_query($con, $sql) or die("Query failed. alle_jaren: " . mysqli_error($con));
$adatum[][] = 0;
$aTotaaljaar[] = 0;
$acdatum = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $adatum[date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = $row['som'];
        $acdatum[] = $row['year'];
        if (!isset($abdatum[$row['naam']][date("Y", strtotime($row['maxi']))])) $abdatum[$row['naam']][date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = 0;

        $abdatum[$row['naam']][date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = $row['som'];
        //$aTotaaljaar[date("Y", strtotime($row['maxi']))] += $row['som'];
        if (!isset($abTotaaljaar[$row['naam']][date("Y", strtotime($row['maxi']))])) $abTotaaljaar[$row['naam']][date("Y", strtotime($row['maxi']))] = 0;
        $abTotaaljaar[$row['naam']][date("Y", strtotime($row['maxi']))] += $row['som'];
    }
}
$acdatum = array_values(array_unique($acdatum));
if (count($acdatum) > 0) {
    $firstYear = reset($acdatum);
} else {
    $firstYear = 1970;
}


//  new average
$sqlavg = "SELECT Naam, MONTH( Datum_Maand ) AS Maand, ROUND( SUM( Geg_Maand ) / COUNT( DISTINCT (
YEAR( Datum_Maand ) ) ) , 0
) AS AVG
FROM " . TABLE_PREFIX . "_maand
GROUP BY Naam, MONTH( Datum_Maand ) 
ORDER BY naam ASC ";
$result = mysqli_query($con, $sqlavg) or die("Query failed (gemiddelde) " . mysqli_error($con));
while ($row = mysqli_fetch_array($result)) {
    $avg_data[$row['Naam']][$row['Maand']] = $row['AVG'];
}

//	new reference
$totalExpectedMonth = $params['totalExpectedMonth'];
//max for all inverters
$sqlmax = "SELECT maand,jaar,som, Name FROM 
(SELECT naam as Name, month(Datum_Maand) AS maand,year(Datum_Maand) AS jaar, sum(Geg_Maand) AS som FROM " . TABLE_PREFIX . "_maand GROUP BY naam, maand,jaar ) AS somquery JOIN (SELECT maand as tmaand, max( som ) AS maxgeg FROM ( SELECT naam, maand, jaar, som FROM ( SELECT naam, month( Datum_Maand ) AS maand, year( Datum_Maand ) AS jaar, sum( Geg_Maand ) AS som FROM " . TABLE_PREFIX . "_maand GROUP BY naam, maand, jaar ) AS somqjoin ) AS maxqjoin GROUP BY naam,tmaand )AS maandelijks ON (somquery.maand= maandelijks.tmaand AND maandelijks.maxgeg = somquery.som) ORDER BY Name, maand";
$resultmax = mysqli_query($con, $sqlmax) or die("Query failed. ERROR: " . mysqli_error($con));

for ($i = 1; $i <= 12; $i++) {
    $maxmaand[$i] = 0;
}

if (mysqli_num_rows($resultmax) == 0) {
    $maxmaand[] = 0;
    $nmaxmaand[][] = 0;
} else {
    while ($row = mysqli_fetch_array($resultmax)) {
        $maxmaand[$row['maand']] = $row['som'];
        $nmaxmaand[$row['maand']][$row['Name']] = Round($row['som']);
        $nmaxmaand_jaar[$row['maand']][$row['Name']] = $row['jaar'];
    }
}
?>
<?php
// ----------------------------------------------------------------------------
$strxas = "";
$tellerkleuren = 0;
$href = HTML_PATH . "pages/month_overview.php?date=";
$sum_per_month = array();
$cnt_per_month = array();

for ($i = 1; $i <= 12; $i++) {
    $sum_per_month[$i] = 0.0;
    $cnt_per_month[$i] = 0;
}

$my_year = date("Y", $chartdate);
$dummy = "";
$max_bars = "";
$reflines = "";
$avglines = "";
$value_series = "";
$zz = 0;
$colorzz = 0;
$year = $firstYear - 1;
$i = 0;
$colori = 1;
$newLine = '';
$dummyyears = '';
$dyear = '';
$dummyyears .= "{name: $firstYear , newLine: 'true', type: 'column', grouping: false, id: 'year', zIndex: -1,color: '" . $colors['color_palettes'][$colori - 1][2] . "',  data:[] },";
$isFirst = true;
foreach ($acdatum as $i => $dyear) {
    if ($isFirst) {
        $isFirst = false;
        continue;
    }
    $dummyyears .= "{name: $dyear ,type: 'column', grouping: false, id: 'year', zIndex: -1, color: '" . $colors['color_palettes'][$colori][2] . "', data:[] },";
    if ($colori == 4) {
        $colori = 0;
    } else $colori++;
}

foreach (PLANT_NAMES as $zz => $inverter_name) {
    if ($zz == 0) {
        $dash = "dashStyle: 'solid',";
    } else {
        $dash = "dashStyle: 'shortdash',";
    }
    $link = 0;
    $cnt = 0;
    $colorcnt = 0;
    $bdatum = array();
    if (isset($abdatum[$inverter_name])) {
        $bdatum = $abdatum[$inverter_name];
    }

    $dummy .= "{name: '$inverter_name', id: '$inverter_name dummy',type: 'column',  zIndex: -1, stacking: 'normal', color: '" . $colors['color_palettes'][5][$colorzz] . "',data: [";

    $max_bars .= "{name: '$inverter_name max', type: 'column',  zIndex: -1, linkedTo: '$inverter_name',  grouping: false, pointPlacement: 0.048, stacking: 'normal', color: \"" . $colors['color_chart_max_bar'] . "\" ,data: [";

    $reflines .= "{ name: '$inverter_name ref', type:'line', $dash linkedTo: '$inverter_name', pointPlacement: 0.048, color: '" . $colors['color_chart_reference_line'] . "',
         stacking: 'normal', stack: 'ref', data: [";
    $avglines .= "{ name: '$inverter_name avg', type:'line', $dash linkedTo: '$inverter_name', pointPlacement: 0.048, color: '" . $colors['color_chart_average_line'] . "',
         stacking: 'normal', stack: 'avg', data: [";

// empty series to overcome a LinkedTo bug in Highcharts
    $value_series .= "{ name: '$inverter_name',
                    id: '$inverter_name',
                    linkedTo: '$inverter_name dummy', 
                    type: 'column',
                    stack: $year,
                    stacking: 'normal',
                    data: []
            		},";

    $firstYear = date("Y", strtotime(STARTDATE));
    foreach ($bdatum as $asx => $asy) {

        if ($asx <= $paramnw['jaar'] && $asx >= ($firstYear)) {
            //echo ($asx);echo '<BR>';
            $mydata = "";
            $current_bars = "";
            $my_year = 0;
            for ($i = 1; $i <= 12; $i++) {

                if (array_key_exists($i, $asy)) {
                    $cur_year = $asy[$i];
                    $cur_max = $maxmaand[$i];
                } elseif (array_key_exists($i, $maxmaand)) {
                    $cur_year = 0;
                    $cur_max = $maxmaand[$i];
                } else {
                    $cur_year = 0;
                    $cur_max = 0;
                }

                if (!isset($nmaxmaand[$i][$inverter_name])) {
                    $nmaxmaand[$i][$inverter_name] = 0;
                }
                $val = round($nmaxmaand[$i][$inverter_name], 2);
                //@$my_year= $nmaxmaand_jaar[$i][$inverter_name];
                $formattedHref = sprintf("%s%04d-%02d-%02d", $href, $my_year, $i, 1);
                $max_bars .= "  { 
                          y:  $val, 
                          url: \"$formattedHref\",
                          color: \"" . $colors['color_chart_max_bar'] . "\"
                        },";
                $aclickxas[$tellerkleuren][] = $asx . '-' . $i . '-1';
                $mydata .= '["' . $i . '", ' . $cur_year . '], ';

                $sum_per_month[$i] = $sum_per_month[$i] + $cur_year;
                if ($cur_year > 0.0) {
                    $cnt_per_month[$i]++;
                }

                if (!isset($avg_data[$inverter_name][$i])) {
                    $avg_data[$inverter_name][$i] = 0;
                }
                $av = $avg_data[$inverter_name][$i];

                $avglines .= "$av, $av, $av, null,";
                //refline
                if (count($totalExpectedMonth) > 0) {
                    $z = $totalExpectedMonth[$i][$inverter_name];
                    $reflines .= "$z, $z, $z, null,";
                }
                $formattedHref = sprintf("%s%04d-%02d-%02d", $href, $asx, $i, 1);
                $current_bars .= "
                    { x: $i-1,
                      y: $cur_year , 
                      
                      url: \"$formattedHref\",
                      color: '" . $colors['color_palettes'][$colorcnt][$colorzz] . "', 
                    },";
                $tellerkleuren++;

            }
            $value_series .= "
            {
                    name: '$inverter_name',
                    id: '$inverter_name',
                    linkedTo: '$inverter_name dummy',
                    color: '" . $colors['color_palettes'][5][$colorzz] . "', 
                    type: 'column',
                    stack: $asx,
                    stacking: 'normal',
                    data: [$current_bars]
            },
            ";
            //echo $colorcnt;
            $cnt++;
            if ($colorcnt == 4) {
                $colorcnt = 0;
            } else $colorcnt++;
        }
    }
    $dummy .= "]},";
    $max_bars .= "]},";
    $reflines .= "]},";
    $avglines .= "]},";
//echo $colorzz;
    if ($colorzz == 3) {
        $colorzz = 0;
    } else $colorzz++;
}

$strxas = substr($strxas, 0, -1);
$slinkdoorgeven = "/year_overviewt.php?date=";

$sub_title = "";
$show_legende = "true";
if ($isIndexPage) {
    echo '<div class = "index_chart" id="all_years_chart_' . $inverter . '"></div>';
    $show_legende = "false";
}
//echo $max_bars;
include_once "chart_styles.php";
$categories = $shortmonthcategories;
?>
<script>
    (function (H) {
        H.wrap(H.Legend.prototype, 'layoutItem', function (proceed, item) {
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

    $(function () {
        var khhWp = <?= json_encode($params['PLANTS_KWP']) ?>;
        var first = <?= $firstYear ?>;
        var nmbr = khhWp.length //misused to get the inverter count
        var sub_title = '<?= $sub_title ?>';
        var myoptions = <?= $chart_options ?>;
        var mychart = new Highcharts.Chart('all_years_chart_<?= $inverter ?>', Highcharts.merge(myoptions, {

            plotOptions: {
                line: {
                    lineWidth: 1,
                    zIndex: 1,
                    pointStart: -0.25,
                    pointInterval: 0.25
                },
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
                column: {
                    stacking: 'normal',
                    grouping: true,

                    events: {

                        legendItemClick: function (e) {

                            let chart = this.chart;
                            let ThisClick = this.userOptions.id;
                            var NamesYearsBefore = [];
                            var NamesYearsAfter = [];
                            var clickedSeries = this,

                                lineSeries = clickedSeries.chart.series.filter(series => series.type === 'line' && series.name.includes('ref')),
                                visibleLineSeries = [];//console.log(clickedSeries.index)
                            lineSeries2 = clickedSeries.chart.series.filter(series => series.type === 'line' && series.name.includes('avg')),
                                visibleLineSeries2 = [];
                            //console.log (lineSeries)
                            //serie 1
                            lineSeries.forEach(function (series) {

                                // Set all series to "dot"
                                if (series.options.dashStyle === 'solid') {
                                    //console.log('case solid',series.index)
                                    series.update({
                                        dashStyle: 'shortdash'
                                    })
                                }
                                // Push all visible series to an array except the one that was clicked
                                if (series.visible && series.index !== clickedSeries.index + nmbr) {
                                    visibleLineSeries.push(series)
                                }//console.log ('case a',  series.index  )
                                if (!series.visible && series.index === clickedSeries.index + nmbr) {
                                    visibleLineSeries.push(series)
                                }//console.log ('case b', clickedSeries.index)
                            })


                            //serie 2
                            lineSeries2.forEach(function (series) {
                                // Set all series to "dot"
                                if (series.options.dashStyle === 'solid') {
                                    series.update({
                                        dashStyle: 'shortdash'
                                    })
                                }
                                // Push all visible series to an array except the one that was clicked
                                if (series.visible && series.index !== clickedSeries.index + nmbr * 2) {
                                    visibleLineSeries2.push(series)
                                }//console.log ('case c', series.index  )
                                if (!series.visible && series.index === clickedSeries.index + nmbr * 2) {
                                    visibleLineSeries2.push(series)
                                }//console.log ('case d', series.index)
                            })

                            // Set first visible series to "solid"

                            if (visibleLineSeries.length) {
                                //console.log(visibleLineSeries);
                                visibleLineSeries[0].update({
                                    dashStyle: 'solid'
                                })

                            }
                            if (visibleLineSeries2.length) {
                                visibleLineSeries2[0].update({
                                    dashStyle: 'solid'
                                })
                            }

                            chart.series.forEach(s => {
                                console.log(s.index, s.name)
                                if (s.userOptions.stack && s.visible && s.type === 'column') {

                                    NamesYearsBefore.push(s.name, s.userOptions.stack);
                                } else if (s.userOptions.stack && s.type === 'column') {

                                    NamesYearsAfter.push(s.name, s.userOptions.stack);
                                }
                            })
                            NamesYearsBefore = [...new Set(NamesYearsBefore)]
                            NamesYearsAfter = [...new Set(NamesYearsAfter)]

                            if (NamesYearsBefore.length == 0 && ThisClick.includes('dummy')) {
                                NamesYearsBefore = NamesYearsAfter; //console.log ('k')
                            } else if (NamesYearsBefore.length == 0 && ThisClick.includes('year')) {
                                NamesYearsBefore = NamesYearsAfter; //console.log ('m')
                            }

                            chart.series.forEach(s => {

                                if (this.name == s.userOptions.stack && s.visible && NamesYearsBefore.includes(s.name)) {
                                    if (this.name == 2000) {
                                    } else {

                                        s.hide();
                                        this.legendItem.symbol.element.style.fill = '#cccccc'
                                    }
                                } else if (this.name == s.userOptions.stack && !s.visible && NamesYearsBefore.includes(s.name)) {
                                    this.legendItem.symbol.element.style.fill = this.color
                                    s.show();
                                } else if (this.name == s.userOptions.id && NamesYearsBefore.includes(s.userOptions.stack) && s.visible) {
                                    this.legendItem.symbol.element.style.fill = '#cccccc'

                                    s.hide();
                                } else if (this.name == s.userOptions.id && NamesYearsBefore.includes(s.userOptions.stack) && !s.visible) {
                                    this.legendItem.symbol.element.style.fill = this.color

                                    s.show();
                                }
                            })

                            e.preventDefault()
                        }
                    },
                },
            },

            xAxis: [{

                labels: {

                    step: 1,
                    style: {
                        color: '<?= $colors['color_chart_labels_xaxis1'] ?>',
                    },
                },

                min: 0,
                max: 11,
                categories: [<?= $categories ?>],
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    formatter: function () {
                        return this.value
                    },
                    style: {
                        color: '<?= $colors['color_chart_labels_yaxis1'] ?>',
                    },
                },
                opposite: true,
                title: {
                    text: 'Total (kWh)',
                    style: {
                        color: '<?= $colors['color_chart_title_yaxis1'] ?>',
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
            tooltip: {
                formatter: function () {
                    var chart = this.series.chart,
                        x = this.x,

                        stackName = this.series.userOptions.stack,
                        contribuants = '';
                    //console.log(x);

                    chart.series.forEach(function (series) {
                        series.points.forEach(function (point) {
                            if (point.category === x && stackName === point.series.userOptions.stack && point.series.visible) {
                                contribuants += '<span style="color:' + point.series.color + '">\u25CF</span>' + point.series.name + ': ' + point.y + ' kWh' + '<br/>'
                            }
                        })
                    })
                    if (stackName === undefined) {
                        stackName = '';
                    }
                    //if (x.match[0-9]){x = 'test';}
                    return '<b>' + x + ' ' + stackName + '<br/>' + '<br/>' + contribuants + 'Total: ' + this.point.stackTotal + ' kWh';
                }
            },

            series: [
                <?= $dummy .
                $reflines .
                $avglines .
                $dummyyears .
                $max_bars .
                $value_series ?>
            ]
        }));
        setInterval(function () {
            $("#all_years_chart_<?= $inverter ?>").highcharts().reflow();
        }, 500);

    });
</script>
