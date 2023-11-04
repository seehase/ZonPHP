<?php
/*
 * Spezial Version für Marcel needs to be reworked
 */

global $con, $params;
$wr = "WR1";
$table_prefix = TABLE_PREFIX;

$sql = "SELECT * FROM " . $table_prefix . "_dag ORDER BY Datum_Dag DESC LIMIT 1";

// get latest import date from db
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0)
    $dateTime = "2012-01-03";
else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

$directory = ROOT_DIR . "/WR1/";

$aday = array();
for ($tel = 0; $tel <= 500; $tel++) {
    $num = (date("Ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));
    if (file_exists($directory . "MeinePV-Anlage1-" . $num . '.csv')) {
        $adag[] = $directory . "MeinePV-Anlage1-" . $num . '.csv';
    }
}

?>
<?php
if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $insertStringDayTotalValue = "";
        $insertStringValues = "";
        $houdeeindwaarde = 0;
        $stringend = "";
        $voegnulltoe = 0;
        $skip = true;
        $startkw = [0, 0, 0];
        $latestDayTotal = [0, 0, 0];
        $insertStringStatement = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_suo = fgets($file, 1024);
            $geg_suo = trim($geg_suo);
            // skip first 10 lines of CSV file
            if ($teller > 10) {
                if (!empty($geg_suo)) {

                    $p = explode(";", $geg_suo);
                    for ($i = 1; $i <= 3; $i++) {

                        $wrname = "WR" . $i;

                        $TimeStamp = $p[0];
                        $skip = true;
                        if ($i == 1 && sizeof($p) > 2) {
                            $totalValuekWh = $p[1];
                            $currentWattValue = $p[2];
                            $skip = false;
                        } else if ($i == 2 && sizeof($p) > 4) {
                            $totalValuekWh = $p[3];
                            $currentWattValue = $p[4];
                            $skip = false;
                        } else if ($i == 3 && sizeof($p) > 6) {
                            $totalValuekWh = $p[5];
                            $currentWattValue = $p[6];
                            $skip = false;
                        }

                        if (!$skip) {
                            /// list($TimeStamp, $totalValuekWh, $currentWattValue) = explode(";", $geg_suo);    //,$rest
                            $oTimeStamp = omzetdatum($TimeStamp);
                            $odatum = explode(" ", $oTimeStamp);

                            $currentWattValue = floatval(str_replace(array(','), '.', $currentWattValue));
                            if ($totalValuekWh == "") {
                                // workaround no value in CSV --> skip it, no insert
                                $oTimeStamp = "geen datumtijd";
                                // echo $teller;
                            }
                            $totalValuekWh = floatval(str_replace(array(','), '.', $totalValuekWh));


                            // get first total of the day
                            if ($startkw[$i - 1] == 0) {
                                $startkw[$i - 1] = $totalValuekWh;
                            }
                            if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {

                                if ($currentWattValue != 0) {
                                    $insertStringValues .= "('" . $oTimeStamp . $wrname . "','" . $oTimeStamp . "'," . ($currentWattValue * 1000) . "," . number_format($totalValuekWh - $startkw[$i - 1], 3) . ",'" . $wrname . "'),";

                                    $latestDayTotal[$i - 1] = ($totalValuekWh - $startkw[$i - 1]) ;

                                    $insertStringDayTotalValue = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                        $wrname . "','" . $odatum[0] . "'," . number_format(($totalValuekWh - $startkw[$i - 1]), 3) . ",'" . $wrname . "')";
                                    $stringend = "";
                                    $houdeeindwaarde = 1;
                                    $voegnulltoe = 0;

                                } else {
                                    if ($houdeeindwaarde != 0 && $voegnulltoe == 0) {
                                        $insertStringValues .= "('" . $oTimeStamp . $wrname . "','" . $oTimeStamp . "',0," . number_format($totalValuekWh - $startkw[$i - 1], 3) . ",'" . $wrname . "'),";
                                        $voegnulltoe = 1;
                                    }
                                    $insertStringDayTotalValue = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] .
                                        $wrname . "','" . $odatum[0] . "'," . number_format(0.0, 3) . ",'" . $wrname . "')";
                                }
                            }
                        }
                    }
                }
            }
            $teller++;
        }
        fclose($file);
        if ($insertStringValues != "") {
            if ($odatum[0] != "geen")
            {
                $sql = "DELETE FROM " . $table_prefix . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con) . $sql);
                $insertStringDayTotalValue = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . "WR1','" . $odatum[0] . "'," . number_format($latestDayTotal[0], 3) . ",'WR1')";
                mysqli_query($con, $insertStringDayTotalValue) or die("Query failed. ERROR: " . $insertStringDayTotalValue);
                $insertStringDayTotalValue = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . "WR2','" . $odatum[0] . "'," . number_format($latestDayTotal[1], 3) . ",'WR2')";
                mysqli_query($con, $insertStringDayTotalValue) or die("Query failed. ERROR: " . $insertStringDayTotalValue);
                $insertStringDayTotalValue = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . "WR3','" . $odatum[0] . "'," . number_format($latestDayTotal[2], 3) . ",'WR3')";
                mysqli_query($con, $insertStringDayTotalValue) or die("Query failed. ERROR: " . $insertStringDayTotalValue);
            }
            $insertStringValues = substr($insertStringValues, 0, -1);
            $insertStringStatement = $insertStringStatement . $insertStringValues;
            mysqli_query($con, $insertStringStatement) or die("Query failed. ERROR: " . $insertStringStatement);
        }
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
	WHERE Naam='" . $wr . "'
	GROUP BY DATE_FORMAT(Datum_Maand,'%y-%m')
	ORDER BY 1 DESC";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con) . $sql);

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
	FROM " . $table_prefix . "_maand
	WHERE Naam='" . $wr . "'
	ORDER BY Datum_Maand DESC";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con) . $sql);

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
?>

<?php
function omzetdatum($date)
{
    if (mb_check_encoding($date, "UCS-2")) {
        $date = ucs2toutf8_x($date);
    }
    $date = str_replace(array('.', '/', '-', ' ', ':', 'u0000'), '/', $date);

    $d_m_j_t = explode("/", $date);
    if (!isset($d_m_j_t[0])) return "geen datumtijd";
    if (!isset($d_m_j_t[1])) return "geen datumtijd";
    if (!isset($d_m_j_t[2])) return "geen datumtijd";
    if (!isset($d_m_j_t[3])) return "geen datumtijd";
    if (!isset($d_m_j_t[4])) return "geen datumtijd";
    $d_m_j_t[5] = "00";
    if (!isset($d_m_j_t[5])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[0])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[1])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[2])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[3])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[4])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[5])) return "geen datumtijd";

    if (controledatum_x($d_m_j_t[0], $d_m_j_t[1], $d_m_j_t[2]))
        if (checktime_x($d_m_j_t[3], $d_m_j_t[4], $d_m_j_t[5]))
            return $d_m_j_t[2] . "-" . $d_m_j_t[1] . "-" . $d_m_j_t[0] . " " . $d_m_j_t[3] . ":" . $d_m_j_t[4] . ":" . $d_m_j_t[5];
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}

?>
<?php
function controledatum_x($idag, $imaand, $ijaar)
{
    If (!checkdate($imaand, $idag, $ijaar)) {
        return false;
    } else {
        return true;
    }
}

?>
<?php
function checktime_x($hour, $minute, $second)
{
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
        return true;
    }
}

function ucs2toutf8_x($str)
{
    if (strlen($str) < 20) return $str;
    $out = "";
    for ($i = 0; $i < strlen($str); $i += 2) {
        $a1 = $str[$i];
        $out .= $a1;
    }
    return $out;
}


?> 