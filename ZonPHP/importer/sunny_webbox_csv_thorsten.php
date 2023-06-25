<?php
$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag 
	ORDER BY Datum_Dag DESC LIMIT 1";

$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0)
    $dateTime = STARTDATE;
else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

$directory = ROOT_DIR . "/" . $_SESSION['plant'] . '/';
for ($tel = 0; $tel <= 60; $tel++) {  //2009-12-19.csv
    $num = (date("Y-m-d", strtotime("+" . $tel . " day", strtotime($dateTime))));
    if (file_exists($directory . $num . '.csv')) {
        $adag[] = $directory . $num . '.csv';
    }
}

?>

<?php
if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $teller2 = 1;
        $begindagEtotaal = 0;
        $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_solar = fgets($file, 1024);
            $geg_solar = trim($geg_solar);
            if ($teller > 6) {
                if (!empty($geg_solar)) {
                    $alist = array();
                    $alist = explode(";", $geg_solar);
                    $Datum = $alist[0];

                    // -----------------------------------------------------------------
                    //Spalte aus csv GridMs.TotW
                    //bis 08.12.2013 pac=spalte 5
                    //ab 09.12.2013 pac=spalte 10
                    //Spalte aus csv Metering.TotWhOut
                    //bis 08.12.2013 daysum=spalte 1
                    //ab 09.12.2013 daysum=spalte 15
                    // -----------------------------------------------------------------
                    $currentFileDate = strtotime($Datum);
                    $switchDate = strtotime("2013-12-09 ");
                    if ($currentFileDate < $switchDate) {
                        // old version (before 2013-12-09)
                        $Pac = str_replace(array(','), '.', $alist[5]);
                        $DaySum = str_replace(array(','), '.', $alist[1]);
                    } else {
                        // new version (after 2013-12-08)
                        $Pac = str_replace(array(','), '.', $alist[10]);
                        $DaySum = str_replace(array(','), '.', $alist[15]);
                    }

                    if ($teller == 7) {
                        $begindagEtotaal = $DaySum;
                    }

                    $oDatumTijd = omzetdatum($Datum);
                    $odatum = explode(" ", $oDatumTijd);
                    if ((strtotime($oDatumTijd) > strtotime($dateTime)) and ($oDatumTijd != "geen datumtijd")) {
                        $DaySum = ($DaySum - $begindagEtotaal) * $params['coefficient'];
                        if ($teller2 == 1) {
                            $string .= "('" . $oDatumTijd . $_SESSION['plant'] . "','" . $oDatumTijd . "'," . $Pac . "," . number_format($DaySum, 3) . ",'" . $_SESSION['plant'] . "')";
                            $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . number_format($DaySum, 3) . ",'" . $_SESSION['plant'] . "')";
                            $stringdelete = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Datum_Maand='" . $odatum[0] . "'";
                            $teller2 = 0;
                        } else {
                            $string .= ",('" . $oDatumTijd . $_SESSION['plant'] . "','" . $oDatumTijd . "'," . $Pac . "," . number_format($DaySum, 3) . ",'" . $_SESSION['plant'] . "')";
                            $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . number_format($DaySum, 3) . ",'" . $_SESSION['plant'] . "')";
                        }
                    }
                }
            }
            $teller++;
        }
        fclose($file);
        if ($teller2 == 0) {
            mysqli_query($con, $stringdelete) or die ('SQL Error stringdelete:' . mysqli_error($con));
            mysqli_query($con, $string) or die ('SQL Error string:' . mysqli_error($con));
            mysqli_query($con, $string1) or die ('SQL Error string1:' . mysqli_error($con));
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
?>

<?php
function omzetdatum($date)
{
    //echo $date.'b<br />';
    $date = str_replace(array('.', ' ', ':'), '/', $date);
    $m_d_j_t = explode("/", $date);
    if (!isset($m_d_j_t[0])) return "geen datumtijd";
    if (!isset($m_d_j_t[1])) return "geen datumtijd";
    if (!isset($m_d_j_t[2])) return "geen datumtijd";
    if (!isset($m_d_j_t[3])) return "geen datumtijd";
    if (!isset($m_d_j_t[4])) return "geen datumtijd";

    if (!is_numeric($m_d_j_t[0])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[1])) return "geen datumtijd";;
    if (!is_numeric($m_d_j_t[2])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[3])) return "geen datumtijd";
    if (!is_numeric($m_d_j_t[4])) return "geen datumtijd";

    if (controledatum($m_d_j_t[1], $m_d_j_t[0], $m_d_j_t[2]))
        if (checktime($m_d_j_t[3], $m_d_j_t[4], 0))
            return $m_d_j_t[2] . "-" . $m_d_j_t[0] . "-" . $m_d_j_t[1] . " " . $m_d_j_t[3] . ":" . $m_d_j_t[4] . ":" . "00";
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}

?>

<?php
function controledatum($idag, $imaand, $ijaar)
{
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