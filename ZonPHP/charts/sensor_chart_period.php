<?php
if (strpos(getcwd(), "charts") > 0) {
    chdir("../");
    include_once "Parameters.php";
    include_once "inc/sessionstart.php";
    include_once "inc/load_cache.php";
}

$sensotable = "_sensordata";
$val_c_dif = 100;
$val_rh_dif = 100;
$startdate = time();
$startdatestring = strftime("%Y-%m-%d", $startdate);
$enddate = strtotime("+1 day", $startdate);
$enddatestring = strftime("%Y-%m-%d", $enddate);

if (isset($_GET['start'])) {
    $startdatestring = html_entity_decode($_GET['start']);
    $startdate = strtotime($startdatestring);
    $startdatestring = strftime("%Y-%m-%d 00:00:00", $startdate);
}
if (isset($_GET['end'])) {
    $enddatestring = html_entity_decode($_GET['end']);
    $enddate = strtotime($enddatestring);
    $enddatestring = strftime("%Y-%m-%d 23:59:59", $enddate);
}

$isIndexPage = false;
if (isset($_POST['action']) && ($_POST['action'] == "indexpage")) {
    $isIndexPage = true;
}

$id = "";
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// get sensorID's from URL
$allsensors = array();
$urlparams = "";
$params = "";
if (isset($_GET['sensors'])) {
    $params = html_entity_decode($_GET['sensors']);
} else if (isset($_POST['sensors'])) {
    $params = $_POST['sensors'];
}


    // goto index if on parameters are specified
    if (strlen($params) == 0) {
        echo '<script type="text/javascript">';
        echo 'window.location.href="index.php";';
        echo '</script>';
        echo '<noscript>';
        ///echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
        exit();
        // die(header('location:index.php'));  // does not work because of header modification with echo "..."
    }



$interval = 60;
if (isset($_GET['interval'])) {
    $interval = intval($_GET['interval']);
} else if (isset($_POST['interval'])) {
    $interval = intval($_POST['interval']);
}
$interval = $interval * 60;  // * 60min


$linetype = "avg";
if (isset($_GET['linetype'])) {
    $linetype = $_GET['linetype'];
} else if (isset($_POST['linetype'])) {
    $linetype = $_POST['linetype'];
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

//-----------------------------------------------------------------------------------------
// get first and last day of values from db
$sensorid = $allsensors[0]["id"];
$sensortype = $allsensors[0]["type"];

$sqlminmax = "SELECT MAX(logtime)AS maxi,MIN(logtime)AS mini
	FROM " . $table_prefix . $sensotable . "
	WHERE sensorid=" . $sensorid . " AND sensortype = " . $sensortype;
$resultminmax = mysqli_query($con, $sqlminmax) or die("Query failed. dag-minmax " . mysqli_error($con));
while ($row = mysqli_fetch_array($resultminmax)) {
    if (is_null($row['mini'])) {
        $dminimum = strtotime('2038-01-01 00:00:00');
        $dmaximum = strtotime('1990-01-01 00:00:00');
    } else {
        $dminimum = strtotime($row['mini']);
        $dmaximum = strtotime($row['maxi']);
    }
}
//-----------------------------------------------------------------------------------------

//-----------------------------------------------------------------------------------------
// select values for a day grouped/avagage every 5min for 1 sensor

foreach ($allsensors as &$sensor) {
    $sensor_values = array();
    $temp_vals = array();
    $sensorid = $sensor["id"];
    $sensortype = $sensor["type"];
    $geengevdag = 0;

    $sql_sensor =
        "
        SELECT FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(logtime)/ $interval) * $interval - ($time_offset)) AS nicedate,
        $linetype(measurevalue) as val
        FROM " . $table_prefix . $sensotable . "         
        WHERE (logtime  >  \"$startdatestring\" AND logtime < \"$enddatestring\" ) AND sensorid= $sensorid AND sensortype = $sensortype
        GROUP BY nicedate
        
        ";

   //  var_dump($sql_sensor);

$x=
        "
            SELECT
               AVG( measurevalue ) AS val,
               STR_TO_DATE( CONCAT( DATE( logtime ) ,  ' ',HOUR( logtime ) , ':', LPAD( FLOOR( MINUTE( logtime ) / $interval ) * $interval, 2, '0' ) , ':00' ) ,
                   '%Y-%m-%d %H:%i:%s' ) AS nicedate
            FROM " . $table_prefix . $sensotable . " 
            WHERE (logtime  >  \"$startdatestring\" AND logtime < \"$enddatestring\" ) AND sensorid= $sensorid AND sensortype = $sensortype
            GROUP BY nicedate ORDER BY nicedate ASC";

    $result_sensor = mysqli_query($con, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
    if (mysqli_num_rows($result_sensor) != 0) {
        while ($row = mysqli_fetch_array($result_sensor)) {
            // array time = value
            $sensor_values[strtotime($row['nicedate'])] = $row['val'];
            $temp_vals[strtotime($row['nicedate'])] = $row['val'];
        }
        $geengevdag = 1;
    }
    $sensor["values"] = $sensor_values;
    $str_temp_vals = "";
    foreach ($temp_vals as $time => $val) {
        $str_temp_vals .= "[" . $time * 1000 . ", " . number_format($val, 1, '.', '') . " ],";
    }
    $str_temp_vals = substr($str_temp_vals, 0, -1);
    $sensor["newvaluestring"] = $str_temp_vals;

}
unset($sensor);


//-----------------------------------------------------------------------------------------
// var_dump($allsensors);

//-----------------------------------------------------------------------------------------
?>

<?php $strgeg = "";
$str_data = "";
$str_ticks = "";
$str_tmpticks = "";
$cnt = 0;
$str_sensor_vals = array();

$val_c_min = 500;
$val_c_max = -500;
$val_c_dif = 100;

$val_rh_min = 100;
$val_rh_max = 0;
$val_rh_dif = 100;
foreach ($allsensors as &$sensor) {
    $val_min = 500;
    $val_max = -500;
    $val_avg = 0;

    $currentval = $sensor["values"];
    $str_current_vals = "";
    $cnt = 0;
    $avg_cnt = 0;
    foreach ($currentval as $time => $val) {
        $cnt++;
        $str_current_vals .= "[" . $time * 1000 . ", " . $val . " ], ";

        if (strlen($val) > 0) {
            // clac min/max/avg
            $avg_cnt++;
            if ($val > $val_max) $val_max = $val;
            if ($val < $val_min) $val_min = $val;
            $val_avg += $val;
        }
        if ($str_ticks == "") {
            if ($cnt % 24 == 1) {
                $str_tmpticks .= '[' . $time * 1000 . ',"' . $time * 1000 . '"],';
            }
        }
    }
    $str_ticks = substr($str_tmpticks, 0, -1);
    if ($avg_cnt == 0) $avg_cnt = 1;
    $sensor["valuestring"] = substr($str_current_vals, 0, -1);
    $sensor["val_min"] = $val_min;
    $sensor["val_max"] = $val_max;
    $sensor["val_avg"] = $val_avg / $avg_cnt;
    $sensor["val_dif"] = abs($val_max - $val_min);
    if ($sensor["type"] == 1) {
        if ($sensor["val_dif"] < $val_c_dif) {
            $val_c_dif = $sensor["val_dif"];
        }
        if ($sensor["val_min"] < $val_c_min) {
            $val_c_min = $sensor["val_min"];
        }
        if ($sensor["val_max"] > $val_c_max) {
            $val_c_max = $sensor["val_max"];
        }
    }
    if ($sensor["type"] == 3) {
        if ($sensor["val_dif"] < $val_rh_dif) {
            $val_rh_dif = $sensor["val_dif"];
        }
        if ($sensor["val_min"] < $val_rh_min) {
            $val_rh_min = $sensor["val_min"];
        }
        if ($sensor["val_max"] > $val_rh_max) {
            $val_rh_max = $sensor["val_max"];
        }
    }
}
unset($sensor);

// ---------------------------------------------------------------------------

if ($isIndexPage == true) {
    echo '<div id="sensor_chart_period_' . $id . '" class = "smallCharts" style="width:400px; height:300px; margin-top:30px; float: left"></div>';
}

// build javascript string
$js = "";

foreach ($allsensors as $sensor) {
    $valuestring = $sensor["newvaluestring"];
    $label = $sensor["label"];
    $color = $sensor["color"];
    $val_min = $sensor["val_min"];
    $val_max = $sensor["val_max"];
    $val_avg = $sensor["val_avg"];
    $yaxis = 1;
    $unit = "C°";
    if ($sensor["type"] == 3) {
        $yaxis = 2;
        $unit = "%RH";
    }

    $js .=
        "
        {
            type: 'spline',
            name: \"$label\",
            data: [$valuestring],
        },     
        ";
}


$idstring = "sensor_chart_period_$id"
?>

<script type="text/javascript">

    $(function () {

        var mychart = new Highcharts.chart('<?php echo $idstring ?>', {
            chart: {
                zoomType: 'x',
                backgroundColor: '#888888',
                alignThresholds: false,
            },
            title: {
                text: 'Temperature',
                style: {
                    color: '#<?php echo $colors['color_chart_text_title'] ?>',
                },
            },
            subtitle: {
                text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in',
                style: {
                    color: '#<?php echo $colors['color_chart_text_subtitle'] ?>',
                },
            },
            xAxis: {
                type: 'datetime',
                style: {
                    color: '#<?php echo $colors['color_chart_labels_xaxis1'] ?>',
                },
            },
            yAxis: [
                { // Watt
                    title: {
                        text: 'Temp',
                        style: {
                            color: '#<?php echo $colors['color_chart_title_yaxis1'] ?>'
                        },
                        visible: false,
                    },
                    labels: {
                        format: '{value}°C',
                        style: {
                            color: '#<?php echo $colors['color_chart_labels_yaxis1'] ?>',
                        },
                    },
                    gridLineColor: '#<?php echo $colors['color_chart_gridline_yaxis1'] ?>',
                }
            ],
            legend: {
                enabled: true,
            },
            credits: {
                enabled: false
            },
            tooltip: {
                crosshairs: [true],
                shared: true,
            },
            reflow: true,
            plotOptions: {

                spline: {
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    marker: {
                        enabled: false,
                        radius: 2,
                    },
                    threshold: 0,
                },
            },

            series: [
                <?php echo $js ?>
            ]
        });

        $("#<?php echo $idstring ?>").resize(function () {
            mychart.reflow();
        });


    });


</script>