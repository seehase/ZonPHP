<?php

// set default inverter
if (isset($_GET['naam']))
    $_SESSION['Wie'] = $_GET['naam'];

if ($debugmode) error_log("calling load_parameters");
// check if tables exists
$tablename = $table_prefix . "_parameters";
$result = mysqli_query($con, "SHOW TABLES LIKE '" . $tablename . "'");
$tables_exists = ($result->num_rows == 1);

if (!$tables_exists) {
    $_SESSION['install'] = "running";
}

$param = array();
$param['image1'] = "inc/image/image1.jpg";
$param['image2'] = "inc/image/image2.jpg";

$charts = array();

if (!isset($colors)) $colors = array();
$teller = 0;
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters";
$sNaamSaveDatabase = array();
$resultpar = mysqli_query($con, $sqlpar) or die(header('location:install/opstart_installatie.php?fout=table'));

if (mysqli_num_rows($resultpar) != 0) {
    while ($row = mysqli_fetch_array($resultpar)) {
        $var = $row['Variable'];
        $value = $row['Waarde'];
        // to arrays for Parameters, charts and Colors
        if (stripos($var, "color") === 0) {
            $colors[$var] = $value;
        } else if (stripos($var, "chart") === 0) {
            $charts[$var] = $value;
        } else {
            $param[$var] = $row['Waarde'];
        }
    }
    if (!isset($param['sNaamSaveDatabasest']) || strlen($param['sNaamSaveDatabasest']) < 2) {
        die(header('location:install/opstart_installatie.php?fout=parameter'));
    }

    foreach (explode(',', $param['sNaamSaveDatabasest']) as $key => $stringnaam) {
        $sNaamSaveDatabase[] = $stringnaam;
        $teller++;
    }
    foreach (explode(',', $param['ieffectief_kwpiekst']) as $key => $stringnaam)
        $ieffectief_kwpiek[] = $stringnaam;
    if ($teller > 1)
        $iveromvormers = 1;
    else
        $iveromvormers = 0;
    $dstartdatum = $param['jaar'] . "-" . $param['maand'] . "-" . $param['dag'];

    if (count($charts) == 0){
        $charts['chart_31days'] = 'on';
        $charts['chart_allyearoverview'] = 'on';
        $charts['chart_lastyearoverview'] = 'on';
        $charts['chart_monthoverview'] = 'on';
        $charts['chart_solar_temp'] = 'on';
        $charts['chart_weekoverview'] = 'on';
        $charts['chart_yearoverview'] = 'on';
    }

    $_SESSION['charts'] = $charts;
    $_SESSION['ieffectief_kwpiek'] = $ieffectief_kwpiek;
    $_SESSION['dstartdatum'] = $dstartdatum;
    $_SESSION['iveromvormers'] = $iveromvormers;
    $_SESSION['sNaamSaveDatabase'] = $sNaamSaveDatabase;
    $_SESSION['param'] = $param;
} else {

    die(header('location:install/opstart_installatie.php?fout=parameter'));
}


$anaam_wattpiek = array();
foreach ($sNaamSaveDatabase as $keynaam => $snaam) {
    $anaam_wattpiek[$snaam] = $ieffectief_kwpiek[$keynaam];
    if (!isset($_SESSION['Wie']))
        $_SESSION['Wie'] = $snaam;
}

$ieffectiefkwpiek = $anaam_wattpiek[$_SESSION['Wie']];
$_SESSION['ieffectiefkwpiek'] = $ieffectiefkwpiek;


?>
