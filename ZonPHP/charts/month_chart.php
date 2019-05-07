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

$chartcurrentdate = @mktime();
$chartdate = $chartcurrentdate;

$chartdatestring = strftime("%Y-%m-%d", $chartdate);

if (isset($_GET['maand'])) {
    $chartdatestring = html_entity_decode($_GET['maand']);
    $chartdate = strtotime($chartdatestring);
    // reformat string
    $chartdatestring = strftime("%Y-%m-%d", $chartdate);
}


// -----------------------------  get data from DB -----------------------------------------------------------------

$current_year = date('Y', $chartdate);
$current_month = date('m', $chartdate);
$current_year_month = "" . date('Y-m', $chartdate);

if (isset($year_euro[$current_year])) {
    $current_euroval = $year_euro[$current_year];
} else {
    $current_euroval = 0.25;
}

// get reference values
$sqlref = "SELECT *
        FROM " . $table_prefix . "_refer
        WHERE DATE_FORMAT(Datum_Refer,'%m')='" . $current_month . "'" . "
        ORDER BY Datum_Refer ASC";
$resultref = mysqli_query($con, $sqlref) or die("Query failed. maand-ref " . mysqli_error($con));
$frefmaand = 0;
if (mysqli_num_rows($resultref) == 0) {
    $frefmaand = 1;
} else {
    while ($row = mysqli_fetch_array($resultref)) {
        $frefmaand += $row['Dag_Refer'];
    }
}

$DaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

$sql = "SELECT Datum_Maand, sum(Geg_Maand) as Geg_Maand, naam
        FROM " . $table_prefix . "_maand
        where Datum_Maand like '" . $current_year_month . "%'
        GROUP BY Datum_Maand, naam
        ORDER BY Datum_Maand ASC";
$result = mysqli_query($con, $sql) or die("Query failed. maand " . mysqli_error($con));

$all_valarray = array();
$inveter_list = array();
if (mysqli_num_rows($result) == 0) {
    $datum = $txt["nodata"] . strftime("%B-%Y", $chartdate);
    $agegevens[] = 0;
    $iyasaanpassen = $frefmaand * 1.5;
    $geengevmaand = 0;
    $fgemiddelde = 0;
} else {
    $geengevmaand = 1;
    $agegevens = array();
    // fill empty days
    for ($i = 1; $i <= $DaysPerMonth; $i++) {
        $agegevens[$i] = 0;
    }
    for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
        for ($i = 1; $i <= $DaysPerMonth; $i++) {
            $all_valarray[$i][$sNaamSaveDatabase[$k]] = 0;
        }
    }

    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['naam'];

        $agegevens[date("j", strtotime($row['Datum_Maand']))] += $row['Geg_Maand'];
        $all_valarray[ date("j", strtotime($row['Datum_Maand']))] [$inverter_name]  = $row['Geg_Maand'];
        $dmaandjaar[] = $row['Datum_Maand'];
        if (!in_array($inverter_name, $inveter_list)){
            if (in_array($inverter_name, $sNaamSaveDatabase)) {
                // add to list only if it configured (ignore db entries)
                $inveter_list[] = $inverter_name;
            }
        } ;
    }
    $datum = strftime("%B-%Y", $chartdate);

    $daycount=0;
    for ($i = 1; $i <= $DaysPerMonth; $i++) {
        if ($agegevens[$i] > 0) {
            $daycount++;
        }
    }
    if ($daycount == 0) {
        $daycount=1;
    }
    $fgemiddelde = array_sum($agegevens) / $daycount;
    $iyasaanpassen = round(0.5 + max($agegevens) / 5) * 5;

}
?>


<?php
// -----------------------------  build data for chart -----------------------------------------------------------------

// build colors per inverter array
$myColors = array();
for ($k = 0; $k < count($sNaamSaveDatabase); $k++) {
    $col1 = "color_inverter" . $k ."_chartbar_min";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['min'] = $col1;
    $col1 = "color_inverter" . $k ."_chartbar_max";
    $col1 = "'#" . $colors[$col1] . "'";
    $myColors[$sNaamSaveDatabase[$k]]['max'] = $col1;
}

// collect data array
$myurl = "day_overview.php?dag=";

$categories = "";
$strdataseries = "";
$maxval_yaxis = 0;
foreach ($inveter_list as $inverter_name) {

    $strdata = "";
    $local_max = 0;
    for ($i = 1; $i <= $DaysPerMonth; $i++) {
        $categories .= '"' . $i . '",';
        if (array_key_exists($i, $agegevens)) {

            $myColor1 =$myColors[$inverter_name]['min'];
            $myColor2 =$myColors[$inverter_name]['max'];

            if ($agegevens[$i] == max($agegevens)) {
                $myColor1 = "'#" . $colors['color_chartbar_piek1'] . "'";
                $myColor2 = "'#" . $colors['color_chartbar_piek2'] . "'";
            }
            $var = round($all_valarray[$i][$inverter_name], 2);
            if ($var > $local_max ) $local_max = $var;
            $strdata .= "
                    {
                      y: $var, 
                      url: \"$myurl$current_year_month-$i\",
                      color: {
                        linearGradient: { x1: 0, x2: 0, y1: 0, y2: 1 },
                        stops: [
                            [0, $myColor1],
                            [1, $myColor2]
                        ]}                                                       
                    },";
        }
    }
    $maxval_yaxis += $local_max;
    $local_max = 0;
    $strdata = substr($strdata, 0, -1);
    $strdataseries .= " {
                    name: '". $inverter_name. "',
                    color: { linearGradient: {x1: 0, x2: 0, y1: 0, y2: 1}, stops: [ [0, $myColor1], [1, $myColor2]] },
                    type: 'column',
                    stacking: 'normal',
                    data: [".$strdata."]
                },
    ";

}
$categories = substr($categories, 0, -1);


$sub_title = "";
if ($geengevmaand != 0) {
    $sub_title .= "<b>" . strftime("%B", $chartdate) . ": <\/b>" . number_format(array_sum($agegevens), 2, ',', '.') . "kWh = "
        . number_format(1000 * array_sum($agegevens) / $ieffectiefkwpiek, 1, ',', '.') . "  kWh/kWp = "
        . number_format(100 * array_sum($agegevens) / ($frefmaand * $DaysPerMonth), 0, ',', '.') . "%<br />";
    $sub_title .= "<b>" . $txt["max"] . ": <\/b>" . number_format(max($agegevens), 2, ',', '.') . "  kWh = "
        . number_format(1000 * max($agegevens) / $ieffectiefkwpiek, 1, ',', '.') . "  kWh/kWp <br /> ";
    $sub_title .= "<b>" . $txt["gem"] . ": <\/b>" . number_format($fgemiddelde, 2, ',', '.') . "kWh  ";
    $sub_title .= "<b>" . $txt["ref"] . ": <\/b>" . number_format($frefmaand, 2, ',', '.') . "kWh";
};

$show_legende = "true";
if ($isIndexPage == true) {
    echo '<div class = "index_chart" id="month_chart_' . $inverter_id . '"></div>';
    $show_legende = "false";
}

include_once "chart_styles.php";
?>

<script type="text/javascript">

    $(function () {

        var daycount = <?php echo $DaysPerMonth ?>;
        var avg = <?php echo round($fgemiddelde, 2); ?>;
        var ref = <?php echo round($frefmaand, 2); ?>;
        var sub_title = '<?php echo $sub_title ?>';
        var myoptions = <?php echo $chart_options ?>;

        var mychart = new Highcharts.Chart('month_chart_<?php echo $inverter_id ?>', Highcharts.merge(myoptions, {

            chart: {
                events: {
                    // make serias public available
                    render() {
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
                    rotation: 270,
                    step: 1,
                    style: {
                        color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                    },
                },
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
                gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
                max: <?php echo $maxval_yaxis ?>,
            }],
            tooltip: {
                formatter: function () {
                    if (this.series.name == 'Reference' || this.series.name == 'Average') {
                        return this.series.name + ' ' + this.y.toFixed(2) + 'kWh';
                    }
                    else {
                        return this.x + ': ' + this.y.toFixed(2) + 'kWh';
                    }
                }
            },
            series: [
                <?php echo $strdataseries ?>
                {
                    name: 'Reference',
                    type: 'line',
                    color: '#<?php echo $colors['color_chart_reference_line'] ?>',
                    data: [
                        {x: 0, y: ref},
                        {x: daycount - 1, y: ref}
                    ]
                }, {
                    name: 'Average',
                    type: 'line',
                    color: '#<?php echo $colors['color_chart_average_line'] ?>',
                    data: [
                        {x: 0, y: avg},
                        {x: daycount - 1, y: avg}
                    ]
                }
            ]

        }));

        $("#month_chart_<?php echo $inverter_id ?>").resize(function () {
            mychart.reflow();
        });
    });


</script>



