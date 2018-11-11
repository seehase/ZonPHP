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

// ---------------------------------------------------------------------------------------------------------------------

$sqlref = "SELECT *
	FROM " . $table_prefix . "_refer
	WHERE Naam='" . $inverter . "' ORDER BY Datum_Refer ASC";

$resultref = mysqli_query($con, $sqlref) or die("Query failed. jaar-ref " . mysqli_error($con));
if (mysqli_num_rows($resultref) == 0) {
    $frefmaand[] = 0;
} else {
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand[date("n", strtotime($row['Datum_Refer']))] = $row['Geg_Refer'];
    }
}

$sql = "SELECT DAYOFWEEK(Datum_Maand) AS weekdag,count(Datum_Maand) AS aantaldw,MAX(Geg_Maand) AS maxdw,SUM(Geg_Maand) AS somdw,Naam
	FROM " . $table_prefix . "_maand
	WHERE Naam='" . $inverter . "'
	group by weekdag
	ORDER BY weekdag ASC";

$result = mysqli_query($con, $sql) or die("Query failed. jaar " . mysqli_error($con));
$agegaantaldw = array(0, 1, 1, 1, 1, 1, 1, 1);
$agegmaxdw = array(0, 0, 0, 0, 0, 0, 0, 0);
$agegsomdw = array(0, 0, 0, 0, 0, 0, 0, 0);
$aberekengem = array(0, 0, 0, 0, 0, 0, 0, 0);
if (mysqli_num_rows($result) == 0) {
    $datum = "Leeg";

} else {
    while ($row = mysqli_fetch_array($result)) {
        $agegaantaldw[$row['weekdag']] = $row['aantaldw'];
        $agegmaxdw[$row['weekdag']] = $row['maxdw'];
        $agegsomdw[$row['weekdag']] = $row['somdw'];
        $aberekengem[$row['weekdag']] = $row['somdw'] / $row['aantaldw'];
    }
    $datum = "Data";
    $iyasaanpassen = (round(0.5 + max($aberekengem) / 5) * 5);
}

?>

<?php


$strgemiddelde = "";
$href = "index.php";
$categories = "";
$current_bars = "";
$maximum = 0;
$sum = 0;
for ($i = 1; $i <= 7; $i++) {

    if ($isIndexPage == true) {
        $categories .= '"' . ($short_weekdays[$i - 1]) . '",';
    } else{
        $categories .= '"' . ($weekdays[$i - 1]) . '",';
    }
    $sum += $aberekengem[$i];
    if ($aberekengem[$i] > $maximum) {
        $maximum = $aberekengem[$i];
    }

    $myColor1 = $colors['color_chartbar1'];
    $myColor2 = $colors['color_chartbar2'];
    if ($aberekengem[$i] >= max($aberekengem)) {
        $myColor1 = $colors['color_chartbar_piek1'];
        $myColor2 = $colors['color_chartbar_piek2'];
    }

    // normal actual  bar
    $val = number_format($aberekengem[$i], 3, '.', ',');
    $current_bars .= "
                    { x: $i-1, 
                      y: $val ,                       
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                        stops: [
                            [0, '#$myColor1'],
                            [1, '#$myColor2']
                        ]}                                                       
                    },";
}
$avgr = number_format(($sum / 7), 2);
$maximum = number_format(($maximum / 7), 2);

$sub_title = ("<b>" . $txt["totaal"] . ": <\/b>"
    . number_format($total_sum_for_all_years, 1, ',', '') . " kWh = "
    . number_format($total_sum_for_all_years * 4086 / 10000, 2, ',', '.') . "€ = "
    . number_format(1000 * $total_sum_for_all_years / $ieffectiefkwpiek, 0, ',', '.') . " kWh/kWp<br />");

$sub_title = "";
$categories = substr($categories, 0, -1);

$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="week_chart_' . $inverter . '"></div>';
    $show_legende = "false";
};

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {
        var sub_title = '<?php echo $sub_title ?>';
        var avrg =<?php echo $avgr ?>;
        var myoptions = <?php echo $chart_options ?>;

        var mychart = new Highcharts.Chart('week_chart_<?php echo $inverter ?>', Highcharts.merge(myoptions, {

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
                max: 6,
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
                    if (this.series.name == 'Maximum' || this.series.name == 'Average') {
                        return this.series.name + ' ' + this.y.toFixed(2) + 'kWh';
                    }
                    else {
                        return this.x + ': ' + this.y.toFixed(2) + 'kWh';
                    }
                }
            },

            series: [
                {
                    name: 'week days',
                    type: 'column',
                    color: '#<?php echo $colors['color_chartbar1'] ?>',
                    data: [<?php echo $current_bars; ?>],
                },
                {
                    name: "Average",
                    type: "line",
                    color: '#<?php echo $colors['color_chart_average_line'] ?>',
                    data: [{x: -0.4, y: avrg}, {x: 6.4, y: avrg}],
                },
            ]
        }));

        $("#week_chart_<?php echo $inverter ?>").resize(function () {

            var parentheight = $(this).parent().height();
            var headerheight = $('#week_chart_header').height();

            mychart.reflow();

        });


    });


</script>

