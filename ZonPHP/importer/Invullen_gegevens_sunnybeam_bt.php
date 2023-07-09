<?php
global $con, $params;

$sql = "SELECT *
	FROM " . TABLE_PREFIX . "_dag 
	ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
$result = mysqli_query($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));
$dateTime = STARTDATE;
$flaatstewaarde = "start";
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result))
        $dateTime = $row['Datum_Dag'];
}
//echo $dateTime;	
$directory = ROOT_DIR . "/" . $_SESSION['plant'] . '/';
$aday = array();
for ($tel = 0; $tel <= 60; $tel++) {
    $num = (date("y-m-d", strtotime("+" . $tel . " day", strtotime($dateTime))));//echo $num."<br />";echo $directory.$num.'.csv'."<br />";
    if (file_exists($directory . $num . '.csv')) {
        $adag[] = $directory . $num . '.csv';
    }
}

$ieffectiefkwpiek = $params[$_SESSION['plant']]['capacity'];


if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $string1 = "";
        $start = 0;
        $startend = 0;
        $beginfile = 0;
        $stringend = "";
        $oploopkw = 0;
        $lege_waarde = 0;
        //$string="insert into ".TABLE_PREFIX."_dag(Datum_Dag,Geg_Dag,KwH_Dag)values";
        $string = "insert into " . TABLE_PREFIX . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_suo = fgets($file, 1024);
            $geg_suo = trim($geg_suo);
            if ($teller > 10) {
                if (!empty($geg_suo)) {
                    //echo "<pre>".print_r($geg_suo,true)."/<pre>";
                    $p = explode(";", $geg_suo);
                    list($TimeStamp, $MeteringDykWh) = explode(";", $geg_suo);    //,$rest
                    //echo '<pre>'.print_r($p,true).'<pre>';
                    $oTimeStamp = omzetdatum($TimeStamp);//date("Y-m-d H:i:s",strtotime($TimeStamp));
                    //echo $oTimeStamp.'--'.$dateTime.'<br />';
                    $MeteringDykWh = str_replace(array(','), '.', $MeteringDykWh);
                    if ($beginfile == 0 && $MeteringDykWh != "") {
                        $startkw = $MeteringDykWh;
                        $oploopkw = $MeteringDykWh;
                        $beginfile = 1;
                        $lege_waarde = $MeteringDykWh;
                    } else {
                        if ($MeteringDykWh == "")
                            $MeteringDykWh = $lege_waarde;
                        else
                            $lege_waarde = $MeteringDykWh;
                    }
                    //echo $MeteringDykWh." ".$oTimeStamp."--<br />";
                    if ((strtotime($oTimeStamp) == strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                        $oploopkw = $MeteringDykWh;
                    }
                    if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {
                        //echo ($MeteringDykWh-$oploopkw).'11 ';
                        if (($MeteringDykWh - $oploopkw) > (0.0015 * $ieffectiefkwpiek)) {
                            $startkw = $MeteringDykWh;//echo $startkw.'22 <br /><br /><br />';
                        }

                        if (($MeteringDykWh - $oploopkw) != 0 && (($MeteringDykWh - $oploopkw) < (0.0015 * $ieffectiefkwpiek)) && $MeteringDykWh != $startkw) {// || $start!=0
                            //echo $oTimeStamp.'---'.$MeteringDykWh.'<br />';
                            if ($MeteringDykWh == $oploopkw) {//|| ($MeteringDykWh)==""
                                $startend = 1;
                                $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                $GridMsTotW = ($MeteringDykWh - $startkw) * $params['coefficient'];
                                $stringend .= "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "'," . number_format(($MeteringDykWh - $oploopkw) * (60 / $params['displayInterval']) * 1000) . "," . number_format($MeteringDykWh - $oploopkw, 3) . ",'" . $_SESSION['plant'] . "'),";
                                if ($MeteringDykWh != "")
                                    $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . number_format($GridMsTotW, 3) . ",'" . $_SESSION['plant'] . "')";
                            } else {
                                $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                $GridMsTotW = ($MeteringDykWh - $startkw) * $params['coefficient'];
                                if ($startend == 0)
                                    $string .= "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "'," . number_format(($MeteringDykWh - $oploopkw) * (60 / $params['displayInterval']) * 1000, 0, '.', '') . "," . number_format($MeteringDykWh - $oploopkw, 3) . ",'" . $_SESSION['plant'] . "'),";
                                else
                                    $string .= $stringend . "('" . $oTimeStamp . $_SESSION['plant'] . "','" . $oTimeStamp . "'," . number_format(($MeteringDykWh - $oploopkw) * (60 / $params['displayInterval']) * 1000, 0, '.', '') . "," . number_format($MeteringDykWh - $oploopkw, 3) . ",'" . $_SESSION['plant'] . "'),";
                                if ($MeteringDykWh != "")
                                    $string1 = "insert into " . TABLE_PREFIX . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['plant'] . "','" . $odatum[0] . "'," . number_format($GridMsTotW, 3) . ",'" . $_SESSION['plant'] . "')";
                                $stringend = "";
                                $startend = 0;
                                $start = 1;
                            }
                            $oploopkw = $MeteringDykWh;
                        } else
                            $oploopkw = $MeteringDykWh;
                    }
                }
            }
            $teller++;
        }
        fclose($file);
        if ($string1 != "" && $start == 1) {
            $string = substr($string, 0, -1);
            //echo $string;echo "<br />";
            //echo $string1;echo "<br />";
            mysqli_query($con, "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Datum_Maand='" . $odatum[0] . "'") or die("Query failed. ERROR: " . mysqli_error($con));
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
    $date = str_replace(array('.', '/', '-', ' ', ':'), '/', $date);

    $d_m_j_t = explode("/", $date);
    if (!isset($d_m_j_t[0])) return "geen datumtijd";
    if (!isset($d_m_j_t[1])) return "geen datumtijd";
    if (!isset($d_m_j_t[2])) return "geen datumtijd";
    if (!isset($d_m_j_t[3])) return "geen datumtijd";
    if (!isset($d_m_j_t[4])) return "geen datumtijd";
    //if(!isset($d_m_j_t[5]))	return "geen datumtijd";
    if (!is_numeric($d_m_j_t[0])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[1])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[2])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[3])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[4])) return "geen datumtijd";
    //if(!is_numeric($d_m_j_t[5]))	return "geen datumtijd";

    if (controledatum($d_m_j_t[0], $d_m_j_t[1], $d_m_j_t[2]))
        if (checktime($d_m_j_t[3], $d_m_j_t[4], "00"))
            return $d_m_j_t[2] . "-" . $d_m_j_t[1] . "-" . $d_m_j_t[0] . " " . $d_m_j_t[3] . ":" . $d_m_j_t[4] . ":00";
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}

