<?php
global $con, $params;

$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag 
	ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));
$dateTime = STARTDATE;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}
//echo $dateTime;	
$adag = array();
$directory = ROOT_DIR . "/" . $_SESSION['plant'] . '/';//echo $directory;
for ($tel = 0; $tel <= 60; $tel++) {
    $num = (date("ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));//echo $num."<br />";
    if (file_exists($directory . "min" . $num . '.csv')) {
        //echo $directory.$num.$stHVL_suo.'.suo<br />';
        $adag[] = $directory . "min" . $num . '.csv';
    }
}

foreach ($adag as $v) {
    $teller = 1;
    $teller2 = 1;
    $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
    $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values";
    $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
    while (!feof($file)) {
        $geg_solar = fgets($file, 1024);
        $geg_solar = trim($geg_solar);
        //echo '<pre>'.print_r($geg_solar, true).'</pre>';
        if ($teller > 1) {
            if (!empty($geg_solar)) {
                $alist = array();
                $alist = explode(";", $geg_solar);
                //echo '<pre>'.print_r($geg_solar, true).'</pre>';
                //list($Datum,$Uhrzeit,$WR,$Pac,$DaySum,$Status,$Error,$Pdc1,$Udc,$Uac)=explode(";",$geg_solar);
                //$alist=explode(";",$geg_solar);
                if (isset($alist[4])) {
                    $Datum = $alist[0];
                    $Uhrzeit = $alist[1];
                    $oDatumTijd = omzetdatum($Datum . " " . $Uhrzeit);
                    $odatum = explode(" ", $oDatumTijd);
                    if ((strtotime($oDatumTijd) > strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {
                        if ($teller2 == 1) {
                            for ($i = 0; $i < count(PLANT_NAMES); $i++) {
                                $Pac = $alist[3 + ($i * 8)];
                                $DaySum = $alist[4 + ($i * 8)];
                                $DaySum = $DaySum * $params['coefficient'];
                                $string .= "('" . $oDatumTijd . PLANT_NAMES[$i] . "','" . $oDatumTijd . "'," . $Pac . "," . ($DaySum / 1000) . ",'" . PLANT_NAMES[$i] . "'),";
                                $string1 .= "('" . $odatum[0] . PLANT_NAMES[$i] . "','" . $odatum[0] . "'," . ($DaySum / 1000) . ",'" . PLANT_NAMES[$i] . "'),";
                                $stringdelete = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                                $teller2 = 0;
                            }
                        } else {
                            for ($i = 0; $i < count(PLANT_NAMES); $i++) {
                                $Pac = $alist[3 + ($i * 8)];
                                $DaySum = $alist[4 + ($i * 8)];
                                $DaySum = $DaySum * $params['coefficient'];
                                $string .= "('" . $oDatumTijd . PLANT_NAMES[$i] . "','" . $oDatumTijd . "'," . $Pac . "," . ($DaySum / 1000) . ",'" . PLANT_NAMES[$i] . "'),";
                            }
                        }
                    }
                }
            }
        }
        $teller++;
    }
    fclose($file);
    if ($teller2 == 0) {
        $string = substr($string, 0, -1);
        $string1 = substr($string1, 0, -1);
        //echo "<br />";echo $stringdelete;
        //echo "<br />";echo $string;
        //echo "<br />";echo $string1;
        mysqli_query($con, $stringdelete) or die ('SQL Error stringdelete:' . mysqli_error($con));
        mysqli_query($con, $string) or die ('SQL Error string:' . mysqli_error($con));
        mysqli_query($con, $string1) or die ('SQL Error string1:' . mysqli_error($con));
    }
}

/******************************************************************************
 * maak months.js bestand aan
 * ****************************************************************************
 */
$sql = "SELECT MAX(Datum_Maand) AS maxi,SUM(Geg_Maand) AS som
	FROM " . TABLE_PREFIX . "_maand
	WHERE Naam='" . $_SESSION['plant'] . "'
	GROUP BY DATE_FORMAT(Datum_Maand,'%y-%m')
	ORDER BY 1 DESC";
$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    $fp = fopen($directory . 'months.js', "w+");
    while ($row = mysqli_fetch_array($result)) {
        $datfile = date("d.m.y", strtotime($row['maxi']));
        $dateTime = 'mo[mx++]="' . $datfile . "|" . floor($row['som'] * 1000) . '"';//echo $dateTime; //datum omzetten
        //$fp = fopen('months1.js',"r+");
        fwrite($fp, $dateTime . "\r\n");
    }
    fclose($fp);
}

/******************************************************************************
 * maak days_hist.js bestand aan
 * ****************************************************************************
 */
$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_maand
	WHERE Naam='" . $_SESSION['plant'] . "'
	ORDER BY Datum_Maand DESC";
$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    $fp = fopen($directory . 'days_hist.js', "w+");
    while ($row = mysqli_fetch_array($result)) {
        $datfile = date("d.m.y", strtotime($row['Datum_Maand']));
        //echo $datfile."<br />";
        $dateTime = 'da[dx++]="' . $datfile . "|" . floor($row['Geg_Maand'] * 1000) . ';1000"';//echo $dateTime; //datum omzetten
        //$fp = fopen('months1.js',"r+");
        fwrite($fp, $dateTime . "\r\n");
    }
    fclose($fp);
}

function omzetdatum($date): string
{
    //echo $date.'b<br />';
    $date = str_replace(array('.', ' ', ':'), '/', $date);
    $d_m_j_t = explode("/", $date);
    if (!isset($d_m_j_t[0])) return "geen datumtijd";
    if (!isset($d_m_j_t[1])) return "geen datumtijd";
    if (!isset($d_m_j_t[2])) return "geen datumtijd";
    if (!isset($d_m_j_t[3])) return "geen datumtijd";
    if (!isset($d_m_j_t[4])) return "geen datumtijd";
    if (!isset($d_m_j_t[5])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[0])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[1])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[2])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[3])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[4])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[5])) return "geen datumtijd";
    if (controledatum($d_m_j_t[0], $d_m_j_t[1], $d_m_j_t[2]))
        if (checktime($d_m_j_t[3], $d_m_j_t[4], $d_m_j_t[5]))
            return $d_m_j_t[2] . "-" . $d_m_j_t[1] . "-" . $d_m_j_t[0] . " " . $d_m_j_t[3] . ":" . $d_m_j_t[4] . ":" . $d_m_j_t[5];
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}

