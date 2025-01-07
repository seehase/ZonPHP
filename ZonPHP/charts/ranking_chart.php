<?php
global $con, $colors, $params, $chart_options, $locale;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/charts/chart_support.php";

$isIndexPage = false;

if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}
$month_local = array();
$types = ['MMM', 'MMMM'];
foreach ($types as $tk => $tv) {
    $df = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, NULL, NULL, $tv);
    for ($i = 1; $i <= 12; $i++) {
        $val = $df->format(mktime(0, 0, 0, $i));
        $val = str_replace('.', '', $val);
        $month_local[$i][$tv] = $val;
    }
}
$years = array();
$sql2 = "SELECT distinct(YEAR(Datum_Maand)) as years FROM " . TABLE_PREFIX . "_maand";
$result = $con->query($sql2);
while ($row = $result->fetch_row()) {
    $years[] = $row[0];
}
$whereInYear = implode(',', $years);
$whereInMonth = '1,2,3,4,5,6,7,8,9,10,11,12';
$sort = "";
if (isset($_GET['sort']) && $_GET['sort'] != "undefined") {
    $sort = $_GET['sort'];
} else {
    $sort = 'desc';
}
$visibleInvertersJS = "";
if (isset($_GET['inverters']) && $_GET['inverters'] != "undefined" && $_GET['inverters'] != "") {
    $input = explode(',', $_GET['inverters']);
    $visibleInvertersString = "'" . implode("', '", $input) . "'";
    $visibleInvertersJS = implode(",", $input);
} else {
    $visibleInvertersString = "'" . implode("', '", PLANT_NAMES) . "'";
    $visibleInvertersJS = implode(",", PLANT_NAMES);
}
$whereInClause = " where naam in ($visibleInvertersString)";

$selectedMonths = array();
if (isset($_GET['months']) && $_GET['months'] != "undefined" && $_GET['months'] != "") {
    $selectedMonths = explode(',', $_GET['months']);
    $selectedMonthsString = $_GET['months'];
} else {
    $selectedMonths = explode(',', $whereInMonth);
    $selectedMonthsString = $whereInMonth;
}
$selectedYears = array();
if (isset($_GET['years']) && $_GET['years'] != "undefined" && $_GET['years'] != "") {
    $selectedYears = explode(",", $_GET['years']);
    $selectedYearsString = $_GET['years'];
} else {
    $selectedYears = $years;
    $selectedYearsString = $whereInYear;
}

$sql = "SELECT db1.*
FROM " . TABLE_PREFIX . "_maand AS db1
JOIN (SELECT Datum_Maand, sum(Geg_Maand) as mysum FROM " . TABLE_PREFIX . "_maand $whereInClause  AND MONTH(Datum_Maand) IN ($selectedMonthsString) AND YEAR(Datum_Maand) IN ($selectedYearsString) Group by Datum_Maand ORDER BY mysum $sort LIMIT 0,31) AS db2
ON db1.Datum_Maand = db2.Datum_Maand $whereInClause order by mysum $sort";

$result = mysqli_query($con, $sql) or die("Query failed. de_top_31_dagen " . mysqli_error($con));
$datum = "Geen data";
$adatum = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $inverter_name = $row['Naam'];
        $adatum[] = date("Y-m-d", strtotime($row['Datum_Maand']));
        $all_valarray[date("Y-m-d", strtotime($row['Datum_Maand']))] [$inverter_name] = $row['Geg_Maand'];
    }
    $datum = date("M-Y", time());
}
//clean-up category array
$adatum = array_values(array_unique($adatum));
?>

<?php
// -----------------------------  build data for chart -----------------------------------------------------------------
$myurl = HTML_PATH . "pages/day.php?date=";
$myMetadata = array();
$myColors = colorsPerInverter();
$plantNames = "";
$plantNamesJS = "";
$strdataseries = "";
$strdata = "";
$maxval_yaxis = 0;
$labels = convertValueArrayToDataString($adatum);

foreach (PLANT_NAMES as $key => $inverter_name) {
    $plantNames .= "'$inverter_name',";
    $plantNamesJS .= "$inverter_name,";
    $strdata = "";
    $local_max = 0;
    $myColor1 = $myColors[$inverter_name]['min'];
    $myColor2 = $myColors[$inverter_name]['max'];

    for ($i = 0; $i < count($adatum); $i++) {
        $val = 0.0;
        if (isset($adatum[$i])) {
            if (isset($all_valarray[$adatum[$i]][$inverter_name])) {
                $val = round($all_valarray[$adatum[$i]][$inverter_name], 2);
            }
            $formattedHref = sprintf("%s%s", $myurl, $adatum[$i]);
            $strdata .= " { x: $adatum[$i], y: $val, url: '$formattedHref'},";
        }
    }

    $maxval_yaxis += $local_max;
    $strdataseries .= " {
                    datasetId: '" . $inverter_name . "', 
                    label: '" . $inverter_name . "', 
                    inverter: '" . $inverter_name . "',
                    type: 'bar',                               
                    stack: 'Stack 0',
                    borderWidth: 1,
                    myColor: " . $myColor2 . ",
                    data: [" . $strdata . "],                    
                    dataCUM: [],
                    dataMAX: [], 
                    dataREF: [],
                    averageValue: 0,
                    expectedValue: 0,
                    maxIndex: 0,
                    fill: true,
                    backgroundColor: customGradientBackground,
                    yAxisID: 'y',
                    xAxisID: 'x',
                    isData: true,
                },
    ";
    $strdata = "";
}

$legendMonth = "";
foreach ($selectedMonths as $key) {
    $legendMonth .= $month_local[$key]["MMM"] . ", ";
}
$legendMonth = strip($legendMonth);

$show_legende = "true";
if ($isIndexPage) {
    echo '<div class = "index_chart" id="ranking">
            <canvas id="day_ranking_chart_canvas"></canvas>
         </div>';
    $show_legende = "false";
    $subtitle = "'" . strip($plantNamesJS) . "'";
} else {
    $subtitle = "['" . implode(", ", $selectedYears) . "', '" . $legendMonth . "']";
}

?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4/dist/chart.umd.min.js"></script>
<script src="<?= HTML_PATH ?>inc/js/chart_support.js"></script>
<script>
    const rankingLegendClickHandler = function (e, legendItem, legend) {
        let chart = legend.chart;
        // no default click handler here
        // Chart.defaults.plugins.legend.onClick(e, legendItem, legend);

        let data = chart.data;
        let visibleInverters = "";

        for (let inverter of data.inverters) {
            let idx = findDatasetById(data.datasets, inverter);
            if (idx >= 0) {
                if (inverter === legendItem.text) {
                    if (legendItem.hidden) {
                        visibleInverters += inverter + ",";
                    }
                } else {
                    let meta = chart.getDatasetMeta(0);
                    let isHidden = meta.hidden === null ? false : meta.hidden;
                    if (!isHidden) {
                        visibleInverters += inverter + ",";
                    }
                }
            }
        }

        let sort = "<?= $sort ?>";
        window.location.href = "?sort=" + sort +
            "&inverters=" + stripLastChar(visibleInverters) +
            "&months=" + getSelectedMonths() +
            "&years=" + getSelectedYears();
        chart.update();
    }

    $(function () {
            const ctx = document.getElementById('day_ranking_chart_canvas').getContext("2d");
            Chart.defaults.color = '<?= $colors['color_chart_text_title'] ?>';
            new Chart(ctx, {
                data: {
                    labels: [<?= $labels ?>],
                    inverters: [<?= $plantNames ?>],
                    datasets: [<?= $strdataseries  ?>],
                    myColors: <?= json_encode(colorsPerInverterJS()) ?>,
                    maxIndex: 99,
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '<?= getTxt("total") ?> (kWh)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return (value).toFixed(0)
                                }
                            },
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        customCanvasBackgroundColor: {
                            color: '<?= $colors['color_chartbackground'] ?>',
                        },
                        legend: {
                            display: <?= $show_legende ?>,
                            position: 'bottom',
                            labels: {
                                filter: item => !item.text.includes('line'),
                                generateLabels: function (chart) {
                                    const visibleInverters = [<?= $visibleInvertersString ?>];
                                    return chart.data.inverters.map(function (inverter) {
                                        const vis = !visibleInverters.includes(inverter);
                                        let idx = findDatasetById(chart.data.datasets, inverter);
                                        return (
                                            {
                                                datasetIndex: idx,
                                                text: inverter,
                                                hidden: vis,
                                                fillStyle: chart.data.datasets[idx].myColor,
                                                strokeStyle: chart.data.datasets[idx].myColor,
                                            })
                                    })
                                }
                            },
                            onClick: rankingLegendClickHandler,
                        },
                        subtitle: {
                            display: true,
                            text: <?= $subtitle ?>,
                            padding: {top: 5, left: 0, right: 0, bottom: 3},
                        },
                    },
                    onClick: (event, elements, chart) => {
                        if (elements[0]) {
                            const i = elements[0].index;
                            const url = chart.data.datasets[0].data[i].url;
                            if (url.length > 0) {
                                location.href = url;
                            }
                        }
                    }
                },
                plugins: [getPlugin()],
            });
        }
    )

</script>