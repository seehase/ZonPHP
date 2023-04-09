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

$title = "";
if (isset($_GET['title'])) {
    $title = $_GET['title'];
    $urlparams .= "&amp;title=" . $title;
}


//-----------------------------------------------------------------------------------------
// select last values for each sensor
if (isset($use_weewx) && $use_weewx == true) {

$sql_sensor =
    "SELECT *,                        
            from_unixtime(dateTime) as logtime
         FROM archive         
         ORDER BY logtime DESC
         LIMIT 1";

$value = array();
$result_sensor = mysqli_query($con_weewx, $sql_sensor) or die("Query failed. dag " . mysqli_error($con_weewx));
if (mysqli_num_rows($result_sensor) != 0) {
    while ($row = mysqli_fetch_array($result_sensor)) {
        // fetch latest value only one result
        $logtime = $row["logtime"];
        $value['inTemp'] = number_format($row["inTemp"], 2);
        $value['outTemp'] = number_format($row["outTemp"], 2);
        $value['inHumidity'] = number_format($row["inHumidity"], 1);
        $value['outHumidity'] = number_format($row["outHumidity"], 1);
        $value['pressure'] = number_format($row["pressure"], 2);
        $value['barometer'] = number_format($row["barometer"], 2);
        $value['altimeter'] = number_format($row["altimeter"], 2);
        $value['windSpeed'] = number_format($row["windSpeed"], 2);
        $value['windDir'] = number_format($row["windDir"], 2);
        $value['rainRate'] = number_format($row["rainRate"], 2);
        $value['rain'] = number_format($row["rain"], 2);
        $value['rain_total'] = number_format($row["rain_total"], 2);
        $value['UV'] = number_format($row["UV"], 2);
        $value['uv_raw'] = number_format($row["uv_raw"], 2);
        $value['dewpoint'] = number_format($row["dewpoint"], 2);
        $value['windchill'] = number_format($row["windchill"], 2);
        $value['heatindex'] = number_format($row["heatindex"], 2);
        $value['ET'] = number_format($row["ET"], 2);
        $value['radiation'] = number_format($row["radiation"], 2);
        $value['luminosity'] = $row["luminosity"];

    }

    // convert units
    $value['inTemp'] = number_format(($value['inTemp'] - 32) * 5 / 9, 1); // F --> °C
    $value['outTemp'] = number_format(($value['outTemp'] - 32) * 5 / 9, 1); // F --> °C
    $value['pressure'] = number_format(($value['pressure'] / 0.0295299830714), 1);  // inHG --> mBar
    $value['barometer'] = number_format(($value['barometer'] / 0.0295299830714), 1);// inHG --> mBar
    $value['altimeter'] = number_format(($value['altimeter'] / 0.0295299830714), 1);// inHG --> mBar
    $value['windSpeed'] = number_format(($value['windSpeed'] * 1.60934), 1);  // miles --> km
    $value['luminosity'] = number_format(($value['luminosity'] / 126.7), 1);  // lux --> radiation
}

//-----------------------------------------------------------------------------------------


//-----------------------------------------------------------------------------------------

    echo '
        <div class="sensorgauge" style="float: left; padding-top: 13px; text-align:center; font-size:12px;">
            <div style="float: none"><strong>Indoor</strong><br />' . strftime("%H:%M:%S", strtotime($logtime)) . ' </div>
            <div id="weewxGaugeContainer1" style="float: none; margin-left: 11px;"></div>
            <div style="float: none; text-align:center; font-size:10px;">' . $value['inTemp'] . '°C</div>
        </div>

        <div class="sensorgauge" style="float: left; padding-top: 13px; text-align:center; font-size:12px;">
            <div style="float: none"><strong>Outdoor</strong> <br />' . strftime("%H:%M:%S", strtotime($logtime)) . ' </div>
            <div id="weewxGaugeContainer2" style="margin-left: 3px;"></div>
            <div style="float: none; text-align:center; font-size:10px;">' . $value['outTemp'] . '°C</div>
        </div>

        <div class="sensorgauge" id="wewwx-container-inHumidity" style="width: 140px; height: 100px; float: left; ">indoor</div>
        <div class="sensorgauge" id="wewwx-container-outHumidity" style="width: 140px; height: 100px; float: left; ">outdoor</div>
        ';

    echo '
        <div class="sensorgauge"  style="width: 200px;  float: left; ">pressure : ' . $value['pressure'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">barometer : ' . $value['barometer'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">altimeter : ' . $value['altimeter'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">windSpeed : ' . $value['windSpeed'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">windDir : ' . $value['windDir'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">rainRate : ' . $value['rainRate'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">rain : ' . $value['rain'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">rain_total : ' . $value['rain_total'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">UV : ' . $value['UV'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">uv_raw : ' . $value['uv_raw'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">dewpoint : ' . $value['dewpoint'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">windchill : ' . $value['windchill'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">heatindex : ' . $value['heatindex'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">ET : ' . $value['ET'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">radiation : ' . $value['radiation'] . ' </div>
        <div class="sensorgauge"  style="width: 200px;  float: left; ">luminosity : ' . $value['luminosity'] . ' </div>
    ';

}

?>

<script type="text/javascript">

    $(function () {
        var labels = {position: 'near', offset: 10, interval: 1};

        $('#weewxGaugeContainer1').jqxLinearGauge({
            width: 70,
            max: 40,
            min: 10,
            pointer: {pointerType: 'arrow', size: '20%', visible: true},
            labels: {position: 'near', offset: 10, interval: 10},
            colorScheme: 'scheme02',
            background: {visible: false},
            ticksMajor: {size: '10%', interval: 10},
            ticksMinor: {size: '5%', interval: 5, style: {'stroke-width': 1, stroke: '#aaaaaa'}},
            rangeSize: '2%',
            value: 0
        });
        $('#weewxGaugeContainer1').jqxLinearGauge('value', <?php echo $value['inTemp'] ?>);

        $('#weewxGaugeContainer2').jqxLinearGauge({
            width: 70,
            max: 40,
            min: -10,
            pointer: {pointerType: 'arrow', size: '20%', visible: true},
            labels: {position: 'near', offset: 10, interval: 10},
            colorScheme: 'scheme02',
            background: {visible: false},
            ticksMajor: {size: '10%', interval: 10},
            ticksMinor: {size: '5%', interval: 5, style: {'stroke-width': 1, stroke: '#aaaaaa'}},
            rangeSize: '2%',
            value: 0
        });
        $('#weewxGaugeContainer2').jqxLinearGauge('value', <?php echo $value['outTemp'] ?>);


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
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',},
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

        var chartInHumidity = Highcharts.chart('wewwx-container-inHumidity', Highcharts.merge(gaugeOptions, {
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Indoor',
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'},
                },
                visible: true,
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'humitiy',
                data: [<?php echo $value['inHumidity'] ?>],
                dataLabels: {
                    y: 15,
                    format: '<div style="text-align:center"><span style="font-size:12px;' +
                        'color: #<?php echo $colors['color_chart_text_subtitle'] ?> ; font-weight:normal;">{y:.1f}<\/span><br/>' +
                        '<span style="font-size:10px; color:#<?php echo $colors['color_chart_text_subtitle'] ?>; font-weight:normal;">%RH<\/span><\/div>'
                },
            }]
        }));

        var chartOutHumidity = Highcharts.chart('wewwx-container-outHumidity', Highcharts.merge(gaugeOptions, {
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Outdoor',
                    style: {color: '#<?php echo $colors['color_chart_text_subtitle'] ?>'},
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'humidity',
                data: [<?php echo $value['outHumidity'] ?>],
                dataLabels: {
                    y: 15,
                    format: '<div style="text-align:center"><span style="font-size:12px;' +
                        'color: #<?php echo $colors['color_chart_text_subtitle'] ?> ; font-weight:normal;">{y:.1f}<\/span><br/>' +
                        '<span style="font-size:10px; color:#<?php echo $colors['color_chart_text_subtitle'] ?>; font-weight:normal;">%RH<\/span><\/div>'
                },
            }]

        }));


    });

</script>
