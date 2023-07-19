<?php
global $con, $params;
$Uhrzeit = 0;
$FehlerUhrzeit = 0;
$DC1 = 0;
$TagesErtragPrufung = 0;
$TagesErtrag = 0;

$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag
	WHERE Naam ='" . $_SESSION['plant'] . "' 
	ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));
$dateTime = STARTDATE;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

//echo "Ausgelesenes Datum aus SQL=". $dateTime;
$Jahr = (date("Y", strtotime($dateTime)));// Aktuelles Jahr 2013 (Y) Aktelles Monat (m) Akteller Tag (d); Ymd -->
$Tag = (date("d", strtotime($dateTime)));
$Monat = (date("n", strtotime($dateTime)));
if ($Tag == 31 && $Monat == 12) {
    $Jahr++;
}

$directory = ROOT_DIR . "/" . $_SESSION['plant'] . '/';
$directoryImport = ROOT_DIR . "/" . $_SESSION['plant'] . '/' . $Jahr . '/';
//echo "directory=".$directory."<br />";
//echo "directoryimport=".$directoryImport."<br />";

for ($tel = 0; $tel <= 160; $tel++) {//2009-12-19.csv
    $num = (date("Ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
    //echo $num."<br />";
    if (file_exists($directoryImport . $num . '.csv')) {
        //echo $directory.$num.$stHVL_suo.'.suo<br />';
        $adag[] = $directoryImport . $num . '.csv';
        //echo "Datei gefunden=".$directoryImport .$num.'.csv';
    }
}

if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $begindagEtotaal = 0;
        $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $stringtest = $string;


        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_solar = fgets($file, 1024);
            $geg_solar = trim($geg_solar);
            if ($teller < 2) {
                //Datum aus Datei auslesen
                //echo"Datum aus Datei auslesen";
                $line = explode(" ", $geg_solar);
                $dat = $line[1];
                //echo "Ausgelesenes Datum".$dat."<br />";//26.01.2013
                $dat = date("Y-m-d", strToTime($dat));// Datumsformat ändern auf YYYY-MM-DD
                //echo "Ausgelesenes Datum, Format geändert".$dat."<br />";
            }


            if ($teller > 7) {// Von Welcher Zeile gelesen wird
                if (!empty($geg_solar)) {
                    $alist = array();
                    $alist = explode(";", $geg_solar);//echo '<pre>'.print_r($alist, true).'</pre>';

                    //$Pac = str_replace(array(','), '.', $alist[21]);//echo $Pac."---";
                    $AC1 = str_replace(array(','), '.', $alist[13]);//Aktuelle Leistun W von String 1
                    $AC2 = str_replace(array(','), '.', $alist[16]);//Aktuelle Leistun W von String 2
                    $AC3 = str_replace(array(','), '.', $alist[19]);//Aktuelle Leistun W von String 3
                    $Pac = $AC1 + $AC2 + $AC3;
                    $DC1 = str_replace(array(','), '.', $alist[2]);//Aktuelle DC Strom V von String 1

                    //echo $Pac."---";

                    //$TagesErtragPrufung=$TagesErtrag;

                    $DaySum = str_replace(array(','), '.', $alist[25]);//echo $DaySum."<br />";
                    //$TagesErtrag=str_replace(array(','), '.', $alist[24]);//echo $DaySum."<br />";


                    if ($teller == 8)
                        $begindagEtotaal = $DaySum;
                    //$Datum=$alist[0];
                    $UhrPrufung = $Uhrzeit;
                    $Uhrzeit = $alist[0];
                    if ($Pac != 0) {
                        //Prüfung ob Aktuelle Leistung "Null" ist, wenn Ja erfolgt kein Eintrag in die Datenbank
                        //echo "Pac ist leer= ".$Pac." ".$Uhrzeit."<br />";
                        //echo"Leistungs Prüfung ist OK - weiter gehts"."<br />";
                        //$Uhrzeit=$alist[0];
                        //$TagesErtragPrufung=$TagesErtrag;
                        $TagesErtrag = str_replace(array(','), '.', $alist[24]);//echo $DaySum."<br />";

//Prüfung ob eine Zeile mit dem Gleichen Datum und Uhrzeit existiert, wenn Ja Fehler bei der Uhrzeitprüfung
// sonst wird else (ganz normal weiter) ausgeführt.
// Ich hoffe es funktioniert, wenn nicht dann folgende Zeile löschen: 91 - 96	;eine Klammer } in 138.					
                        if ($Uhrzeit == $UhrPrufung) {
                            //echo "Fehler bei der Uhrzeitprufung="."<br />";
                            //echo"Prufung=".$UhrPrufung."<br />";
                            //echo"Uhrzeit=".$Uhrzeit."<br />";
                            $FehlerUhrzeit = 1;
                        } else {
                            //echo "Else wird ausgeführt";

                            $Datum = $dat . " " . $Uhrzeit;
                            //list($Datum,$Uhrzeit,$WR,$Pac,$DaySum,$Status,$Error,$Pdc1,$Udc,$Uac)=explode(";",$geg_solar);
                            //echo "Fertiges Datum".$Datum."<br />";
                            $oDatumTijd = ($Datum);
                            $odatum = explode(" ", $oDatumTijd);
                            if ((strtotime($oDatumTijd) > strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {
                                //$Pac=$alist[21];
                                //$DaySum=$alist[8];
                                $DaySum = ($DaySum - $begindagEtotaal) * $params['coefficient'];
                                $TagesErtrag = $TagesErtrag * $params['coefficient'];
                                /*	if ($TagesErtragPrufung>$TagesErtrag){//Prüfung ob TagesErtrag nicht null ist,wenn ja dann wird er auf den letzten Eintrag zurückgesetzt.
                                    echo "TagesErtragPrufung=$TagesErtragPrufung > TagesErtrag=$TagesErtrag"."<br />";
                                    echo "TagesErtrag wird auf TagesErtragPrufung gesetzt"."<br />";
                                    $TagesErtrag=$TagesErtragPrufung;
                                    }*/

                                $string .= "('" . $oDatumTijd . $_SESSION['plant'] . "','" . $oDatumTijd . "'," . $Pac . "," . $TagesErtrag . ",'" . $_SESSION['plant'] . "')";
                                $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . $TagesErtrag . ",'" . $_SESSION['plant'] . "')";
                                $stringdelete = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                                $teller2 = 0;
                                $stringtest = $string;
                                //echo "Gesammter String",$string."<br />"."<br />";
                                //echo "1. Feld ",$oDatumTijd.$_SESSION['plant']."<br />";
                                //echo "2, Feld ",$oDatumTijd."<br />";
                                //echo "3, Feld Aktelle Leistung= ",$Pac."<br />";
                                //echo "4, Feld Aktueller TagesErtrag= ",$TagesErtrag."<br />";
                                //echo "4, Feld Tagesgesammtsumme",$DaySum."<br />";
                                //echo "4, Feld Formartierte Tagessumme",number_format($DaySum,3)."<br />";// Formatieren funktioniert nicht
                                //echo "5, Feld ",$_SESSION['plant']."<br />"."<br />";
                            }
                        }
                    }
                }
            }
            $teller++;
        }
        fclose($file);

        if ($teller2 == 0) {
            //echo "<br />";echo $stringdelete;
            //echo "<br />";echo $string;
            //echo "<br />";echo $string1;
            //mysql_query($stringdelete)or die ('SQL Error stringdelete:'. mysql_error());
            //mysql_query($string)or die ('SQL Error string:'. mysql_error());
            //mysql_query($string1)or die ('SQL Error string1:'. mysql_error());

//added by AVB : WHERE Naam ='".$_SESSION['plant']."' 
            mysqli_query($con, "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Naam ='" . $_SESSION['plant'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string) or die("Query failed. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string1) or die("Query failed. ERROR: " . mysqli_error($con));
            //echo $oDatum;echo "<br />";
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
    //$date = str_replace(array('.', ' ', ':'), '/', $date);
    $date = str_replace(array('.', ' ', ':', '-'), '/', $date);
    //echo $date.'b<br />';
    $m_d_j_t = explode("/", $date);
    if (!isset($m_d_j_t[0])) return "geen datumtijd";
    if (!isset($m_d_j_t[1])) return "geen datumtijd";
    if (!isset($m_d_j_t[2])) return "geen datumtijd";
    if (!isset($m_d_j_t[3])) return "geen datumtijd";
    if (!isset($m_d_j_t[4])) return "geen datumtijd";
    //if(!isset($m_d_j_t[5]))	return "geen datumtijd";
    if (!is_numeric($m_d_j_t[0])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[1])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[2])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[3])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[4])) return "geen datumtijd";
    //if(!is_numeric($m_d_j_t[5]))	return "geen datumtijd";
    if (controledatum($m_d_j_t[1], $m_d_j_t[0], $m_d_j_t[2]))
        if (checktime($m_d_j_t[3], $m_d_j_t[4], 0))
            return $m_d_j_t[2] . "-" . $m_d_j_t[0] . "-" . $m_d_j_t[1] . " " . $m_d_j_t[3] . ":" . $m_d_j_t[4] . ":" . "00";
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}
