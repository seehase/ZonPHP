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

$showAllInverters = false;
$inverter_id = $inverter;
$inverter_clause1 = " AND Naam='" . $inverter . "' ";
$inverter_clause2 = " WHERE Naam='" . $inverter . "' ";
if ((isset($_POST['type']) && ($_POST['type'] == "all"))  ||
   (isset($_GET['type']) && ($_GET['type'] == "all"))) {
    $showAllInverters = true;
    $inverter_id = "all";
    $inverter_clause1 = " ";
    $inverter_clause2 = " ";
}

$chartdate = time();
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
	WHERE DATE_FORMAT(Datum_Verbruik,'%y')='" . date('y', $chartdate) . "' AND Naam='" . $inverter . "'";

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

$sqlref = "SELECT *
	FROM " . $table_prefix . "_refer
	WHERE Naam='" . $inverter . "' ORDER BY Datum_Refer ASC";

$resultref = mysqli_query($con, $sqlref) or die("Query failed. jaar-ref " . mysqli_error($con));
if (mysqli_num_rows($resultref) == 0) {
    $frefmaand = array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
    $frefdagmaand = array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
} else {
    $frefmaand = array();
    $frefdagmaand = array();
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand[date("n", strtotime($row['Datum_Refer']))] = $row['Geg_Refer'];
        $frefdagmaand[date("n", strtotime($row['Datum_Refer']))] = $row['Dag_Refer'];
    }
    $iyasaanpassen = (round(0.5 + max($frefmaand) / 50) * 50);
}

$sql = "SELECT MAX( Datum_Maand ) AS maxi, SUM( Geg_Maand ) AS som,COUNT(Geg_Maand) AS aantal
	FROM " . $table_prefix . "_maand
	where DATE_FORMAT(Datum_Maand,'%y')='" . date('y', $chartdate) . "'"  . $inverter_clause1 . "
	GROUP BY month(Datum_Maand)
	ORDER BY 1 ASC";

$result = mysqli_query($con, $sql) or die("Query failed. jaar " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $datum = date("Y", $chartdate) . " geen data.";
    $agegevens = array();
    $fgemiddelde = 0;
    $agegaantal = array();
} else {
    $agegevens = array();
    while ($row = mysqli_fetch_array($result)) {
        $agegevens[date("n", strtotime($row['maxi']))] = $row['som'];
        $agegaantal[date("n", strtotime($row['maxi']))] = $row['aantal'];
    }
    $fgemiddelde = array_sum($agegevens) / count($agegevens);
    $datum = date("Y", $chartdate);
    $iyasaanpassen = (round(0.5 + max($agegevens) / 50) * 50);
}


$sqlmax = "SELECT maand,jaar,som
        FROM (SELECT month(Datum_Maand) AS maand,year(Datum_Maand) AS jaar,sum(Geg_Maand) AS som
                FROM " . $table_prefix . "_maand "
                . $inverter_clause2. "				
                GROUP BY maand,jaar
		) AS somquery
        JOIN (SELECT maand as tmaand, max( som ) AS maxgeg
                FROM (
                        SELECT maand, jaar, som
                        FROM (
                                SELECT month( Datum_Maand ) AS maand, year( Datum_Maand ) AS jaar, sum( Geg_Maand ) AS som
                                FROM " . $table_prefix . "_maand "
                                . $inverter_clause2. "								
                                GROUP BY maand, jaar
                        ) AS somqjoin
                ) AS maxqjoin
                GROUP BY tmaand
             )AS maandelijks
        ON (somquery.maand= maandelijks.tmaand
            AND maandelijks.maxgeg = somquery.som)
	ORDER BY maand";


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
    }
    $iyasaanpassen = (round(0.5 + max($maxmaand) / 50) * 50);
}

if (max($frefmaand) < max($maxmaand)) {
    $iyasaanpassen = (round(0.5 + max($maxmaand) / 50) * 50);
} else {
    $iyasaanpassen = (round(0.5 + max($frefmaand) / 50) * 50);
}

?>

<?php
$categories = "";
for ($i = 1; $i <= 12; $i++) {
    // get month names in current locale
    $categories .= '"' . ($months[$i]) . '",';
}

$categories = substr($categories, 0, -1);
$fverwacht = array();

$my_year = date("Y", $chartdate);
$href = "month_overview.php?maand=";
$gridlines = "";

$max_bars = "";
$expected_bars = "";
$current_bars = "";
$reflines = "";
for ($i = 1; $i <= 12; $i++) {
    $stoon = "";
    $sverwacht = "";
    if ($param['iTonendagnacht'] == 1) {
        if (isset($ajaarverbruikdag[$i]))
            $stoon .= '<br />' . $txt["dagverbruik"] . ': ' . number_format($ajaarverbruikdag[$i], 0, ',', '.') . ' kWh';
        if (isset($ajaarverbruiknacht[$i]))
            $stoon .= '<br />' . $txt["nachtverbruik"] . ': ' . number_format($ajaarverbruiknacht[$i], 0, ',', '.') . ' kWh';
        if (array_key_exists($i, $agegevens) && isset($ajaarverbruikdag[$i]) && isset($ajaarverbruiknacht[$i]))
            $stoon .= '<br />' . $txt["totaalverbruik"] . ': ' . number_format($ajaarverbruikdag[$i] + $ajaarverbruiknacht[$i] + $agegevens[$i], 0, ',', '.') . ' kWh';
    }

    // max bars
    $val = round($maxmaand[$i], 2);
    $max_bars .= "  { 
                      y:  $val, 
                      url: \"$href$my_year-$i-01\",
                      color: \"#" . $colors['color_chart_max_bar'] . "\"
                    },";

    $expected = 0.0;
    if (array_key_exists($i, $agegevens)) {

        if (array_key_exists($i, $agegaantal)) {
            if ($agegaantal[$i] < cal_days_in_month(CAL_GREGORIAN, $i, $i)) {
                $fverwacht[$i] = $agegevens[$i] + $frefdagmaand[$i] * (cal_days_in_month(CAL_GREGORIAN, $i, $i) - $agegaantal[$i]);
                $sverwacht = "<br />" . $txt["verwacht"] . ": " . number_format($fverwacht[$i], 0, ',', '.') . ' kWh';
                $expected = $fverwacht[$i];

                // expected bars char
                $val = round($fverwacht[$i], 2);
                $expected_bars .= "                
                    { x: ($i-1),
                      y:  $val, 
                      url: \"$href$my_year-$i-01\",
                      color: \"#" . $colors['color_chart_expected_bar'] . "\",
                    },";

            }
        }

        $myColor1 = $colors['color_chartbar1'];
        $myColor2 = $colors['color_chartbar2'];
        if ($agegevens[$i] == max($agegevens)) {
            $myColor1 = $colors['color_chartbar_piek1'];
            $myColor2 = $colors['color_chartbar_piek2'];
        }

        // normal actual  bar
        $val = round($agegevens[$i], 2);
        $current_bars .= "
                    { x: $i-1, 
                      y: $val, 
                      url: \"$href$my_year-$i-01\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                        stops: [
                            [0, '#$myColor1'],
                            [1, '#$myColor2']
                        ]}                                                       
                    },";
    }
    // refline per bar
    $reflines .= "{ name: 'ref_$maxmaand[$i]', type: 'line', color: '#" . $colors['color_chart_reference_line'] . "', data: [{ x: ($i -1.3), y: $frefmaand[$i]}, { x: ($i - 0.7), y: $frefmaand[$i] }], showInLegend: false},";
}
$max_bars = substr($max_bars, 0, -1);
$expected_bars = substr($expected_bars, 0, -1);
$current_bars = substr($current_bars, 0, -1);
$reflines = substr($reflines, 0, -1);

// avagageline char
$gridlines .= '{xaxis: {from:  0.5, to: 12.5}, yaxis: {from: ' . $fgemiddelde . ', to: ' . $fgemiddelde . '}, color: "#' . $colors['color_chart_reference_line'] . '", lineWidth: 1.5},';
$sub_title = "";

if ($datum != date("Y", $chartdate) . " geen data.") {
    $sub_title .= "<b>" . date("Y", time()) . ": <\/b>"
        . number_format(array_sum($agegevens), 0, ',', '.') . " kWh = "
        . number_format((array_sum($agegevens) * $current_euroval), 0, ',', '.') . "€ = "
        . number_format(1000 * array_sum($agegevens) / $ieffectiefkwpiek, 0, ',', '.') . " kWh/kWp = "
        . number_format(100 * array_sum($agegevens) / array_sum($frefmaand), 0, ',', '.') . "%<br />";
    $sub_title .= "<b>" . $txt["max"] . ": <\/b>" . number_format(max($agegevens), 0, ',', '.') . " kWh = "
        . number_format(1000 * max($agegevens) / $ieffectiefkwpiek, 0, ',', '.') . " kWh/kWp <br /> ";
    $sub_title .= "<b>" . $txt["gem"] . ": <\/b>" . number_format(array_sum($agegevens) / count($agegevens), 0, ',',
            '.') . " kWh";
}

$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="year_chart_' . $inverter_id . '"></div>';
    $show_legende = "false";
};

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {
        var sub_title = '<?php echo $sub_title ?>';
        var avrg =<?php echo round($fgemiddelde, 2) ?>;
        var myoptions = <?php echo $chart_options ?>;

        var mychart = new Highcharts.Chart('year_chart_<?php echo $inverter_id ?>', Highcharts.merge(myoptions, {

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
                        else
                        {
                        return this.series.name + ' ' + this.y.toFixed(0) + 'kWh';
                        }
                    }
                }
            },

            series: [
                {
                    name: "Maximum",
                    type: "column",
                    color: '#<?php echo $colors['color_chart_max_bar'] ?>',
                    data: [<?php echo $max_bars; ?>],
                },
                {
                    name: "Expected",
                    type: "column",
                    color: '#<?php echo $colors['color_chart_expected_bar'] ?>',
                    data: [<?php echo $expected_bars; ?>],
                },
                {
                    name: 'kWh/Month',
                    type: 'column',
                    color: '#<?php echo $colors['color_chartbar1'] ?>',
                    data: [<?php echo $current_bars; ?>],
                },
                {
                    name: "Average",
                    type: "line",
                    color: '#<?php echo $colors['color_chart_average_line'] ?>',
                    data: [{x: -0.4, y: avrg}, {x: 11.4, y: avrg}],
                },
                <?php echo $reflines; ?>
            ]
        }));

        $("#year_chart_<?php echo $inverter ?>").resize(function () {
            mychart.reflow();
        });
    });


</script>
