<?php
global $con, $params;
$sql = "SELECT Datum_Dag,Geg_Dag 
	FROM " . TABLE_PREFIX . "_dag 
	ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
$result = mysqli_query($con, $sql) or die("invullen gegevens solar_js ERROR: " . mysqli_error($con));
$dateTime = STARTDATE;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}
$adag = array(); //CVDK
$directory = ROOT_DIR . "/" . $_SESSION['plant'] . '/solarlog/'; //CVDK

$aantaldagen = 60; //aantal dagen dat hij moet inladen (enkel bij initiele load) normaal gelijk aan 2
//echo "-----------------------------".$dateTime."<br>";

/******************************************************************************
 *script aangepast door Peter Smessaert==>http://www.solarlog-home.eu/petersmessaert/
 *script bugfix door Chris VDK==>http://chris.3940.be met hulp van Jeanphi
 ******************************************************************************/
for ($tel = 0; $tel <= $aantaldagen; $tel++) {
    $num = (date("ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
    $filename = $directory . "min" . $num . '.js';

//CVDK$AgetHeaders = @get_headers($directory . "min".$num.'.js');
//echo '<pre>'.print_r($AgetHeaders, true).'</pre>';

//CVDKif (preg_match("|200|", $AgetHeaders[0])){
//echo $directory."min".$num.'.js';
//CVDK$adag[]=$directory."min".$num.'.js';
//CVDK}

    if (file_exists($filename)) { //CVDK
        $adag[] = $directory . "min" . $num . '.js';
    }
}
//CVDK$adag[]=$directory."min_day".'.js';//FIX: Replace the last value with min_day.js (the last value should be the current day)if(!empty($adag)){ //CVDK	$arrcount = count($adag);	$adag[$arrcount-1]=$directory."min_day".'.js';}else{	$adag[]=$directory."min_day".'.js';}
//echo '<pre>'.print_r($adag, true).'</pre>';


foreach ($adag as $v) {    //CVDK
    $teller = 1;
    $teller2 = 1;
    $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
    $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
    while (!feof($file)) {
        $geg_solar = fgets($file, 1024);
        //CVDK if ($teller>1){
        if (!empty($geg_solar)) {                //			list($Datum,$Uhrzeit,$WR,$Pac,$DaySum,$Status,$Error,$Pdc1,$Udc,$Uac)=explode(";",$geg_solar);
            list($deel1, $deel2, $deel3) = explode("\"", $geg_solar);
            list($datuur, $rest) = explode("|", $deel2);
            list($Datum, $Uhrzeit) = explode(" ", $datuur);
            //CVDK list($Pac,$weg1,$DaySum,$weg2)=explode(";",$rest);				//list($Pac,$weg1,$weg2,$DaySum)=explode(";",$rest); //CVDK - FIX: use correct value for DaySum
            list($Pac, $weg1, $DaySum, $weg2, $weg3) = explode(";", $rest);
            //echo "------------------------------>".$Datum."XXX".$Uhrzeit."XXX".$Pac."XXX".$DaySum;echo"<br>";

            $oDatumTijd = omzetdatum($Datum . " " . $Uhrzeit);
            $odatum = explode(" ", $oDatumTijd);
            //echo $oDatumTijd;echo"<br>";echo $odatum[0];echo "<br>";

            if ((strtotime($oDatumTijd) > strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {
                $DaySum = $DaySum * $params['coefficient'];
                if ($teller2 == 1) {
                    $string .= "('" . $oDatumTijd . $_SESSION['plant'] . "','" . $oDatumTijd . "'," . $Pac . "," . ($DaySum / 1000) . ",'" . $_SESSION['plant'] . "')";
                    $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . ($DaySum / 1000) . ",'" . $_SESSION['plant'] . "')";
                    $stringdelete = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                    $teller2 = 0;
                } else {
                    $string .= ",('" . $oDatumTijd . $_SESSION['plant'] . "','" . $oDatumTijd . "'," . $Pac . "," . ($DaySum / 1000) . ",'" . $_SESSION['plant'] . "')";
                }
            }
        }        //CVDK }
        //CVDK $teller++;
    }
    fclose($file);
    if ($teller2 == 0) {        //echo "<br>";echo $stringdelete;
        //echo "<br>";echo $string;
        //echo "<br>";echo $string1;
        mysqli_query($con, $stringdelete) or die ('SQL Error stringdelete:' . mysqli_error($con));
        mysqli_query($con, $string) or die ('SQL Error string:' . mysqli_error($con));
        mysqli_query($con, $string1) or die ('SQL Error string1:' . mysqli_error($con));
    }
}

/******************************************************************************
 * maak months.js bestand aan
 * ****************************************************************************
 */
//$directory2 =$_SESSION['plant'].'/solarlog/';
$sql = "SELECT MAX(Datum_Maand) AS maxi,SUM(Geg_Maand) AS som
	FROM " . TABLE_PREFIX . "_maand
	WHERE Naam='" . $_SESSION['plant'] . "'
	GROUP BY DATE_FORMAT(Datum_Maand,'%y-%m')
	ORDER BY Datum_Maand DESC";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    //CVDK $fp = fopen($_SESSION['plant'].'months.js',"w+");
    $fp = fopen($directory . 'months.js', "w+"); //CVDK
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
    //CVDK $fp = fopen($_SESSION['plant'].'days_hist.js',"w+");
    $fp = fopen($directory . 'days_hist.js', "w+"); //CVDK
    while ($row = mysqli_fetch_array($result)) {
        $datfile = date("d.m.y", strtotime($row['Datum_Maand']));
        //echo $datfile."<br>";
        $dateTime = 'da[dx++]="' . $datfile . "|" . floor($row['Geg_Maand'] * 1000) . ';1000"';//echo $dateTime; //datum omzetten
        //$fp = fopen('months1.js',"r+");
        fwrite($fp, $dateTime . "\r\n");
    }
    fclose($fp);
}

function omzetdatum($date): string
{
    //echo $date.'b<br>';
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
