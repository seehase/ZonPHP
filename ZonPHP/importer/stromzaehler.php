<?php

//
//include "Parameters.php";
//include "sessionstart.php";
//include "startup.php";
$Zeitumstellungaktiv = 0;
$Durchlauf1 = "1";
$leereintragerstellt = 0;
$Jahr = (date("Y"));// Aktuelles Jahr 2013 (Y)
$lastJahr = $Jahr - 1;
$Uhrzeit = 0;
$Uhrzeitexport = 0;
$FehlerUhrzeit = 0;
$UhrPrufung = 0;
$DC1 = 0;
$TagesErtragPrufung = 0;
$TagesErtrag = 0;
$Jahrlastenrty = $lastJahr . "-12-31 23:59";//Datum und Uhrzeit vom letzten Eintrag im Jahr
$Impulse = 400; //400;//Wieviele Impulse werden Pro KW ausgegeben?? Standard 400
$pfad = "stromzaehler"; // Pfad zu den Logdaten; ohne abschliessendes "/"
$directory = $_SESSION['Wie'] . '/';

if ($directory == "import/" or $directory == "export/" or $directory == "20KW-Fronius/") {// Prüfen ob Session für Stromzähler ist
    //echo "Import oder Export - Stromzähler ist aktiv"."<br />";
    if ($directory == "import/") include "importstromzaehler.php";
    if ($directory == "export/") include "exportstromzaehler.php";
    if ($directory == "20KW-Fronius/") include "import20KW-Fronius.php";

} else {

    ?>

    <?php
    $sql = "SELECT *
	FROM " . $table_prefix . "_dag
	WHERE Naam ='" . $_SESSION['Wie'] . "' 
	ORDER BY Datum_Dag DESC LIMIT 1";
    //echo $sql;
    $result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysql_error());
    if (mysqli_num_rows($result) == 0)
        $dateTime = $dstartdatum;
    else {
        while ($row = mysqli_fetch_array($result)) {
            $dateTime = $row['Datum_Dag'];
        }
    }
    #echo $dateTime;
########## Startdatum für Stromzähler beim ersten import neu setzten!!!!
    //$dateTime ="2016-5-17";

//echo "Ausgelesenes Datum aus SQL= ". $dateTime."<br />";
    $Jahr = (date("Y"));// Aktuelles Jahr 2013 (Y) Aktelles Monat (m) Akteller Tag (d); Ymd -->

//echo " Aktuelles Jahr aus Date Y= ".$Jahr."<br />";

    $Jahrsql = date("Y", strToTime($dateTime));// Datumsformat ändern auf YYYY
    $Jahrsqllast = date("Y-m-d H:i", strToTime($dateTime));// Datumsformat ändern auf YYYY-MM-DD HH:mm
//echo " Aktuelles Jahr SQL= ".$Jahrsql."<br />";
//echo " Aktueller letzter Eintrag SQL= ".$Jahrsqllast."<br />";
//echo "Letzter Eintrag laut Variable ".$Jahrlastenrty."<br />";
    $Jahrlastenrty = $Jahrsql . "-12-31 23:59";//Datum und Uhrzeit vom letzten Eintrag im Jahr

    if ($Jahrsql == $Jahr) {
//echo "SQL Jahr ist gleich wie aktuelles Jahr ".$Jahrsql."=".$Jahr."<br />";
//echo " Jahr bleibt gleich ".$Jahr."<br />";
        $Jahr = $Jahr;    //später aktivieren!!

    } else {

//echo "SQL Jahr ist kleiner als aktuelles Jahr ".$Jahrsql."=".$Jahr."<br />";
        $Jahr2 = $Jahrsql;
        if ($Jahrsqllast > $Jahrlastenrty) { //letzter Eintrag im Jahr

            //echo " Letzter SQL Eintrag ist größer als ".$Jahrsqllast." > ".$Jahrlastenrty."<br />";
            $Jahr2 = $Jahrsql + 1;
            //echo " JahrSQL plus 1= ".$Jahr2."<br />";

        }
        $Jahr = $Jahr2;    //später aktivieren!!
    }
//echo "Jahr für import ".$Jahr."<br />";
//$Jahr="2200";  //nur für Testzwecke damit nichts importiert wird
    $directory = $_SESSION['Wie'] . '/';
    $directoryImport = $pfad . '/import/';
    $directoryExport = $pfad . '/export/';

//echo "directory=".$directory."<br />";
//echo "directoryimport=".$directoryImport."<br />";

    for ($tel = 0; $tel <= 60; $tel++) {//2009-12-19.csv
        $num = (date("Y-m-d", strtotime("+" . $tel . " day", strtotime($dateTime))));
        //echo $num."<br />";
        if (file_exists($directoryImport . $num . '.csv')) {
            //echo $directory.$num.$stHVL_suo.'.suo<br />';
            $adag[] = $directoryImport . $num . '.csv';

            //echo "Datei gefunden=".$directoryImport .$num.'.csv';
        }
    }
    ?>

    <?php
    if (!empty($adag)) {
        foreach ($adag as $v) {
            $teller = 1;
            $teller2 = 1;
            $begindagEtotaal = 0;
            $string = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
            $stringtest = $string;

############################################
#Verzeichnis Teilen

            $var = explode("/", $v);
            $verzeichnis0 = $var[0];
            $verzeichnis1 = $var[1];
// $verzeichnis2 = $var[2];
// $verzeichnis3 = $var[3];
// $verzeichnis4 = $var[4];
            $dateiname6 = $var[2];

#Ende Verzeichnis teilen

#Verzeichnis export zusammensetzen	
            // $exportv = $verzeichnis0."/".$verzeichnis1."/".$verzeichnis2."/".$verzeichnis3."/".$verzeichnis4."/export/".$dateiname6;//Verzeichnis für export
            $exportv = $verzeichnis0 . "/export/" . $dateiname6;//Verzeichnis für export

            //echo $v."<br />"; //Verzeichnis für import
            //echo $exportv."<br />";// fertiges Verzeichnis Export

            $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
            $file1 = fopen($exportv, "r") or die ("Kan " . $exportv . " niet openen");// Datei für Export wird zum lesen geöffnet
            while (!feof($file)) {
                $geg_solar = fgets($file, 1024);
                $geg_solar = trim($geg_solar);
                $geg_solar1 = fgets($file1, 1024);
                $geg_solar1 = trim($geg_solar1);


                if ($teller > 0) {// Von Welcher Zeile gelesen wird
                    if (!empty($geg_solar)) {
                        $alist = array();//import
                        $alist = explode(";", $geg_solar);//echo '<pre>'.print_r($alist, true).'</pre>';

                        $alist1 = array();//export
                        $alist1 = explode(";", $geg_solar1);//echo '<pre>'.print_r($alist, true).'</pre>';

                        $dat = $alist[0];
                        $Uhrzeit = $alist[1];
                        $AC1 = str_replace(array(','), '.', $alist[2]);//Import
                        if (sizeof($alist1) < 3) {
                            $Uhrzeitexport = $alist[1];
                            $AC2 = 0.0;//Export
                        } else {
                            $Uhrzeitexport = $alist1[1];
                            $AC2 = str_replace(array(','), '.', $alist1[2]);//Export
                        }
                        $Pac = $AC1 / $Impulse;
                        $AktuelleLeistung = $Pac * 1000 * 60; // Aktuelle Leistung in von KW in Watt umrechnen
                        //$AktuelleLeistunganzeige = $AktuelleLeistung;

                        $AC2 = "-" . $AC2;    //minus Wert erstellen für Export
                        $Pac1 = $AC2 / $Impulse;
                        $AktuelleLeistung1 = $Pac1 * 1000 * 60; // Aktuelle Leistung in von KW in Watt umrechnen

                        $Pac = $Pac + $Pac1; // Wert für Stromzähler berechnen
                        $AktuelleLeistung = $AktuelleLeistung + $AktuelleLeistung1;

                        //echo $Uhrzeitexport." Import=".$AktuelleLeistunganzeige." Export= ".$AktuelleLeistung1." --- Import - Export = ".$AktuelleLeistung." "."<br />";// ACHTUNG bei aktivierung weiter oben die $AktuelleLeistunganeuge aktivieren!!


                        //echo "aktueller Ertrag um ".$dat." ".$Uhrzeit." = ".$AC1." Impulse = berechnet ".$Pac." kw"."<br />";

                        //$TagesErtragPrufung=$TagesErtrag;

                        $TagesErtrag = $Pac + $TagesErtrag;
                        //$TagesErtrag=str_replace(array(','), '.', $alist[24]);//echo $DaySum."<br />";
                        //echo "Tagesertrag= ".$TagesErtrag."<br />";

                        if ($teller == 0)
                            #$begindagEtotaal=$DaySum;
                            $begindagEtotaal = $TagesErtrag;
                        //$Datum=$alist[0];
                        #$UhrPrufung=$Uhrzeit;

                        #######Zeitumstellung

                        //echo "Datum = ".$dat."<br />";
                        //echo "Uhrzeit = ".$Uhrzeit."<br />";

                        //echo date("I", strtotime($dat))."<br />";

                        $zdat = explode("-", $dat);
                        $zjahr = $zdat[0];
                        $zmon = $zdat[1];
                        $ztag = $zdat[2];

                        $zmontag = $zmon . $ztag;

                        $start_str = date('d.m.', dst_start($zjahr)); //Ende der Winterzeit
                        $end_str = date('md', dst_end($zjahr));// Ende der Sommerzeit


//echo "Im Jahr $zjahr begann die Sommerzeit am $start_str und endete am $end_str.<br />"; 
//echo $zmontag."<br />";

                        if ($end_str == $zmontag) { // Prüfen wann Zeitumstellung aktiv werden soll

                            //echo "Zeitumstellung aktiv!! Im Jahr $zjahr begann die Sommerzeit am $start_str und endete am $end_str.<br />";


                            $ztime = explode(":", $Uhrzeit);
                            $zstd = $ztime[0];
                            $zmin = $ztime[1];

                            $zstdmin = $zstd . $zmin;
                            //echo $zstdmin."<br />";
                            if ($Zeitumstellungaktiv == 1) {
                                //echo "Zeitumstellung aktiv ist 1 und Zeit = ".$Uhrzeit."<br />";

                                $Uhrzeit = $Uhrzeit . ":01";
                                //echo "Uhrzeit neu = ".$Uhrzeit."<br />";

                                if ($zstdmin == "0259") {
                                    //echo "Zeitumstellung wird deaktiviert"."<br />";
                                    $Zeitumstellungaktiv = 0;
                                    $Durchlauf1 = "0";
                                }
                            }

                            if ($zstdmin == "0259" and $Durchlauf1 == "1") {
                                //echo "Zeitumstellung wird aktiviert"."<br />";
                                $Zeitumstellungaktiv = 1;
                            }
                        }

                        #######Ende Zeitumstellung#####


                        if ($Pac == "-1" or $leereintragerstellt == 0) {//Prüfung ob Aktuelle Leistung "Null" ist, wenn Ja erfolgt kein Eintrag in die Datenbank //--- Dies wurde mit -1 statt 0 deaktiviert da es beim Stromzähler auch im Winter nie den Wert null geben kann!!
                            //echo "Pac ist leer= ".$Pac." ".$Uhrzeit."<br />";
                            ##############################################
                            if ($leereintragerstellt == 0) {
                                $Datum = $dat . " " . $Uhrzeit;
                                $oDatumTijd = ($Datum);
                                if ((strtotime($oDatumTijd) <= strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {
                                    //echo "Kein LeerEintrag"."<br />";
                                    $leereintragerstellt = 1;
                                } else {

//echo "Leereintrag erfolgt für den Tag ".$dat.$oDatumTijd.$dateTime."<br />";
                                    $Null = 0;
#$stringtagleer="insert into ".$table_prefix."_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values('".$dat." 12:00:02".$_SESSION['Wie']."','".$dat." 12:00:02"."',".$Null.",".$Null.",'".$_SESSION['Wie']."')ON DUPLICATE KEY UPDATE Geg_Dag=".$Null."";

#$stringmaandleer="insert into ".$table_prefix."_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('".$dat.$_SESSION['Wie']."','".$dat."',".$Null.",'".$_SESSION['Wie']."') ON DUPLICATE KEY UPDATE Geg_Maand=".$Null."";


                                    $leereintragerstellt = 1;
                                }
                            }


                        } else {                    //echo"Leistungs Prüfung ist OK - weiter gehts"."<br />";
                            //$TagesErtragPrufung=$TagesErtrag;
                            #$TagesErtrag=$Pac+$TagesErtrag;;//echo $DaySum."<br />";

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
                                ###################################
                                /*
                                If ($leereintragerstellt==0){
                                        if((strtotime($oDatumTijd)<=strtotime($dateTime))and ($oDatumTijd!="geen datumtijd")){echo "Kein LeerEintrag"."<br />";
                                        $leereintragerstellt=1;
                                        }else{

                        echo "Leereintrag erfolgt für den Tag ".$dat.$oDatumTijd.$dateTime."<br />";
                        $Null=0;
                        $stringtagleer="insert into ".$table_prefix."_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values('".$dat." 12:00:02".$_SESSION['Wie']."','".$dat." 12:00:02"."',".$Null.",".$Null.",'".$_SESSION['Wie']."')ON DUPLICATE KEY UPDATE Geg_Dag=".$Null."";

                        $stringmaandleer="insert into ".$table_prefix."_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('".$dat.$_SESSION['Wie']."','".$dat."',".$Null.",'".$_SESSION['Wie']."') ON DUPLICATE KEY UPDATE Geg_Maand=".$Null."";


                            $leereintragerstellt=1;
                        #$string="INSERT INTO ".$table_prefix."_maand(IndexMaand,Datum_Maand,Geg_Maand,Naam) VALUES ('".$TimeStamp.$xls_file.$Naam."','".$TimeStamp.$xls_file."',".$kWh.",'".$Naam."') ON DUPLICATE KEY UPDATE Geg_Maand=".$kWh."";


                                #echo "String - Tag leer - ".$stringtagleer."<br />";
                                #echo "String - Maand leer - ".$stringmaandleer."<br />";
                                #mysql_query("DELETE FROM ".$table_prefix."_maand WHERE Naam ='".$_SESSION['Wie']."' AND Datum_Maand='".$dat."'") or die("Query failed. ERROR: ".mysql_error());
                                #$datloschen="2015-01-24";
                                #mysql_query("DELETE FROM ".$table_prefix."_dag WHERE Naam ='".$_SESSION['Wie']."' AND Datum_Dag='".$datloschen."'") or die("Query failed. ERROR: ".mysql_error());
                                mysql_query($stringtagleer) or die("Query failed. ERROR: ".mysql_error());
                                mysql_query($stringmaandleer) or die("Query failed. ERROR: ".mysql_error());
                                        }
                                }


                                */

                                ##########################

                                if ((strtotime($oDatumTijd) > strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {

                                    #$DaySum=($DaySum-$begindagEtotaal)*$param['coefficient'];
                                    //echo "BeginnDagTotaal= ".$begindagEtotaal;
                                    $TagesErtrag = ($TagesErtrag - $begindagEtotaal) * $param['coefficient'];
                                    /*	if ($TagesErtragPrufung>$TagesErtrag){//Prüfung ob TagesErtrag nicht null ist,wenn ja dann wird er auf den letzten Eintrag zurückgesetzt.
                                        echo "TagesErtragPrufung=$TagesErtragPrufung > TagesErtrag=$TagesErtrag"."<br />";
                                        echo "TagesErtrag wird auf TagesErtragPrufung gesetzt"."<br />";
                                        $TagesErtrag=$TagesErtragPrufung;
                                        }*/


                                    if ($teller2 == 1) {
                                        $string .= "('" . $oDatumTijd . $_SESSION['Wie'] . "','" . $oDatumTijd . "'," . $AktuelleLeistung . "," . $TagesErtrag . ",'" . $_SESSION['Wie'] . "')";
                                        $string1 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . $TagesErtrag . ",'" . $_SESSION['Wie'] . "')";
                                        $stringdelete = "DELETE FROM " . $table_prefix . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                                        $teller2 = 0;
                                        $stringtest = $string;
                                        //echo "Gesammter String",$string."<br />"."<br />";
                                        //echo "1. Feld ",$oDatumTijd.$_SESSION['Wie']."<br />";
                                        //echo "2, Feld ",$oDatumTijd."<br />";
                                        //echo "3, Feld Aktelle Leistung= ",$Pac."<br />";
                                        //echo "4, Feld Aktueller TagesErtrag= ",$TagesErtrag."<br />";
                                        //echo "4, Feld Tagesgesammtsumme",$DaySum."<br />";
                                        //echo "4, Feld Formartierte Tagessumme",number_format($DaySum,3)."<br />";// Formatieren funktioniert nicht
                                        //echo "5, Feld ",$_SESSION['Wie']."<br />"."<br />";
                                    } else {
                                        $string .= ",('" . $oDatumTijd . $_SESSION['Wie'] . "','" . $oDatumTijd . "'," . $AktuelleLeistung . "," . $TagesErtrag . ",'" . $_SESSION['Wie'] . "')";
                                        $string1 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . $TagesErtrag . ",'" . $_SESSION['Wie'] . "')";
                                        $stringtest = $string;
                                        //echo "Gesammter String",$string."<br />"."<br />";
                                        //echo "1. Feld ",$oDatumTijd.$_SESSION['Wie']."<br />";
                                        //echo "2, Feld ",$oDatumTijd."<br />";
                                        //echo "3, Feld Aktelle Leistung= ",$Pac."<br />";
                                        //echo "4, Feld Aktueller TagesErtrag= ",$TagesErtrag."<br />";
                                        //echo "4, Feld Tagesgesammtsumme",$DaySum."<br />";
                                        //echo "4, Feld Formartierte Tagessumme",number_format($DaySum,3)."<br />";// Formatieren funktioniert nicht
                                        //echo "5, Feld ",$_SESSION['Wie']."<br />"."<br />";

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
                //echo "<br />";echo $stringdelete;
                //echo "<br />";echo $string;
                //echo "<br />";echo $string1;
                #mysql_query($stringdelete)or die ('SQL Error stringdelete:'. mysql_error());
                #mysql_query($string)or die ('SQL Error string:'. mysql_error());
                #mysql_query($string1)or die ('SQL Error string1:'. mysql_error());
                //echo "Standardeintrag erfolgt."."<br />";
                $leereintragerstellt = 0;

//added by AVB : WHERE Naam ='".$_SESSION['Wie']."' 
                #mysql_query("DELETE FROM ".$table_prefix."_maand WHERE Naam ='".$_SESSION['Wie']."' AND Datum_Maand='".$odatum[0]."'") or die("Query failed. ERROR: ".mysql_error());
                $stringdelete = "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'";

                //echo $oDatum;echo "<br />";

                mysqli_query($con, $stringdelete) or die ('SQL Error stringdelete:' . mysql_error());
                mysqli_query($con, $string) or die ('SQL Error string: Doppelter Eintrag. Vielleicht haben Sie das Startdatum nicht zur&uuml;ckgesetzt!' . mysql_error());
                mysqli_query($con, $string1) or die ('SQL Error string1:' . mysql_error());

                //echo "Tagesertrag zurückstellen"."<br />";
                $TagesErtrag = 0; // Tagesertrag zurückstellen

            }
        }
    }
} // Stromzähler
?>

<?php
/******************************************************************************
 * maak months.js bestand aan
 * ****************************************************************************
 */
/*
$sql="SELECT MAX(Datum_Maand) AS maxi,SUM(Geg_Maand) AS som
   FROM ".$table_prefix."_maand
   WHERE Naam='".$_SESSION['Wie']."'
   GROUP BY DATE_FORMAT(Datum_Maand,'%y-%m')
   ORDER BY Datum_Maand DESC";
   $result = mysql_query($sql) or die("Query failed. ERROR: ".mysql_error());
   if (mysql_num_rows($result)==0){
       $dateTime="Leeg";
   }
   else {
       $fp = fopen($directory.'months.js',"w+");
       while($row = mysql_fetch_array($result)){
           $datfile=date("d.m.y",strtotime($row['maxi']));
             $dateTime = 'mo[mx++]="'.$datfile."|".floor($row['som']*1000).'"';//echo $dateTime; //datum omzetten
           //$fp = fopen('months1.js',"r+");
           fwrite($fp, $dateTime."\r\n");
       }
       fclose($fp);
   }

/******************************************************************************
* maak days_hist.js bestand aan
* ****************************************************************************
*/
/*
$sql="SELECT *
   FROM ".$table_prefix."_maand
   WHERE Naam='".$_SESSION['Wie']."'
   ORDER BY Datum_Maand DESC";
   $result = mysql_query($sql) or die("Query failed. ERROR: ".mysql_error());
   if (mysql_num_rows($result)==0){
       $dateTime="Leeg";
   }
   else {
       $fp = fopen($directory.'days_hist.js',"w+");
       while($row = mysql_fetch_array($result)){
           $datfile=date("d.m.y",strtotime($row['Datum_Maand']));
           //echo $datfile."<br />";
             $dateTime = 'da[dx++]="'.$datfile."|".floor($row['Geg_Maand']*1000).';1000"';//echo $dateTime; //datum omzetten
           //$fp = fopen('months1.js',"r+");
           fwrite($fp, $dateTime."\r\n");
       }
       fclose($fp);
   }
   */
?>

<?php
function omzetdatum1($date)
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
    if (!is_numeric($m_d_j_t[1])) return "geen datumtijd";;
    if (!is_numeric($m_d_j_t[2])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[3])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[4])) return "geen datumtijd";
    //if(!is_numeric($m_d_j_t[5]))	return "geen datumtijd";
    if (controledatum1($m_d_j_t[1], $m_d_j_t[0], $m_d_j_t[2]))
        if (checktime1($m_d_j_t[3], $m_d_j_t[4], 0))
            return $m_d_j_t[2] . "-" . $m_d_j_t[0] . "-" . $m_d_j_t[1] . " " . $m_d_j_t[3] . ":" . $m_d_j_t[4] . ":" . "00";
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}

?>

<?php
function controledatum1($idag, $imaand, $ijaar)
{
    //echo $idag.$imaand.$ijaar;
    if (!checkdate($imaand, $idag, $ijaar)) {
        return false;
    } else {
        return true;
    }
}

?>

<?php
function checktime1($hour, $minute, $second)
{
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
        return true;
    }
}

?>

<?php
### Funktion zur Berechnung wann Sommer und Winterzeit beginnt
function dst_start($year)
{
    return mktime(2, 0, 0, 3, 31 - date('w', mktime(2, 0, 0, 3, 31, $year)), $year);
}

function dst_end($year)
{
    return mktime(2, 0, 0, 10, 31 - date('w', mktime(2, 0, 0, 10, 31, $year)), $year);
}

?> 