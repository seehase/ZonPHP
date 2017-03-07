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
         FROM " . $table_prefix . "_sensordata
         WHERE sensorid= $sensorid AND sensortype = $sensortype
         ORDER BY logtime DESC
         LIMIT 1";

    $result_sensor = mysqli_query($con, $sql_sensor) or die("Query failed. dag " . mysqli_error($con));
    if (mysqli_num_rows($result_sensor) != 0) {
        while ($row = mysqli_fetch_array($result_sensor)) {
            // only one result
            $sensor["value"] = $row["val"];
            $sensor["logtime"] = $row["logtime"];
        }
    }
}
unset($sensor);
//-----------------------------------------------------------------------------------------
// var_dump($allsensors);

//-----------------------------------------------------------------------------------------
?>

<?php $strgeg = "";

echo('<div class="eenblad">');
echo("<h1>" . $id . "</h1>");
echo '</div>
      <div id="sensor_chart_' . $id . '" style="width:400px; height:300px; margin-top:10px; float: left; top:50%; left:50%" title = "holger">	';

echo '<table style="margin :auto; background-color: #999"><tbody>';
echo '<tr> <th>Sensor</th> <th>Time</th> <th>Value</th> </tr>';
foreach ($allsensors as &$sensor) {
    $unit = "";
    if ($sensor["type"] == 1) {
        $unit = "°C";
    }
    if ($sensor["type"] == 3) {
        $unit = "RH%";
    }

    $logtime = strtotime($sensor["logtime"]);

    echo '<tr style="height: 22pt;">';
    echo '<td width=140 style="text-align: left;  font-size: 12pt; color: #' . $sensor["color"] . '; padding-left: 5px;">' . $sensor["label"] . '</td>';
    echo '<td width=90 style="text-align: center; font-size: 12pt;">' . strftime("%H:%M:%S", $logtime) . '</td>';
    echo '<td width=120 style="text-align: right; font-size: 12pt; padding-right: 5px;"><strong>' . number_format($sensor["value"], 2, ',', '.') . ' ' . $unit . '</strong></td>';
    echo '</tr>';

}
unset($sensor);
echo "</div>";

?>


