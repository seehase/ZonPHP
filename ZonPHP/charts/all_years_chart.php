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
// -----------------------------  get data from DB -----------------------------------------------------------------
$datum = "";

$current_year = date('Y', time());
if (isset($year_euro[$current_year])) {
    $current_euro = $year_euro[$current_year];
} else {
    $current_euro = 0.25;
}

$inveter_list = array();

// load sum per month for all years --------------------------------------------------------------------------------
$sql = "SELECT SUM( Geg_Maand ) AS sum_month, year( Datum_Maand ) AS year, month( Datum_Maand ) AS month, naam, 
            count( Datum_Maand ) AS tdag_maand
        FROM " . $table_prefix . "_maand     
        GROUP BY year, month, naam";

$result = mysqli_query($con, $sql) or die("Query failed. totaal " . mysqli_error($con));
$sum_per_year = array();
$total_sum_for_all_years = 0;
$average_per_month = 0;
$fsomeuro = 0;

$missing_days_month_year = array();
if (mysqli_num_rows($result) == 0) {
    $sum_per_year[date('Y-m-d', time())] = 0;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];
        if (!isset($sum_per_year[$row['year']])) {
            $sum_per_year[$row['year']][$inverter_name] = 0;
        }
        if (!isset($sum_per_year[$row['year']][$inverter_name])) {
            $sum_per_year[$row['year']][$inverter_name] = 0;
        }
        $sum_per_year[$row['year']][$inverter_name] += $row['sum_month'];

        $days_per_month = cal_days_in_month(CAL_GREGORIAN, $row['month'], $row['year']);
        $missingdays = $days_per_month - $row['tdag_maand'];

        $missing_days_month_year[$row['year']][$row['month']] = $missingdays;
        if (!in_array($inverter_name, $inveter_list)) {
            if (in_array($inverter_name, $sNaamSaveDatabase)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        };
    }
    $total_sum_for_all_years = 0;
    foreach ($sum_per_year as $year => $val) {
        $total_sum_for_all_years += array_sum($val);
    }
    $average_per_month = $total_sum_for_all_years / count($sum_per_year);
    $fsomeuro += $current_euro * $total_sum_for_all_years;
}


$sqlref = "SELECT month( Datum_Refer ) AS maand, Geg_Refer, Dag_Refer
        FROM " . $table_prefix . "_refer ";

$resultref = mysqli_query($con, $sqlref) or die("Query failed. totaal-ref " . mysqli_error($con));
$frefjaar = 0;
$arefjaar = array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
if (mysqli_num_rows($resultref) != 0) {
    while ($row = mysqli_fetch_array($resultref)) {
        $frefjaar += $row['Geg_Refer'];
        $arefjaar[$row['maand']] = $row['Dag_Refer'];
    }
} else
    $frefjaar = 1;

$sqlgem = "SELECT month( Datum_Maand ) AS maand, AVG( Geg_Maand ) AS gem, naam
        FROM " . $table_prefix . "_maand 
        GROUP BY maand, naam";
$resultgem = mysqli_query($con, $sqlgem) or die("Query failed. totaal-ref " . mysqli_error($con));
while ($row = mysqli_fetch_array($resultgem)) {
    $agemjaar[$row['maand']] = $row['gem'];
}

$sqlverbruik = "SELECT sum( Geg_Verbruik_Dag ) AS verdag, sum( Geg_Verbruik_Nacht ) AS vernacht,
        year( Datum_Verbruik ) AS jaar
        FROM " . $table_prefix . "_verbruik 
        GROUP BY jaar";

$resultverbruik = mysqli_query($con, $sqlverbruik) or die("Query failed. jaar-verbruik " . mysqli_error($con));
if (mysqli_num_rows($resultverbruik) == 0) {
    $ajaarverbruikdag[] = 0;
    $ajaarverbruiknacht[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultverbruik)) {
        $ajaarverbruikdag[$row['jaar']] = $row['verdag'];
        $ajaarverbruiknacht[$row['jaar']] = $row['vernacht'];
    }
}

// calculate expected values per years without day with no data
$averwacht = array();
$expected_bars = "";
foreach ($missing_days_month_year as $ijaar => $months) {
    if (!isset($averwacht[$ijaar])) $averwacht[$ijaar] = 0;
    for ($i = 1; $i <= 12; $i++) {
        if (!isset($agemjaar[$i])) $agemjaar[$i] = 0;
        if (array_key_exists($i, $months)) {
            if ($months[$i] != 0)
                $averwacht[$ijaar] += $agemjaar[$i] * $months[$i];
        } else {
            $iaantaldagen = cal_days_in_month(CAL_GREGORIAN, $i, $ijaar);
            if (array_key_exists($i, $agemjaar))
                $averwacht[$ijaar] += $agemjaar[$i] * $iaantaldagen;
            else
                $averwacht[$ijaar] += $arefjaar[$i] * $iaantaldagen;
        }
    }
    // fixme
    // $averwacht[$ijaar] += $sum_per_year[$ijaar];

    // expected bars char
    $val = 0;
    if (isset ($averwacht[$ijaar])) {
        $val = round($averwacht[$ijaar], 2);
        $expected_bars .= "                
                    { 
                      y: $val,                       
                      color: \"#" . $colors['color_chart_expected_bar'] . "\",
                    },";
    }
}

?>


<?php
// ----------------------------- build data for chart -----------------------------------------------------------------
$href = "year_overview.php?jaar=";
$my_year = date("Y", time());
$strgeg = "";
$strxas = "";
$aclickxas = array();
$astrverbruikdag = "";
$first_year = 0;
$yearcount = count($sum_per_year);
$current_bars = "";
$categories = "";
$best_year = 0;
$strdataseries = "";

$myColors = array();
for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
    $col1 = "color_inverter" . $k ."_chartbar_min";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k ."_chartbar_max";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['max'] = $col1;
}

foreach ($inveter_list as $inverter_name) {

    $current_bars = "";
    $best_year_per_inverter = 0;
    foreach ($sum_per_year as $ijaar => $fkw) {
        $categories .= '"' . $ijaar . '",';

        if ($first_year == 0) $first_year = $ijaar;

        $myColor1 =$myColors[$inverter_name]['min'];
        $myColor2 =$myColors[$inverter_name]['max'];
        if ($fkw >= max($sum_per_year)) {
            $myColor1 = "'#" .$colors['color_chartbar_piek1'] . "'";
            $myColor2 = "'#" .$colors['color_chartbar_piek2'] . "'";
            $best_year_per_inverter = max($sum_per_year)[$inverter_name];
        }

        // normal chart
        $val = round($fkw[$inverter_name], 2);
        $current_bars .= "
                    {  
                      y: $val, 
                      url: \"$href$ijaar-01-01\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                        stops: [
                            [0, $myColor1],
                            [1, $myColor2]
                        ]}                                                       
                    },";


        $strxas .= '"' . $ijaar . '",';
        $aclickxas[0][] = $ijaar . "-01-01";

        if ($param['iTonendagnacht'] == 1) {
            if (array_key_exists($ijaar, $ajaarverbruikdag)) {
                if (!isset($ajaarverbruiknacht[$ijaar])) $ajaarverbruiknacht[$ijaar] = 0;
                if (array_key_exists($ijaar, $sum_per_year)) {
                    $ftotaalverbruik = $ajaarverbruikdag[$ijaar] + $ajaarverbruiknacht[$ijaar] + $sum_per_year[$ijaar];
                    $astrverbruikdag .= '
                },';
                }
            }
        }

    }
    $best_year += $best_year_per_inverter;
    $current_bars = substr($current_bars, 0, -1);
    $strdataseries .= " {
                    name: '" . $inverter_name . "',
                    type: 'column',
                    stacking: 'normal',                    
                    data: [" . $current_bars . "],
                        }, 
                    ";

}



// strip last ","
$strgeg = substr($strgeg, 0, -1);
$strxas = substr($strxas, 0, -1);

$categories = substr($categories, 0, -1);
$myKeys = array_keys($sum_per_year);

$sub_title = "";
$sub_title .= "<b>" . $txt["totaal"] . ": <\/b>"
    . number_format($total_sum_for_all_years, 0, ',', '.') . " kWh = "
    . number_format($fsomeuro, 0, ',', '.') . "€ = "
    . number_format(1000 * $total_sum_for_all_years / $ieffectiefkwpiek, 0, ',', '.') . " kWh/kWp<br />";
$sub_title .= "<b>" . $txt["max"] . ": <\/b>"
    . number_format($best_year, 0, ',', '.') . " kWh = "
    . number_format(1000 * $best_year / $ieffectiefkwpiek, 0, ',', '.') . " kWh = <br />";

$sub_title .= "<b>" . $txt["gem"] . ": <\/b>" . number_format($average_per_month, 0, ',', '.') . " kWh   ";
$sub_title .= "<b>" . $txt["ref"] . ": <\/b>" . number_format($frefjaar, 0, ',', '.') . " kWh";

$show_legende = "true";
if ($isIndexPage == true) {
    echo ' <div class = "index_chart" id="total_chart_' . $inverter_id . '"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {
        var sub_title = '<?php echo $sub_title ?>';
        var avrg = <?php echo round($average_per_month, 0); ?>;
        var ref = <?php echo round($frefjaar, 0); ?>;
        var years = <?php echo $yearcount ?>;
        var myoptions = <?php echo $chart_options ?>;

        var mychart = new Highcharts.Chart('total_chart_<?php echo $inverter_id ?>', Highcharts.merge(myoptions, {

            chart: {
                events: {
                    render() {
                        // make serias public available
                        mychart = this;
                        series = this.series;
                    }
                }
            },
            subtitle: {
                text: sub_title,
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
                        color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>'
                    },
                },
                steps: 100,
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
            }],
            tooltip: {
                formatter: function () {
                    if (this.series.name == 'kWh/Year') {
                        return this.x + ': ' + this.y.toFixed(0) + 'kWh';
                    } else {
                        return this.series.name + ' ' + this.y.toFixed(0) + 'kWh';
                    }
                }
            },


            series: [
                {
                    name: "Expected",
                    type: "column",
                    color: '#<?php echo $colors['color_chart_expected_bar'] ?>',
                    data: [<?php echo $expected_bars; ?>],
                },
                <?php echo $strdataseries ?>
                {
                    name: "Average",
                    type: "line",
                    color: '#<?php echo $colors['color_chart_average_line'] ?>',
                    data: [{x: -0.4, y: avrg}, {x: years - 0.6, y: avrg}],
                },
                {
                    name: "Reference",
                    type: "line",
                    color: '#<?php echo $colors['color_chart_reference_line'] ?>',
                    data: [{x: -0.4, y: ref}, {x: years - 0.6, y: ref}],
                },
            ]
        }));

        $("#total_chart_<?php echo $inverter_id ?>").resize(function () {
            mychart.reflow();
        });
    });


</script>

