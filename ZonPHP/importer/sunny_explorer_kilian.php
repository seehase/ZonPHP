<?php

global $con, $params;
$save = $_SESSION['plant'];
$_SESSION['plant'] = "SEEHASE";

$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag 
	WHERE Naam ='" . $_SESSION['plant'] . "'
	ORDER BY Datum_Dag DESC LIMIT 1";
// get latest import date from db
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));

$dateTime = STARTDATE;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

$directory = ROOT_DIR . "/" . $_SESSION['plant'] . '/'; //sunnyexplorer/Mijn PV-installatie 1-20091129.csv


$adag = array();
$num_today = (date("Ymd", time()));
for ($tel = 0; $tel <= 160; $tel++) {
    $num = (date("Ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
    if ($num > $num_today) {
        // skip if date is in future
        break;
    }
    $filename = $directory . $params[$_SESSION['plant']]['importPrefix'] . "-" . $num . '.csv';
    if (file_exists($filename)) {
        $adag[] = $filename;
    }
}
$odatum = array();
if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $string1 = "";
        $start = 0;
        $startend = 0;
        $houdeeindwaarde = 0;
        $stringend = "";
        $voegnulltoe = 0;
        $startkw = 0.0;
        $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_suo = fgets($file, 1024);
            $geg_suo = trim($geg_suo);
            // skip first 10 lines of CSV file
            if ($teller > 10) {
                if (!empty($geg_suo)) {
                    $p = explode(";", $geg_suo);
                    if (count($p) > 2) {
                        list($TimeStamp, $GridMsTotW, $MeteringDykWh) = explode(";", $geg_suo);    //,$rest
                        $oTimeStamp = omzetdatum($TimeStamp);      //date("Y-m-d H:i:s",strtotime($TimeStamp));
                        $MeteringDykWh = str_replace(array(','), '.', $MeteringDykWh);
                        $GridMsTotW = str_replace(array(','), '.', $GridMsTotW);
                        if ($start == 0) {
                            $startkw = $GridMsTotW;
                            $start = 1;
                        }
                        if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                            if ($MeteringDykWh != 0) {
                                $odatum = explode(" ", $oTimeStamp);
                                if ($startend == 0) {
                                    $string .= "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['plant'] . "'),";
                                } else {
                                    $string .= $stringend . "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['plant'] . "'),";
                                }
                                $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    $_SESSION['plant'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $params['coefficient'], 3) . ",'" . $_SESSION['plant'] . "')";
                                $stringend = "";
                                $startend = 0;
                                $houdeeindwaarde = 1;
                                $voegnulltoe = 0;

                            } else {
                                if ($houdeeindwaarde != 0 && $voegnulltoe == 0) {
                                    $string .= "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['plant'] . "'),";
                                    $voegnulltoe = 1;
                                }
                            }
                        }
                    }
                }
            }
            $teller++;
        }
        fclose($file);
        if ($string1 != "") {
            $string = substr($string, 0, -1);
            $del = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Naam ='" . $_SESSION['plant'] . "' AND Datum_Maand='" . $odatum[0] . "'";
            mysqli_query($con, $del) or die("Query failed. ERROR1: " . $del . mysqli_error($con));
            mysqli_query($con, $string) or die("Query failed. ERROR2: " . $string . mysqli_error($con));
            mysqli_query($con, $string1) or die("Query failed. ERROR3: " . $string1 . mysqli_error($con));

        }
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

$result = mysqli_query($con, $sql) or die("Query failed. ERROR4: " . $sql . mysqli_error($con));

if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    if ($fp = fopen($directory . 'months.js', "w+")) {
        while ($row = mysqli_fetch_array($result)) {
            $datfile = date("d.m.y", strtotime($row['maxi']));
            $dateTime = 'mo[mx++]="' . $datfile . "|" . floor($row['som'] * 1000) . '"';//echo $dateTime; //datum omzetten
            //$fp = fopen('months1.js',"r+");
            fwrite($fp, $dateTime . "\r\n");
        }
        fclose($fp);
    }
}

/******************************************************************************
 * maak days_hist.js bestand aan
 * ****************************************************************************
 */
$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_maand
	WHERE Naam='" . $_SESSION['plant'] . "'
	ORDER BY Datum_Maand DESC";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR5: " . $sql . mysqli_error($con));

if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    if ($fp = fopen($directory . 'days_hist.js', "w+")) {
        while ($row = mysqli_fetch_array($result)) {
            $datfile = date("d.m.y", strtotime($row['Datum_Maand']));
            //echo $datfile."<br />";
            $dateTime = 'da[dx++]="' . $datfile . "|" . floor($row['Geg_Maand'] * 1000) . ';1000"';//echo $dateTime; //datum omzetten
            //$fp = fopen('months1.js',"r+");
            fwrite($fp, $dateTime . "\r\n");
        }
        fclose($fp);
    }
}

// -----------------------------------------------------------------------------------------------------------------------------
//
//
// -----------------------------------------------------------------------------------------------------------------------------

function omzetdatum($date): string
{
    $date = str_replace(array('.', '/', '-', ' ', ':'), '/', $date);

    $d_m_j_t = explode("/", $date);
    if (!isset($d_m_j_t[0])) return "geen datumtijd";
    if (!isset($d_m_j_t[1])) return "geen datumtijd";
    if (!isset($d_m_j_t[2])) return "geen datumtijd";
    if (!isset($d_m_j_t[3])) return "geen datumtijd";
    if (!isset($d_m_j_t[4])) return "geen datumtijd";
    $d_m_j_t[5] = "00";
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