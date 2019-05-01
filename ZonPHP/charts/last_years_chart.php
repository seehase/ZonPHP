<?php
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "Parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$chartdate = time();
$chartdatestring = strftime("%Y-%m-%d", $chartdate);

if (isset($_GET['jaar'])) {
    $chartdatestring = html_entity_decode($_GET['jaar']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = strftime("%Y-%m-%d", $chartdate);
}
$cur_year_month = "" . date('Y-m', $chartdate);
$color_yearchart = array($colors['color_yearchart0'], $colors['color_yearchart1'], $colors['color_yearchart2'], $colors['color_yearchart3'], $colors['color_yearchart4']);

// ---------------------------------------------------------------------------------------------------------------------

$param['jaar'] = date("Y", $chartdate);
$sql = "SELECT MAX(Datum_Maand) AS maxi,SUM(Geg_Maand) AS som
	FROM " . $table_prefix . "_maand
	WHERE Naam='" . $inverter . "'
	GROUP BY DATE_FORMAT(Datum_Maand,'%y-%m')
	ORDER BY 1 asc";

$aTotaaljaar = array();
$result = mysqli_query($con, $sql) or die("Query failed. alle_jaren: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $adatum[][] = 0;
    $aTotaaljaar[] = 0;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $adatum[date("Y", strtotime($row['maxi']))][date("n", strtotime($row['maxi']))] = $row['som'];
        if (!isset($aTotaaljaar[date("Y", strtotime($row['maxi']))])) $aTotaaljaar[date("Y", strtotime($row['maxi']))] = 0;
        $aTotaaljaar[date("Y", strtotime($row['maxi']))] += $row['som'];
    }
}

$average = array_sum($aTotaaljaar) / count($aTotaaljaar) / 12;

$sqlref = "SELECT Geg_Refer,Datum_Refer
		FROM " . $table_prefix . "_refer
		WHERE Naam='" . $inverter . "'";
$resultref = mysqli_query($con, $sqlref) or die("Query failed. alle_jaren-ref: " . mysqli_error($con));
if (mysqli_num_rows($resultref) == 0) {
    $frefmaand = array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
} else {
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand[date("n", strtotime($row['Datum_Refer']))] = $row['Geg_Refer'];
    }
}

$sqlmax = "SELECT MAX( som ) AS maxsom, DATE_FORMAT( maxi, '%c' ) AS maand
		FROM (
			SELECT MAX( Datum_Maand ) AS maxi, SUM( Geg_Maand ) AS som
			FROM " . $table_prefix . "_maand
			WHERE Naam = '" . $inverter . "'
			GROUP BY DATE_FORMAT( Datum_Maand, '%y-%c' )
			) AS allemax
		GROUP BY maand ";
$resultmax = mysqli_query($con, $sqlmax) or die("Query failed. ERROR: " . mysqli_error($con));


for ($i = 1; $i <= 12; $i++) {
    $maxmaand[$i] = 0;
}

if (mysqli_num_rows($resultmax) == 0) {
    $maxmaand[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultmax)) {
        $maxmaand[$row['maand']] = $row['maxsom'];
    }
}


?>


<?php
// ----------------------------------------------------------------------------
$strgeg = "";
$strxas = "";
$strgemiddelde = "";
$strref = "";
$strmax = "";
$tellerkleuren = 0;
$strbar3d = "";
$max = 0;

$on_click = "allejaar";
$str_data = "";

$str_ticks = "";
$href = "month_overview.php?maand=";
$max_per_month = "";
$tip_max_month = "";
$tip_ref_month = "";
$sum_per_month = array();
$cnt_per_month = array();

$avg_per_month = "";
$gridlines = "";

$frefjaar = 0;
for ($i = 1; $i <= 12; $i++) {
    $sum_per_month[$i] = 0.0;
    $cnt_per_month[$i] = 0;
    $frefjaar = $frefjaar + $frefmaand[$i];
}
$frefjaar = $frefjaar / 12;


$max_bars = "";
$categories = "";
// grey max data chart
for ($i = 1; $i <= 12; $i++) {
    $categories .= '"' . ($months[$i])  . '",';
    $max_bars .= "
                    { 
                      y: $maxmaand[$i] ,                       
                      color: '#" . $colors['color_chart_max_bar']. "',
                    },";
}
$categories = substr($categories, 0, -1);


$cnt = 0;
$fsomeuro = 0;
$value_series = "";
foreach ($adatum as $asx => $asy) {

    if ($asx <= $param['jaar'] && $asx > ($param['jaar'] - 5)) {



        $mydata = "";
        $current_bars = "";
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

            $aclickxas[$tellerkleuren][] = $asx . '-' . $i . '-1';
            $mydata .= '["' . $i . '", ' . $cur_year . '], ';

            $sum_per_month[$i] = $sum_per_month[$i] + $cur_year;
            if ($cur_year > 0.0) {
                $cnt_per_month[$i]++;
            }

            $current_bars .= "
                    { x: $i-1,
                      y: $cur_year , 
                      url: \"$href$asx-$i-01\",
                      color: '#$color_yearchart[$cnt]', 
                    },";

            $tellerkleuren++;
            $strgeg = "";

        }
        $value_series .= "
            {
                    name: '$asx',
                    color: '#$color_yearchart[$cnt]',
                    type: 'column',
                    data: [$current_bars],
            },
            ";
        $cnt++;
    }
}


// ref lines per month
$i = 0;

$lines_per_month = "";
for ($i = 1; $i <= 12; $i++) {
    if ($cnt_per_month[$i] == 0) {
        $cnt_per_month[$i] = 1;
    }
    $cursum = ($sum_per_month[$i] / $cnt_per_month[$i]);
    $cursum = number_format($cursum, 0, ',', '.');
    $avg_per_month .= "[$cursum],";


    $gridlines .= '{xaxis: {from:  ' . ($i - 1 + 0.5) . ', to: ' . ($i - 1 + 1.5) . '}, yaxis: {from: ' . $cursum . ', to: ' . $cursum . '}, color: "#34F02F", lineWidth: 1.5},';

    // average lines
    $lines_per_month .= "{ name: 'ref_avg[$i]', type: 'line', color: '#" . $colors['color_chart_average_line']. "', data: [{ x: ($i -1.3), y: $cursum}, { x: ($i - 0.7), y: $cursum }], showInLegend: false},";
    // reflines
    $lines_per_month .= "{ name: 'ref_avg[$i]', type: 'line', color: '#" . $colors['color_chart_reference_line']. "', data: [{ x: ($i -1.3), y: $frefmaand[$i]}, { x: ($i - 0.7), y: $frefmaand[$i] }], showInLegend: false},";
}

// ----------------------------------------------------------------------------

$strxas = substr($strxas, 0, -1);
$slinkdoorgeven = "/year_overviewt.php?jaar=";

$sub_title = "";
$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="all_years_chart_' . $inverter . '"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {

        var avg = <?php echo $average ?>;
        var ref = <?php echo $frefjaar ?>;
        var sub_title = '<?php echo $sub_title ?>';
        var myoptions = <?php echo $chart_options ?>;

        var mychart = new Highcharts.Chart('all_years_chart_<?php echo $inverter ?>', Highcharts.merge(myoptions, {

            subtitle: {
                text: sub_title,
                style: {
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
                },
            },
            plotOptions: {
                column: {
                    grouping: true,
                },
            },
            xAxis: [{
                labels: {
                    rotation: 270,
                    step: 1,
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
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
                    if (this.series.name == 'Maximum' || this.series.name == 'Reference' || this.series.name == 'Average') {
                        return this.series.name + ' ' + this.y.toFixed(0) + 'kWh';
                    }
                    else {
                        if (this.series.name.indexOf("ref_") == 0)
                        {
                            return 'Ref ' + this.y.toFixed(0) + 'kWh';
                        }
                        else
                        {
                            return this.x + ' ' + this.series.name + ' -> ' + this.y.toFixed(0) + 'kWh';
                        }
                    }
                }
            },
            series: [
                {
                    name: "Maximum",
                    type: "column",
                    grouping: false,
                    color: '#<?php echo $colors['color_chart_max_bar'] ?>',
                    data: [<?php echo $max_bars; ?>],
                },
                <?php echo $value_series ?>
                {
                    name: 'Reference',
                    type: 'line',
                    color: '#<?php echo $colors['color_chart_reference_line'] ?>',
                    data: [
                        {x: -0.6, y: ref},
                        {x: 11.4, y: ref}
                    ]
                }, {
                    name: 'Average',
                    type: 'line',
                    color: '#<?php echo $colors['color_chart_average_line'] ?>',
                    data: [
                        {x: -0.6, y: avg},
                        {x: 11.4, y: avg}
                    ]
                },
                <?php echo $lines_per_month; ?>
            ]
        }));

        $("#all_years_chart_<?php echo $inverter ?>").resize(function () {
            mychart.reflow();
        });
    });


</script>


