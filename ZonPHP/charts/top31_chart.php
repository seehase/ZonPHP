<?php
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "Parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}

$isIndexPage = false;
$DESC_ASC = "DESC";
$txt["top31"] = "Top";
$showTopFlop = "top31_chart";
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
    if (isset($_POST['action']) && ($_POST['topflop'] == "top")) {
        $DESC_ASC = "DESC";
        $txt["top31"] = "Top";
        $showTopFlop = "top31_chart";
    } else {
        $DESC_ASC = "ASC";
        $txt["top31"] = "Flop";
        $showTopFlop = "flop31_chart";
    }
} else {
    if (isset($_GET['Max_Min']) && ($_GET['Max_Min'] == "top")) {
        $isIndexPage = false;

        $DESC_ASC = "DESC";
        $txt["top31"] = "Top";
        $showTopFlop = "top31_chart";
    } else {
        $DESC_ASC = "ASC";
        $txt["top31"] = "Flop";
        $showTopFlop = "flop31_chart";
    }
}

unset($agegevens);

$sql = '
            SELECT Datum_Maand as mydate, sum(Geg_Maand) as mysum                     
            FROM ' . $table_prefix . '_maand
            GROUP BY Datum_Maand
            ORDER BY mysum ' . $DESC_ASC . ' LIMIT 0,31
';

$fsomeuro = 0;
$result = mysqli_query($con, $sql) or die("Query failed. de_top_31_dagen " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $datum = "Geen data";
    $agegevens[date("Y-m-d", time())] = 0;
    $amaxref[] = 0;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $agegevens[date("Y-m-d", strtotime($row['mydate']))] = $row['mysum'];
        $fsomeuro += $price_per_kwh * $row['mydate'];
    }
    $datum = date("M-Y", time());
}

?>

<?php

$current_bars = "";
$iteller = 1;
$myColor1 = $colors['color_chartbar1'];
$myColor2 = $colors['color_chartbar2'];
$href = "day_overview.php?dag=";

$cnt = 0;
foreach ($agegevens as $ddag => $fkw) {
    $cnt++;

    $sum_kwpeak = array_sum($ieffectief_kwpiek);
    $kwpeak = number_format(($fkw * 1000 / $sum_kwpeak), 2, ',', '.');
    $current_bars .= "
                    {  
                      y: $fkw , 
                      url: \"$href$ddag\",
                      ddag: \"$ddag\",
                      kwpeak: \"$kwpeak\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                        stops: [
                            [0, '#$myColor1'],
                            [1, '#$myColor2']
                        ]}                                                       
                    },";

}

$sub_title = ("<b>" . $txt["totaal"] . ": <\/b>"
    . number_format(array_sum($agegevens), 1, ',', '.') . " kWh = "
    . number_format(array_sum($agegevens) * 4086 / 10000, 2, ',', '.') . "€");

$id = $showTopFlop . '_';

$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="' . $id . '"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {

        var sub_title = '<?php echo $sub_title ?>';
        var myoptions = <?php echo $chart_options ?>;


        var mychart = new Highcharts.Chart('<?php echo $id ?>', Highcharts.merge(myoptions, {

            subtitle: {
                text: sub_title,
                style: {
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
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
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
            }],
            tooltip: {

                formatter: function () {
                    value = Highcharts.numberFormat(this.y, 2);
                    kwpeak = this.point.kwpeak;
                    total = '<?php echo $txt['totaal'] ?>';
                    day = Highcharts.dateFormat("%Y-%m-%d", new Date(this.point.ddag));
                    val = `<b> ${day}:</b> <br/>${total}:<b>${value} kWh<\/b> = ${this.point.kwpeak} kWh/kWp<br/>`;
                    val1 = this.x + ': ' + this.y.toFixed(2) + 'kWh';
                    return val;
                }
            },
            series: [
                {
                    name: 'Day',
                    color: '#0C0CFF',
                    type: 'column',
                    data: [<?php echo $current_bars ?>]
                },
            ]

        }));

        $("#<?php echo $id ?>").resize(function () {
            mychart.reflow();
        });


    });


</script>