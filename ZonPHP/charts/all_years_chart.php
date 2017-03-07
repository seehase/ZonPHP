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

// -----------------------------  get data from DB -----------------------------------------------------------------
$datum = "";

$current_year = date('Y', time());
if (isset($year_euro[$current_year])) {
    $current_euro = $year_euro[$current_year];
} else {
    $current_euro = 0.25;
}
$sqlref = "SELECT month( Datum_Refer ) AS maand, Geg_Refer, Dag_Refer
        FROM " . $table_prefix . "_refer
        WHERE Naam = '" . $inverter . "'";
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

$sqlgem = "SELECT month( Datum_Maand ) AS maand, AVG( Geg_Maand ) AS gem
        FROM " . $table_prefix . "_maand
        WHERE Naam = '" . $inverter . "'
        GROUP BY maand";
$resultgem = mysqli_query($con, $sqlgem) or die("Query failed. totaal-ref " . mysqli_error($con));
while ($row = mysqli_fetch_array($resultgem)) {
    $agemjaar[$row['maand']] = $row['gem'];
}

$sqlverbruik = "SELECT sum( Geg_Verbruik_Dag ) AS verdag, sum( Geg_Verbruik_Nacht ) AS vernacht,
        year( Datum_Verbruik ) AS jaar
        FROM " . $table_prefix . "_verbruik
        WHERE Naam = '" . $inverter . "'
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

$averwacht = array();
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
    $averwacht[$ijaar] += $sum_per_year[$ijaar];
}

?>


<?php
// ----------------------------- build data for chart -----------------------------------------------------------------
$href = "year_overview.php?jaar=";
$my_year = date("Y", time());
$strgeg = "";
$strxas = "";
$strgem = "";
$strref = "";
$teller = 1;
$fsomeuro = 0;
$aclickxas = array();
$astrverwacht = "";
$astrverbruikdag = "";


$first_year = 0;

$yearcount = count($sum_per_year);
$expected_bars = "";
$current_bars = "";
$categories = "";

foreach ($sum_per_year as $ijaar => $fkw) {


    // get month names in current locale
    $categories .= '"' . $ijaar . '",';

    if ($first_year == 0) $first_year = $ijaar;
    $stoon = "";
    if ($param['iTonendagnacht'] == 1) {
        if (isset($ajaarverbruikdag[$ijaar]))
            $stoon .= '<br />' . $txt["dagverbruik"] . ': ' . number_format($ajaarverbruikdag[$ijaar], 0, ',', '.') . ' kWh';
        if (isset($ajaarverbruiknacht[$ijaar]))
            $stoon .= '<br />' . $txt["nachtverbruik"] . ': ' . number_format($ajaarverbruiknacht[$ijaar], 0, ',', '.') . ' kWh';
        if (array_key_exists($ijaar, $sum_per_year) && isset($ajaarverbruikdag[$ijaar]) && isset($ajaarverbruiknacht[$ijaar]))
            $stoon .= '<br />' . $txt["totaalverbruik"] . ': ' . number_format($ajaarverbruikdag[$ijaar] + $ajaarverbruiknacht[$ijaar] + $sum_per_year[$ijaar], 0, ',', '.') . ' kWh';
    }

    // expected bars char
    $val = 0;
    if (isset ($averwacht[$ijaar])) {
        $val = round($averwacht[$ijaar], 2);
        $expected_bars .= "                
                    { 
                      y: $val, 
                      url: \"$href$ijaar-01-01\",
                      color: \"#" . $colors['color_chart_expected_bar'] . "\",
                    },";

    }
    $myColor1 = $colors['color_chartbar1'];
    $myColor2 = $colors['color_chartbar2'];
    if ($fkw >= max($sum_per_year)) {
        $myColor1 = $colors['color_chartbar_piek1'];
        $myColor2 = $colors['color_chartbar_piek2'];
    }


    // normal chart
    $val = round($fkw, 2);
    $current_bars .= "
                    {  
                      y: $val, 
                      url: \"$href$ijaar-01-01\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                        stops: [
                            [0, '#$myColor1'],
                            [1, '#$myColor2']
                        ]}                                                       
                    },";


    $strxas .= '"' . $ijaar . '",';
    $aclickxas[0][] = $ijaar . "-01-01";
    $astrgem[] = '{

                },';
    $astrref[] = '{

                },';
    $astrverwacht .= '{

                },';
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
    $teller++;
    if (!isset($year_euro[$ijaar])) $year_euro[$ijaar] = 0.25;
    $fsomeuro += $year_euro[$ijaar] * $fkw;


}


// strip last ","
$strgeg = substr($strgeg, 0, -1);
$strxas = substr($strxas, 0, -1);

$categories = substr($categories, 0, -1);

foreach ($astrgem as $ijaar => $fkw) {
    $strgem .= $fkw;
}
foreach ($astrref as $ijaar => $fkw) {
    $strref .= $fkw;
}
$strref = substr($strref, 0, -1);

$myKeys = array_keys($sum_per_year);


$sub_title = "";
$sub_title .= ("<b>" . $txt["totaal"] . ": <\/b>"
    . number_format(array_sum($sum_per_year), 0, ',', '.') . " kWh = "
    . number_format($fsomeuro, 0, ',', '.') . "€ = "
    . number_format(1000 * array_sum($sum_per_year) / $ieffectiefkwpiek, 0, ',', '.') . " kWh/kWp<br />");
$sub_title .= ("<b>" . $txt["max"] . ": <\/b>"
    . number_format(max($sum_per_year), 0, ',', '.') . " kWh = "
    . number_format(1000 * max($sum_per_year) / $ieffectiefkwpiek, 0, ',', '.') . " kWh = "
    . number_format(100 * max($sum_per_year) / $frefjaar, 0, ',', '.') . "%<br />");
$sub_title .= ("<b>" . $txt["gem"] . ": <\/b>" . number_format($avarage_per_month, 0, ',', '.') . " kWh");
$sub_title .= ("<b>" . $txt["ref"] . ": <\/b>" . number_format($frefjaar, 0, ',', '.') . " kWh");

$show_legende = "true";
if ($isIndexPage == true) {
    echo ' <div class = "index_chart" id="total_chart_' . $inverter . '"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {
        var sub_title = '<?php echo $sub_title ?>';
        var avrg = <?php echo round($avarage_per_month,0); ?>;
        var ref = <?php echo round($frefjaar, 0); ?>;
        var years = <?php echo $yearcount ?>;
        var myoptions = <?php echo $chart_options ?>;

        var mychart = new Highcharts.Chart('total_chart_<?php echo $inverter ?>', Highcharts.merge(myoptions, {

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
                    }
                    else {
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
                {
                    name: 'kWh/Year',
                    type: 'column',
                    color: '#<?php echo $colors['color_chartbar1'] ?>',
                    data: [<?php echo $current_bars; ?>],
                },
                {
                    name: "Avarage",
                    type: "line",
                    color: '#<?php echo $colors['color_chart_avarage_line'] ?>',
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

        $("#total_chart_<?php echo $inverter ?>").resize(function () {
            mychart.reflow();
        });
    });


</script>

