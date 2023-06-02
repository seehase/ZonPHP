<?php

// Changed by JP Gregoire to support multiple inverters combined with JS files reading

// 1. Get the last time the data was updated
$sql = "SELECT *
	FROM " . $table_prefix . "_dag 
	ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0)
    $dateTime = $dstartdatum;
else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}
//echo $dateTime;

$adag = array();
$directory = ROOT_DIR . "/" . $_SESSION['Wie'] . '/';
//echo $directory;

// 2. Store the filenames of the data files that will be used to update, starting from the value in 1.
for ($tel = 0; $tel <= 60; $tel++) {
    $num = (date("ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));//echo $num."<br />";
    if (file_exists($directory . "min" . $num . '.js')) {
        //echo $directory.$num.$stHVL_suo.'.suo<br />';
        $adag[] = $directory . "min" . $num . '.js';
    }
}
//echo '<pre>'.print_r($adag, true).'</pre>'; 

// 3. Replace the last value with min_day.js (the last value should be the current day)
if (!empty($adag)) {
    $arrcount = count($adag);
    $adag[$arrcount - 1] = $directory . "min_day" . '.js';
}
//echo '<pre>'.print_r($adag, true).'</pre>'; 

?>
<?php
// 4. Start parsing the files whose filenames were stored in $adag
foreach ($adag as $v) {
    $teller = 1;
    $teller2 = 1;
    $string = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
    $string1 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values";
    $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
    while (!feof($file)) {
        // Parsing line per line
        $geg_solar = fgets($file, 1024);
        //$geg_solar=trim($geg_solar);
        //echo '<pre>'.print_r($geg_solar, true).'</pre>';
        if ($teller > 0) {

            if (!empty($geg_solar)) {
                // Sort the data using delimiters
                list($deel1, $deel2, $deel3) = explode("\"", $geg_solar);
                $alist = array();
                $alist = explode("|", $deel2);
                list($Datum, $Uhrzeit) = explode(" ", $alist[0]);
                $oDatumTijd = omzetdatum($Datum . " " . $Uhrzeit);
                $odatum = explode(" ", $oDatumTijd);

                if ((strtotime($oDatumTijd) > strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {

                    if ($teller2 == 1) {
                        for ($i = 0; $i < count($sNaamSaveDatabase); $i++) {

                            $omdata = explode(";", $alist[$i + 1]);
                            // Get the useful values
                            $Pac = $omdata[0];
                            $DaySum = $omdata[2];
                            $DaySum = floatval($DaySum) * $param['coefficient'];
                            //echo "------------------------------>".$Datum."XXX".$Uhrzeit."XXX".$Pac."XXX".$DaySum."XXX".$sNaamSaveDatabase[$i];echo"<br />";
                            //Prepare the SQL queries
                            $string .= "('" . $oDatumTijd . $sNaamSaveDatabase[$i] . "','" . $oDatumTijd . "'," . $Pac . "," . ($DaySum / 1000) . ",'" . $sNaamSaveDatabase[$i] . "'),";
                            $string1 .= "('" . $odatum[0] . $sNaamSaveDatabase[$i] . "','" . $odatum[0] . "'," . ($DaySum / 1000) . ",'" . $sNaamSaveDatabase[$i] . "'),";
                            $stringdelete = "DELETE FROM " . $table_prefix . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                        }
                        $teller2 = 0;
                    } else {
                        for ($i = 0; $i < count($sNaamSaveDatabase); $i++) {

                            $omdata = explode(";", $alist[$i + 1]);
                            $Pac = $omdata[0];
                            $DaySum = $omdata[2];
                            $DaySum = floatval($DaySum) * $param['coefficient'];
                            $string .= "('" . $oDatumTijd . $sNaamSaveDatabase[$i] . "','" . $oDatumTijd . "'," . $Pac . "," . ($DaySum / 1000) . ",'" . $sNaamSaveDatabase[$i] . "'),";
                            //echo "------------------------------>".$Datum."XXX".$Uhrzeit."XXX".$Pac."XXX".$DaySum."XXX".$sNaamSaveDatabase[$i];echo"<br />";
                        }
                    }
                }
                //echo '<pre>'.print_r($geg_solar, true).'</pre>';
                //list($Datum,$Uhrzeit,$WR,$Pac,$DaySum,$Status,$Error,$Pdc1,$Udc,$Uac)=explode(";",$geg_solar);
            }
        }
        $teller++;
    }
    // Close the files and run the SQL queries
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

?>
<?php
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
    $fp = fopen($_SESSION['Wie'] . '/months.js', "w+");
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
	FROM " . $table_prefix . "_maand
	WHERE Naam='" . $_SESSION['Wie'] . "'
	ORDER BY Datum_Maand DESC";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    $fp = fopen($_SESSION['Wie'] . '/days_hist.js', "w+");
    while ($row = mysqli_fetch_array($result)) {
        $datfile = date("d.m.y", strtotime($row['Datum_Maand']));
        //echo $datfile."<br />";
        $dateTime = 'da[dx++]="' . $datfile . "|" . floor($row['Geg_Maand'] * 1000) . ';1000"';//echo $dateTime; //datum omzetten
        //$fp = fopen('months1.js',"r+");
        fwrite($fp, $dateTime . "\r\n");
    }
    fclose($fp);
}
?>
<?php
function omzetdatum($date)
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

?>
<?php
function controledatum($idag, $imaand, $ijaar)
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
function checktime($hour, $minute, $second)
{
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
        return true;
    }
}

?> 
