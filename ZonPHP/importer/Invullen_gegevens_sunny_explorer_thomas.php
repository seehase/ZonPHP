<?php

function ReadUnicodeFile($fn)
{
    $fc = "";
    $fh = fopen($fn, "rb") or die("Cannot open file for read: $fn<br>\n");
    $flen = filesize($fn);
    $bc = fread($fh, $flen);
    for ($i = 0; $i < $flen; $i++) {
        $c = substr($bc, $i, 1);
        if ((ord($c) != 0) && (ord($c) != 13))
            $fc = $fc . $c;
    }
    if ((ord(substr($fc, 0, 1)) == 255) && (ord(substr($fc, 1, 1)) == 254))
        $fc = substr($fc, 2);
    return ($fc);
}


$sql = "SELECT *
	FROM " . $table_prefix . "_dag 
	WHERE Naam ='" . $_SESSION['Wie'] . "'
	ORDER BY Datum_Dag DESC LIMIT 1";
// get latest import date from db
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0)
    $dateTime = $dstartdatum;
else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

$directory = "" . $_SESSION['Wie'] . '/'; //sunnyexplorer/Mijn PV-installatie 1-20091129.csv

$adag = array();
for ($tel = 0; $tel <= 160; $tel++) {
    $currentdate = (date("Ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
    if ($currentdate < "20170517") {
        $num = (date("Y-m-d", strtotime("+" . $tel . " day", strtotime($dateTime))));
        $fn = $directory . "Energie_und_Leistung_Tag_" . $num . '.csv';
    } else {
        $num = (date("Ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
        $fn = $directory . $param['plantname'] . "-" . $num . '.csv';
    }

    $fn = mb_convert_encoding($fn, "UTF-8");
    if (file_exists($fn)) {
        $adag[] = $fn;
    }
}

if (!empty($adag)) {
    foreach ($adag as $v) {
        $isoldformat = strpos($v, "Energie") > 0;
        $teller = 1;
        if ($isoldformat) $teller = 10;
        $string1 = "";
        $start = 0;
        $startend = 0;
        $houdeeindwaarde = 0;
        $stringend = "";
        $voegnulltoe = 0;
        $string = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";

        $fileraw = ReadUnicodeFile($v);
        $filecontent = explode("\n", $fileraw);

        $GridMsTotW = 0.0;
        foreach ($filecontent as $geg_suo) {
            $geg_suo = trim($geg_suo);
            // skip first 10 lines of CSV file

            if ($teller > 10) {
                if (!empty($geg_suo)) {
                    if ($isoldformat) {
                        list($mytime, $MeteringDykWh) = explode(";", $geg_suo);    //,$rest
                        $startpos = strpos($v, "Tag_");
                        $myDateStr = substr($v, $startpos + 4, 10);
                        $myDate = strtotime($myDateStr);
                        $myDateStr = strftime("%d.%m.%Y", $myDate);
                        $TimeStamp = $myDateStr . " " . $mytime;
                        $MeteringDykWh = str_replace(array(','), '.', $MeteringDykWh);
                        $GridMsTotW = $GridMsTotW + ($MeteringDykWh/60*15);
                    } else {
                        list($TimeStamp, $GridMsTotW1, $MeteringDykWh1, $GridMsTotW2, $MeteringDykWh2) = explode(";", $geg_suo);    //,$rest

                        $MeteringDykWh1 = str_replace(array(','), '.', $MeteringDykWh1);
                        $GridMsTotW1 = str_replace(array(','), '.', $GridMsTotW1);
                        $MeteringDykWh2 = str_replace(array(','), '.', $MeteringDykWh2);
                        $GridMsTotW2 = str_replace(array(','), '.', $GridMsTotW2);

                        $MeteringDykWh = $MeteringDykWh1 + $MeteringDykWh2;
                        $GridMsTotW = $GridMsTotW1 +  $GridMsTotW2;

                    }
                    $oTimeStamp = omzetdatum($TimeStamp);      //date("Y-m-d H:i:s",strtotime($TimeStamp));
                    if ($start == 0) {
                        $startkw = $GridMsTotW;
                        $start = 1;
                    }
                    if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                        if ($MeteringDykWh != 0) {
                            if ($MeteringDykWh == 0) {
                                $startend = 1;
                                $odatum = explode(" ", $oTimeStamp);
                                $stringend .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['Wie'] . "'),";
                                $string1 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $param['coefficient'], 3) . ",'" . $_SESSION['Wie'] . "')";
                            } else {
                                $odatum = explode(" ", $oTimeStamp);
                                if ($startend == 0) {
                                    $string .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['Wie'] . "'),";
                                } else {
                                    $string .= $stringend . "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . ($MeteringDykWh * 1000) . "," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['Wie'] . "'),";
                                }
                                $string1 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                    $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $param['coefficient'], 3) . ",'" . $_SESSION['Wie'] . "')";
                                $stringend = "";
                                $startend = 0;
                                $houdeeindwaarde = 1;
                                $voegnulltoe = 0;
                            }
                        } else {
                            if ($houdeeindwaarde != 0 && $voegnulltoe == 0) {
                                $string .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW - $startkw, 3) . ",'" . $_SESSION['Wie'] . "'),";
                                $voegnulltoe = 1;
                            }
                        }
                    }
                }
            }
            $teller++;
        }
        if ($string1 != "") {
            $string = substr($string, 0, -1);
            mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string) or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string1) or die("Query failed. ERROR: " . mysqli_error($con));

        }
    }
}

/******************************************************************************
 * maak months.js bestand aan
 * ****************************************************************************
 */
$sql = "SELECT MAX(Datum_Maand) AS maxi,SUM(Geg_Maand) AS som
	FROM " . $table_prefix . "_maand
	WHERE Naam='" . $_SESSION['Wie'] . "'
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
	FROM " . $table_prefix . "_maand
	WHERE Naam='" . $_SESSION['Wie'] . "'
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
    If (!checkdate($imaand, $idag, $ijaar)) {
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