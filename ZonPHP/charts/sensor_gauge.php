<?php

if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "Parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}

$val_c_dif = 100;
$val_rh_dif = 100;
$today = time();
$todaystring = strftime("%Y-%m-%d", $today);


$id = $todaystring;
// get sensorID's from URL
$allsensors = array();
$urlparams = "";
$params = "";
if (isset($_GET['sensors'])) {
    $params = html_entity_decode($_GET['sensors']);
} else if (isset($_POST['sensors'])) {
    $params = $_POST['sensors'];
}


$urlparams = "&amp;sensors=" . $params;
$tmp = explode(",", $params);  // $tmp = "197190:1:innen:#33cc33"  (id, type, name, color
$cnt = 0;
foreach ($tmp as $val) {
    $tmparry = explode(":", $val);
    $sensor = array();
    $sensor["id"] = $tmparry[0];
    $sensor["type"] = $tmparry[1];
    $sensor["label"] = $tmparry[2];
    $sensor["color"] = $tmparry[3];
    $allsensors[$cnt] = $sensor;
    $cnt++;
}

$title = "";
if (isset($_GET['title'])) {
    $title = $_GET['title'];
    $urlparams .= "&amp;title=" . $title;
}


$sensorid = $allsensors[0]["id"];
$sensortype = $allsensors[0]["type"];

//-----------------------------------------------------------------------------------------
// select last values for each sensor

foreach ($allsensors as &$sensor) {
    $sensor_value = 0.;
    $sensorid = $sensor["id"];
    $sensortype = $sensor["type"];
    $geengevdag = 0;

    $sql_sensor =
        "SELECT 
            measurevalue  AS val,            
            logtime as logtime
         FROM " . $table_prefix . "_sensordata_temp
         WHERE sensorid= $sensorid AND sensortype = $sensortype
         ORDER BY logtime DESC
         LIMIT 1";

    $sensor["value"] = 0.0;
    $sensor["logtime"] = time();
    $sensor["minvalue"] = 0.0;
    $sensor["maxvalue"] = 0.0;
    $result_sensor = mysqli_query($con, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
    if (mysqli_num_rows($result_sensor) != 0) {
        while ($row = mysqli_fetch_array($result_sensor)) {
            // fetch latest value only one result
            if (($row["val"] != null) & ($row["logtime"] != null)) {
                $sensor["value"] = $row["val"];
                $sensor["logtime"] = $row["logtime"];
                $sensor["minvalue"] = $row["val"];
                $sensor["maxvalue"] = $row["val"];
            }
        }
    }

    $sql_sensor =
        "SELECT 
            min(measurevalue)  AS min,   
            max(measurevalue) AS max    
         FROM " . $table_prefix . "_sensordata_temp
         WHERE sensorid= $sensorid AND sensortype = $sensortype AND
         (logtime < \"$todaystring 23:59:59\" and logtime > \"$todaystring 00:00:00\")";

    $result_sensor = mysqli_query($con, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
    if (mysqli_num_rows($result_sensor) != 0) {
        while ($row = mysqli_fetch_array($result_sensor)) {
            // only one result
            if ($row["min"] != null) {
                $sensor["minvalue"] = $row["min"];
            }
            if ($row["max"] != null) {
                $sensor["maxvalue"] = $row["max"];
            }
        }
    }
}
unset($sensor);


//-----------------------------------------------------------------------------------------
// var_dump($allsensors);
$indoor = number_format($allsensors[0]["value"], 1);
$cellar = number_format($allsensors[1]["value"], 1);
$outdoor = number_format($allsensors[2]["value"], 1);
$indoorrh = $allsensors[3]["value"];
$cellarrrh = $allsensors[4]["value"];
$outdoorrh = $allsensors[5]["value"];

//-----------------------------------------------------------------------------------------
?>

<?php $strgeg = "";


echo '

<div class="sensorgauge" style="float: left; padding-top: 13px; text-align:center; font-size:12px;">
    <div style="float: none"><strong>Winterg.</strong><br />' . strftime("%H:%M:%S", strtotime($allsensors[0]["logtime"])) . ' </div>
    <div id="gaugeContainer1" style="float: none; margin-left: 11px;"></div>
    <div style="float: none; text-align:center; font-size:10px;">' . $indoor . '°C</div>
</div>

<div class="sensorgauge" style="float: left; padding-top: 13px; text-align:center; font-size:12px;">
    <div style="float: none"><strong>Cellar</strong> <br />' . strftime("%H:%M:%S", strtotime($allsensors[1]["logtime"])) . ' </div>
    <div id="gaugeContainer2" style="margin-left: 11px;"></div>
    <div style="float: none; text-align:center; font-size:10px;">' . $cellar . '°C</div>
</div>
<div class="sensorgauge" style="float: left; padding-top: 13px; text-align:center; font-size:12px;">
    <div style="float: none"><strong>Loft</strong> <br />' . strftime("%H:%M:%S", strtotime($allsensors[2]["logtime"])) . ' </div>    
    <div id="gaugeContainer3" style="margin-left: 3px;"></div>
    <div style="float: none; text-align:center; font-size:10px;">' . $outdoor . '°C</div>
</div>

<div class="sensorgauge" id="container-speed" style="width: 200px; height: 120px; float: left; ">Wintergarden</div>
<div class="sensorgauge" id="container-cellar" style="width: 200px; height: 120px; float: left; ">Cellar</div>
<div class="sensorgauge" id="container-rpm" style="width: 200px; height: 120px; float: left; ">Loft</div>

';


?>

<script type="text/javascript">

    $(function () {
        var labels = {position: 'near', offset: 10, interval: 1};

        $('#gaugeContainer1').jqxLinearGauge({
            width: 70,
            max:  <?php echo intval($allsensors[0]["maxvalue"]) ?> +2,
            min:  <?php echo intval($allsensors[0]["minvalue"]) ?> -2,
            pointer: {pointerType: 'arrow', size: '20%', visible: true},
            // pointer: {size: '5%'},
            labels: {position: 'near', offset: 10, interval: 1},
            colorScheme: 'scheme02',
            background: {visible: false},
            ticksMajor: {size: '10%', interval: 1},
            ticksMinor: {size: '5%', interval: 0.5, style: {'stroke-width': 1, stroke: '#aaaaaa'}},
            rangeSize: '2%',
            ranges: [{
                startValue: <?php echo $allsensors[0]["minvalue"] ?>,
                endValue: <?php echo $allsensors[0]["maxvalue"] ?>,
                style: {
                    fill: '#FFA200',
                    stroke: '#FFA200'
                }
            }],

            value: 0
        });

        $('#gaugeContainer1').jqxLinearGauge('value', <?php echo $indoor ?>);
        $('#gaugeContainer2').jqxLinearGauge({
            width: 70,
            max:  <?php echo intval($allsensors[1]["maxvalue"]) ?> +1,
            min:  <?php echo intval($allsensors[1]["minvalue"]) ?> -1,
            labels: {position: 'near', offset: 10, interval: 1},
            pointer: {size: '5%'},
            pointer: {pointerType: 'arrow', size: '20%', visible: true},
            colorScheme: 'scheme02',
            background: {visible: false},
            ticksMajor: {size: '10%', interval: 2},
            ticksMinor: {size: '5%', interval: 1, style: {'stroke-width': 1, stroke: '#aaaaaa'}},
            rangeSize: '2%',
            ranges: [{
                startValue: <?php echo $allsensors[1]["minvalue"] ?>,
                endValue: <?php echo $allsensors[1]["maxvalue"] ?>,
                style: {
                    fill: '#FFA200',
                    stroke: '#FFA200'
                }
            }],
            value: 0
        });

        $('#gaugeContainer2').jqxLinearGauge('value', <?php echo $cellar ?>);
        $('#gaugeContainer3').jqxLinearGauge({
            width: 70,
            max:  <?php echo intval($allsensors[2]["maxvalue"]) ?> +3,
            min:  <?php echo intval($allsensors[2]["minvalue"]) ?> -3,
            labels: {position: 'near', offset: 10, interval: 5},
            pointer: {size: '5%'},
            pointer: {pointerType: 'arrow', size: '20%', visible: true},
            colorScheme: 'scheme02',
            background: {visible: false},
            ticksMajor: {size: '10%', interval: 2},
            ticksMinor: {size: '5%', interval: 1.0, style: {'stroke-width': 1, stroke: '#aaaaaa'}},
            rangeSize: '2%',
            ranges: [{
                startValue: <?php echo $allsensors[2]["minvalue"] ?>,
                endValue: <?php echo $allsensors[2]["maxvalue"] ?>,
                style: {
                    fill: '#FFA200',
                    stroke: '#FFA200'
                }
            }],

            value: 0
        });
        $('#gaugeContainer3').jqxLinearGauge('value', <?php echo $outdoor ?>);


        var gaugeOptions = {
            chart: {
                type: 'solidgauge',
                backgroundColor: '#<?php echo $colors['color_chartbackground'] ?>',
            },

            title: null,
            pane: {
                center: ['50%', '90%'],
                size: '135%',
                startAngle: -90,
                endAngle: 90,
                background: {
                    backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || '#EEE',
                    innerRadius: '60%',
                    outerRadius: '100%',
                    shape: 'arc'
                }
            },
            tooltip: {
                enabled: false
            },
            exporting: {
                buttons: {
                    contextButton: {
                        enabled: false,
                    }
                }
            },
            yAxis: {
                stops: [
                    [0.1, '#55BF3B'], // green
                    [0.5, '#DDDF0D'], // yellow
                    [0.9, '#DF5353'] // red
                ],
                lineWidth: 0,
                minorTickInterval: null,
                tickAmount: 2,
                title: {
                    y: -40,
                },
                labels: {
                    y: 15,
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'},
                }
            },
            plotOptions: {
                solidgauge: {
                    dataLabels: {
                        y: 5,
                        borderWidth: 0,
                        useHTML: true
                    }
                }
            }
        };

        var chartSpeed = Highcharts.chart('container-speed', Highcharts.merge(gaugeOptions, {
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Wintergarden',
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'},
                },
                visible: true,
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'humitiy',
                data: [<?php echo $indoorrh ?>],
                dataLabels: {
                    y: -38,
                    format: '<div style="text-align:center"><span style="font-size:15px;' +
                        'color: #<?php echo $colors['color_chart_text_subtitle'] ?> ; font-weight:normal;">{y:.1f}<\/span><br/>' +
                        '<span style="font-size:10px; color:#<?php echo $colors['color_chart_text_subtitle'] ?>; font-weight:normal;">%RH<\/span><\/div>'
                },
                tooltip: {
                    valueSuffix: ' km/h'
                }
            }]
        }));

        var chartRpm = Highcharts.chart('container-cellar', Highcharts.merge(gaugeOptions, {
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Cellar',
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'},
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'humidity',
                data: [<?php echo $cellarrrh ?>],
                dataLabels: {
                    y: -38,
                    format: '<div style="text-align:center"><span style="font-size:15px;' +
                        'color: #<?php echo $colors['color_chart_text_subtitle'] ?> ; font-weight:normal;">{y:.1f}<\/span><br/>' +
                        '<span style="font-size:10px; color:#<?php echo $colors['color_chart_text_subtitle'] ?>; font-weight:normal;">%RH<\/span><\/div>'
                },
                tooltip: {
                    valueSuffix: ' revolutions/min'
                }
            }]

        }));

        var chartRpm = Highcharts.chart('container-rpm', Highcharts.merge(gaugeOptions, {
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Loft',
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'},
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'humidity',
                data: [<?php echo $outdoorrh ?>],
                dataLabels: {
                    y: -38,
                    format: '<div style="text-align:center"><span style="font-size:15px;' +
                        'color:#<?php echo $colors['color_chart_text_subtitle'] ?> ; font-weight:normal;">{y:.1f}<\/span><br/>' +
                        '<span style="font-size:10px; color:#<?php echo $colors['color_chart_text_subtitle'] ?>; font-weight:normal;">%RH<\/span><\/div>'
                },
                tooltip: {
                    valueSuffix: ' revolutions/min'
                }
            }]

        }));

    });

</script>


