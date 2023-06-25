<?php

$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag 
	WHERE Naam ='" . $_SESSION['plant'] . "'
	ORDER BY Datum_Dag DESC LIMIT 1";
// get latest import date from db
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0)
    $dateTime = STARTDATE;
else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

//$directory = "" . $_SESSION['plant'] . '/'; //sunnyexplorer/Mijn PV-installatie 1-20091129.csv
$directory = ROOT_DIR . "/SolarlogData/";


$aday = array();
for ($tel = 0; $tel <= 160; $tel++) {
    $num = (date("Ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
    if (file_exists($directory . $params[$_SESSION['plant']]['importPrefix'] . "-" . $num . '.csv')) {
        $adag[] = $directory . $params[$_SESSION['plant']]['importPrefix'] . "-" . $num . '.csv';
    }
}

if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $string1 = "";
        $string12 = "";
        $start = 0;
        $startend = 0;
        $houdeeindwaarde = 0;
        $stringend = "";
        $voegnulltoe = 0;
        $string_vals = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $string_vals2 = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";

        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_suo = fgets($file, 1024);
            $geg_suo = trim($geg_suo);
            // skip first 10 lines of CSV file
            if ($teller > 10) {
                if (!empty($geg_suo)) {

                    $p = explode(";", $geg_suo);
                    list($TimeStamp, $GridMsTotW, $MeteringDykWh, $GridMsTotW2, $MeteringDykWh2) = explode(";", $geg_suo);    //,$rest
                    $oTimeStamp = omzetdatum($TimeStamp);      //date("Y-m-d H:i:s",strtotime($TimeStamp));
                    $MeteringDykWh = str_replace(array(','), '.', $MeteringDykWh);
                    $GridMsTotW = str_replace(array(','), '.', $GridMsTotW);
                    $MeteringDykWh2 = str_replace(array(','), '.', $MeteringDykWh2);
                    $GridMsTotW2 = str_replace(array(','), '.', $GridMsTotW2);
                    if ($start == 0) {
                        $startkw = $GridMsTotW;
                        $startkw2 = $GridMsTotW2;
                        $start = 1;
                    }
                    if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                        if ($MeteringDykWh != 0) {
                            if ($MeteringDykWh == 0) {
                                $startend = 1;
                                $odatum = explode(" ", $oTimeStamp);
                                $stringend .= "('" . $oTimeStamp . "WR1" . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . "WR1" . "'),";
                                $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    "WR1" . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $params['coefficient'], 3) . ",'" . "WR1" . "')";
                            } else {
                                $odatum = explode(" ", $oTimeStamp);
                                if ($startend == 0) {
                                    $string_vals .= "('" . $oTimeStamp . "WR1" . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . "WR1" . "'),";
                                } else {
                                    $string_vals .= $stringend . "('" . $oTimeStamp . "WR1" . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . "WR1" . "'),";
                                }
                                $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    "WR1" . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $params['coefficient'], 3) . ",'" . "WR1" . "')";
                                $stringend = "";
                                $startend = 0;
                                $houdeeindwaarde = 1;
                                $voegnulltoe = 0;
                            }
                        } else {
                            if ($houdeeindwaarde != 0 && $voegnulltoe == 0) {
                                $string_vals .= "('" . $oTimeStamp . "WR1" . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW - $startkw, 3) . ",'" . "WR1" . "'),";
                                $voegnulltoe = 1;
                            }
                        }
                    }

                    if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                        $houdeeindwaarde2 = 0;
                        $startend2 = 0;

                        if ($MeteringDykWh2 != 0) {
                            if ($MeteringDykWh2 == 0) {
                                $startend2 = 1;
                                $odatum = explode(" ", $oTimeStamp);
                                $stringend2 .= "('" . $oTimeStamp . "WR2" . "','" . $oTimeStamp . "'," . ($MeteringDykWh2 * 1000) . "," . number_format($GridMsTotW2 - $startkw2, 3) . ",'" . "WR2" . "'),";
                                $string12 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    "WR2" . "','" . $odatum[0] . "'," . number_format(($GridMsTotW2 - $startkw2) * $params['coefficient'], 3) . ",'" . "WR2" . "')";
                            } else {
                                $odatum = explode(" ", $oTimeStamp);
                                if ($startend2 == 0) {
                                    $string_vals2 .= "('" . $oTimeStamp . "WR2" . "','" . $oTimeStamp . "'," . ($MeteringDykWh2 * 1000) . "," . number_format($GridMsTotW2 - $startkw2, 3) . ",'" . "WR2" . "'),";
                                } else {
                                    $string_vals2 .= $stringend . "('" . $oTimeStamp . "WR2" . "','" . $oTimeStamp . "'," . ($MeteringDykWh2 * 1000) . "," . number_format($GridMsTotW2 - $startkw2, 3) . ",'" . "WR2" . "'),";
                                }
                                $string12 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    "WR2" . "','" . $odatum[0] . "'," . number_format(($GridMsTotW2 - $startkw2) * $params['coefficient'], 3) . ",'" . "WR2" . "')";
                                $stringend2 = "";
                                $startend2 = 0;
                                $houdeeindwaarde2 = 1;
                                $voegnulltoe2 = 0;
                            }
                        } else {
                            if ($houdeeindwaarde2 != 0 && $voegnulltoe == 0) {
                                $string_vals2 .= "('" . $oTimeStamp . "WR2" . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW - $startkw, 3) . ",'" . "WR2" . "'),";
                                $voegnulltoe = 1;
                            }
                        }
                    }
                }
            }
            $teller++;
        }
        fclose($file);
        if ($string1 != "") {
            $string_vals = substr($string_vals, 0, -1);
            mysqli_query($con, "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Naam ='" . "WR1" . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string_vals) or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string1) or die("Query failed. ERROR: " . mysqli_error($con));

        }
        if ($string12 != "") {
            $string_vals2 = substr($string_vals2, 0, -1);
            mysqli_query($con, "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Naam ='" . "WR2" . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string_vals2) or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string12) or die("Query failed. ERROR: " . mysqli_error($con));

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

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));

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

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));

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


function omzetdatum($date)
{
    $date = str_replace(array('.', '/', '-', ' ', ':'), '/', $date);

    $d_m_j_t = explode("/", $date);
    if (!isset($d_m_j_t[0])) return "geen datumtijd";
    if (!isset($d_m_j_t[1])) return "geen datumtijd";
    if (!isset($d_m_j_t[2])) return "geen datumtijd";
    if (!isset($d_m_j_t[3])) return "geen datumtijd";
    if (!isset($d_m_j_t[4])) return "geen datumtijd";
    $d_m_j_t[5] = "00";
    if (!isset($d_m_j_t[5])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[0])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[1])) return "geen datumtijd";;
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

function controledatum($idag, $imaand, $ijaar)
{
    if (!checkdate($imaand, $idag, $ijaar)) {
        return false;
    } else {
        return true;
    }
}

function checktime($hour, $minute, $second)
{
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
        return true;
    }
}

?>