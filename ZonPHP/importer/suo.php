<?php
global $con, $params;

// change coefficient if needed
$coefficient = 1;

$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag 
	ORDER BY Datum_Dag DESC LIMIT 1";
$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));
$dateTime = STARTDATE;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}

$directory = ROOT_DIR . "/" . $_SESSION['plant'] . "/";
$adag = array();
for ($tel = 0; $tel <= 60; $tel++) {
    $num = (date("ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));//echo $num."<br />";
    $HVL_suo = 0;
    $stHVL_suo = "0" . $HVL_suo;
    while (file_exists($directory . $num . $stHVL_suo . '.suo')) {
        $adag[] = $directory . $num . $stHVL_suo . '.suo';
        $HVL_suo++;
        if ($HVL_suo < 10)
            $stHVL_suo = "0" . $HVL_suo;
        else
            $stHVL_suo = $HVL_suo;
    }
}

$ieffectiefkwpiek = $params[$_SESSION['plant']]['capacity'];
foreach ($adag as $v) {
    $teller = 1;
    $string1 = "";
    //$string="insert into ".TABLE_PREFIX."_dag(Datum_Dag,Geg_Dag,KwH_Dag)values";
    $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
    $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
    while (!feof($file)) {
        $geg_suo = fgets($file, 1024);
        $geg_suo = trim($geg_suo);
        if ($teller > 5) {
            if (!empty($geg_suo)) {
                //echo "<pre>".print_r($geg_suo,true)."/<pre>";
                list($TimeStamp, $GridMsTotW, $MeteringDykWh, $rest) = explode(";", $geg_suo);
                $oTimeStamp = omzetdatum($TimeStamp);//date("Y-m-d H:i:s",strtotime($TimeStamp));
                //echo $oTimeStamp.'<br />';
                if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                    $odatum = explode(" ", $oTimeStamp);
                    $MeteringDykWh = $MeteringDykWh * $coefficient;
                    if ($GridMsTotW < 2 * $ieffectiefkwpiek) {
                        $string .= "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "'," . $GridMsTotW . "," . $MeteringDykWh . ",'" . $_SESSION['plant'] . "'),";
                        $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . $MeteringDykWh . ",'" . $_SESSION['plant'] . "') ON DUPLICATE KEY UPDATE Geg_Maand=" . $MeteringDykWh;
                    }
                }
            }
        }
        $teller++;
    }
    fclose($file);
    if ($string1 != "") {
        $string = substr($string, 0, -1);
        //echo $string;echo "<br />";
        //echo $string1;echo "<br />";
        //mysqli_query($con,"DELETE FROM ".TABLE_PREFIX."_maand WHERE Datum_Maand='".$odatum[0]."'") or die("Query failed. ERROR: ".mysqli_error($con));
        mysqli_query($con, $string) or die("Query string failed. ERROR: " . mysqli_error($con));
        mysqli_query($con, $string1) or die("Query string1 failed. ERROR: " . mysqli_error($con));
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
    $date = str_replace(array('/', ' ', ':', ','), '/', $date);
    //19/05/2009 08:56:24
    //echo $date.'rep<br />';
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
