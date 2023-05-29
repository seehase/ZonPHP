<?php
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}
//echo $_SESSION['theme'];
$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
$inverter = $sNaamSaveDatabase[0];
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
$cur_year_month = "" . date('Y-m', $chartdate);
$paramnw['jaar'] = date("Y", $chartdate);

$sql = "SELECT MAX( Datum_Maand ) AS maxi, YEAR(Datum_Maand) as year, ROUND(SUM( Geg_Maand ),0) AS som, naam
FROM " . $table_prefix . "_maand 
GROUP BY naam, DATE_FORMAT( Datum_Maand,  '%y-%m' ) 
ORDER BY maxi, naam ASC";
//echo $sql;
$aTotaaljaar = array();
$result = mysqli_query($con, $sql) or die("Query failed. alle_jaren: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $adatum[][] = 0;
    $aTotaaljaar[] = 0;
    $acdatum = array();
} else {
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
FROM " . $table_prefix . "_maand
GROUP BY Naam, MONTH( Datum_Maand ) 
ORDER BY naam ASC ";
$result = mysqli_query($con, $sqlavg) or die("Query failed (gemiddelde) " . mysqli_error($con));
while ($row = mysqli_fetch_array($result)) {
    $avg_data[$row['Naam']][$row['Maand']] = $row['AVG'];
}

//	new reference
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
        $x = $row['Naam'];
        if (!isset($nfreftot[$x])) {
            $nfreftot[$x] = 0;
        }
        $nfreftot[$x] += $row['sum_geg_refer'];
        $nfrefdagmaand[date("n", strtotime($row['Datum_Refer']))][$row['Naam']] = $row['sum_dag_refer'];

    }
    $iyasaanpassen = (round(0.5 + max($frefmaand) / 50) * 50);
}

$nfreftot = array_values($nfreftot);

//max for all inverters
$sqlmax = "SELECT maand,jaar,som, Name FROM 
(SELECT naam as Name, month(Datum_Maand) AS maand,year(Datum_Maand) AS jaar, sum(Geg_Maand) AS som FROM " . $table_prefix . "_maand GROUP BY naam, maand,jaar ) AS somquery JOIN (SELECT maand as tmaand, max( som ) AS maxgeg FROM ( SELECT naam, maand, jaar, som FROM ( SELECT naam, month( Datum_Maand ) AS maand, year( Datum_Maand ) AS jaar, sum( Geg_Maand ) AS som FROM " . $table_prefix . "_maand GROUP BY naam, maand, jaar ) AS somqjoin ) AS maxqjoin GROUP BY naam,tmaand )AS maandelijks ON (somquery.maand= maandelijks.tmaand AND maandelijks.maxgeg = somquery.som) ORDER BY Name, maand";
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
        $nmaxmaand[$row['maand']][$row['Name']] = Round($row['som'], 0);
        $nmaxmaand_jaar[$row['maand']][$row['Name']] = $row['jaar'];
    }
}
?>
<?php
// ----------------------------------------------------------------------------
$strxas = "";
$tellerkleuren = 0;
$href = HTML_PATH."/pages/month_overview.php?maand=";
$sum_per_month = array();
$cnt_per_month = array();
$frefjaar = 0;
for ($i = 1; $i <= 12; $i++) {
    $sum_per_month[$i] = 0.0;
    $cnt_per_month[$i] = 0;
    $frefjaar = $frefjaar + $frefmaand[$i];
}
$frefjaar = $frefjaar / 12;
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

foreach ($sNaamSaveDatabase as $zz => $inverter_name) {
    if ($zz == 0) {
        $dash = "dashStyle: 'solid',";
    } elseif ($zz > 0) {
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

    $max_bars .= "{name: '$inverter_name max', type: 'column',  zIndex: -1, linkedTo: '$inverter_name',  grouping: false, pointPlacement: 0.048, stacking: 'normal', color: \"#" . $colors['color_chart_max_bar'] . "\" ,data: [";

    $reflines .= "{ name: '$inverter_name ref', type:'line', $dash linkedTo: '$inverter_name', pointPlacement: 0.048, color: '#" . $colors['color_chart_reference_line'] . "',
         stacking: 'normal', stack: 'ref', data: [";
    $avglines .= "{ name: '$inverter_name avg', type:'line', $dash linkedTo: '$inverter_name', pointPlacement: 0.048, color: '#" . $colors['color_chart_average_line'] . "',
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

    foreach ($bdatum as $asx => $asy) {

        if ($asx <= $paramnw['jaar'] && $asx >= ($param['jaar'])) {
            //echo ($asx);echo '<BR>';
            $mydata = "";
            $current_bars = "";
            $my_year = 0;
            for ($i = 1; $i <= 12; $i++) {

                if (array_key_exists($i, $asy)) {
                    $cur_year = $asy[$i];
                    $cur_max = $maxmaand[$i];
                } else {
                    if (array_key_exists($i, $maxmaand)) {
                        $cur_year = 0;
                        $cur_max = $maxmaand[$i];
                    } else {
                        $cur_year = 0;
                        $cur_max = 0;
                    }
                }

                if (!isset($nmaxmaand[$i][$inverter_name])) {
                    $nmaxmaand[$i][$inverter_name] = 0;
                }
                $val = round($nmaxmaand[$i][$inverter_name], 2);
                //@$my_year= $nmaxmaand_jaar[$i][$inverter_name];

                $max_bars .= "  { 
                          y:  $val, 
                          url: \"$href$my_year-$i-01\",
                          color: \"#" . $colors['color_chart_max_bar'] . "\"
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
                if (count($nfrefmaand) > 0) {
                    $z = $nfrefmaand[$i][$inverter_name];
                    $reflines .= "$z, $z, $z, null,";
                }
                $current_bars .= "
                    { x: $i-1,
                      y: $cur_year , 
                      
                      url: \"$href$asx-$i-01\",
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
$slinkdoorgeven = "/year_overviewt.php?jaar=";

$sub_title = "";
$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="all_years_chart_' . $inverter . '"></div>';
    $show_legende = "false";
}
//echo $max_bars;
include_once "chart_styles.php";
$categories = $shortmonthcategories;
?>
<script type="text/javascript">

    $(function () {
        var khhWp = [<?php echo $param['ieffectief_kwpiekst'] ?>];
        var first = <?php echo $firstYear ?>;
        var nmbr = khhWp.length //misused to get the inverter count
        var sub_title = '<?php echo $sub_title ?>';
        var myoptions = <?php echo $chart_options ?>;
        var mychart = new Highcharts.Chart('all_years_chart_<?php echo $inverter ?>', Highcharts.merge(myoptions, {

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
                },
            },

            xAxis: [{
                labels: {
                    step: 1,
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                        fontSize: '0.7em'
                    },
                },

                min: 0,
                max: 11,
                categories: [<?php echo $categories ?>],
            }],
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
                        color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>',
                    },
                },
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
            }],
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
                <?php echo $dummy; ?>
                <?php echo $reflines; ?>
                <?php echo $avglines; ?>
                <?php echo $dummyyears; ?>
                <?php echo $max_bars; ?>
                <?php echo $value_series ?>
            ]
        }));
        setInterval(function () {
            $("#all_years_chart_<?php echo $inverter ?>").highcharts().reflow();
        }, 500);

    });
</script>
