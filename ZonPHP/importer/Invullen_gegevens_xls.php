<?php
/******************************************************************************
 * Zoek naar de records met TIME='00:00:00' ***_maand =delete
 * ****************************************************************************
 */
$sql = "SELECT * 
	FROM " . $table_prefix . "_maand
	WHERE Naam ='" . $_SESSION['Wie'] . "'
	AND TIME( Datum_Maand ) = '00:00:00'
	ORDER BY Datum_Maand DESC";
//echo $sql."<br />";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR:zoek in maand=delete " . mysqli_error($con));
if (mysqli_num_rows($result) != 0) {
    $sdelmaand = "DELETE FROM tgeg_maand WHERE Datum_Maand IN (";
    while ($row = mysqli_fetch_array($result)) {
        $sdelmaand .= "'" . $row['Datum_Maand'] . "',";
        $dcontrolebijvoeg = $row['Datum_Maand'];
        $fcontrolebijvoeg = $row['Geg_Maand'];
    }
    $sdelmaand = substr($sdelmaand, 0, -1);
    $sdelmaand .= ");";

    //echo $sdelmaand;echo "<br />";echo "<br />";
    mysqli_query($con, $sdelmaand) or die("Query failed. ERROR: " . mysqli_error($con));
} else
    $dcontrolebijvoeg = "geen";
/******************************************************************************
 * Zoek naar de laatste ingevulde dag in tabel ***_maand
 * ****************************************************************************
 */

$sql = "SELECT * 
	FROM " . $table_prefix . "_maand
	WHERE Naam ='" . $_SESSION['Wie'] . "'
	AND TIME( Datum_Maand ) = '23:59:59'
	ORDER BY Datum_Maand DESC 
	LIMIT 1 ";
//echo $sql."<br />";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR:laatste dag in maand " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $dateTime = date('Y-m-d', strtotime($dstartdatum));
} else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Maand']; //datum omzetten
        //echo $dateTime."<br />";
    }
}

require_once 'phpExcelReader/Excel/reader.php';
error_reporting(E_ALL ^ E_NOTICE);
/******************************************************************************
 * Zoek naar de SDT xls files
 * ****************************************************************************
 */
$directory = ROOT_DIR . "/" . $_SESSION['Wie'] . "/";
$ajaar = array();
for ($tel = 0; $tel <= 5; $tel++) {
    $num = (date("y", strtotime("+" . $tel . " year", strtotime($dateTime))));//echo $num."<br />";
    //echo $directory . 'SDT_'.($num).'.xls'."<br />";
    if (file_exists($directory . 'SDT_' . ($num) . '.xls')) {
        $ajaar[$tel] = $directory . 'SDT_' . ($num) . '.xls';
    }
}
//echo "<pre>".print_r($ajaar,true)."</pre>";
/******************************************************************************
 * Invullen van de gevonden bestanden in de database ***_maand
 * ****************************************************************************
 */
foreach ($ajaar as $v) {
    // ExcelFile($filename, $encoding);
    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('CP1251');
    $data->read($v);

    $string = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values";

    $buitvoeren = false;
    for ($i = 8; $i <= $data->sheets[0]['numRows']; $i++) {
        //echo excel2mysql_date($data->sheets[0]['cells'][$i][1])."<br />";
        $dtomzet = excel2mysql_date($data->sheets[0]['cells'][$i][1]);
        //echo $dateTime."---".$dtomzet;
        if ($dateTime < $dtomzet) {
            //$string=$string."('".$dtomzet."',";
            $string .= "('" . $dtomzet . $_SESSION['Wie'] . "','" . $dtomzet . "',";
            $string .= $param['coefficient'] * ($data->sheets[0]['cells'][$i][2]) . ",'" . $_SESSION['Wie'] . "'),";
            $buitvoeren = true;
        }
    }

    if ($buitvoeren) {
        $string = substr($string, 0, -1);
        //echo $string;echo "<br />";echo "<br />";
        mysqli_query($con, $string) or die("Query failed. ERROR: " . mysqli_error($con));
    }
}

//echo $dtomzet."<br />";
/******************************************************************************
 * Zoek naar de laatste ingevulde dag in tabel ***_dag
 * ****************************************************************************
 */


$sql = "SELECT *
	FROM " . $table_prefix . "_dag 
	WHERE Naam='" . $_SESSION['Wie'] . "'
	ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql."<br />";
$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0) {
    $dateTime = date('Y-m-d', strtotime($dstartdatum));
    $firstmonth = date('Y-m-01', strtotime($dstartdatum));//echo $firstmonth."<br />";
    $laatsterecord = 0;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag']; //datum omzetten
        $firstmonth = date('Y-m-01', strtotime($dateTime));
        $laatsterecord = $row['kWh_Dag'];
        //echo 'laatste dag in dag'.$dateTime."<br />";
        //echo 'laatste kw in dag'.$laatsterecord."<br />";
    }
}
/******************************************************************************
 * Zoek naar de laatste ingevulde dag in tabel ***_maand zonder time voorwaarde
 * ****************************************************************************
 */
$sql = "SELECT * 
	FROM " . $table_prefix . "_maand
	WHERE Naam ='" . $_SESSION['Wie'] . "'
	AND TIME( Datum_Maand ) = '23:59:59'
	ORDER BY Datum_Maand DESC 
	LIMIT 1 ";
//echo $sql."<br />";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $dlaatstedagmaand = $dstartdatum;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $dlaatstedagmaand = $row['Datum_Maand']; //datum omzetten
        //echo 'laatste dag in maand'.$dlaatstedagmaand."<br />";
    }
}
/******************************************************************************
 * Zoek naar de SDM xls files
 * ****************************************************************************
 */

$tmp_months = array();
for ($tel = 0; $tel <= 11; $tel++) {
    $num = (date("ym", strtotime("+" . $tel . " month", strtotime($firstmonth))));//echo $num;
    //echo $directory . 'SDM_'.($num).'.xls'."<br />";
    if (file_exists($directory . 'SDM_' . ($num) . '.xls')) {
        $tmp_months[$tel] = $directory . 'SDM_' . ($num) . '.xls';
    }
}
/******************************************************************************
 * Invullen van de gevonden bestanden in de database ***_dag
 * ****************************************************************************
 */
//echo '<pre>'.print_r($tmp_months, true).'</pre>'; 
$geheugen = $dateTime;
$vlag = 0;
foreach ($tmp_months as $v) {
    // ExcelFile($filename, $encoding);
    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('CP1251');
    $data->read($v);
    $string = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
    $stringMaand = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values";
    //echo $string."<br />";
    //echo $v;
    $buitvoeren = 0;
    $bgegevensgevonden = 0;
    for ($i = 8; $i <= $data->sheets[0]['numRows']; $i++) {
        //echo "<br />";echo $data->sheets[0]['numRows'];echo "<br />";
        //echo excel2mysql_date($data->sheets[0]['cells'][$i][1])."<br />";
        $dtomzet = excel2mysql_date($data->sheets[0]['cells'][$i][1]); //echo $dtomzet."<br />";
        //echo $data->sheets[0]['cells'][$i][2]."<br />";
        if ($dateTime < $dtomzet) {
            //$teh++;echo $teh."<br />";
            if (date("y-m-d", strtotime($dtomzet)) != date("y-m-d", strtotime($geheugen))) {
                //echo '---------->'.$geheugen.'+++'.$dtomzet."<br />";
                if (date("y-m-d", strtotime($dlaatstedagmaand)) < date("y-m-d", strtotime($geheugen)) && $vlag == 0) {
                    $stringMaand .= "('" . date("Y-m-d 23:59:59", strtotime($geheugen)) . $_SESSION['Wie'] . "','" . date("Y-m-d", strtotime($geheugen)) . "',";
                    $stringMaand .= $laatsterecord . ",'" . $_SESSION['Wie'] . "'),";
                    $bgegevensgevonden = 1;
                    //echo "<br />";
                    //echo 'bgeg'.$bgegevensgevonden;
                    //echo '*-'.$geheugen.'-'.$laatsterecord.'-*<br />';
                    //echo $stringMaand.'-*<br />';
                }
                //echo '*-'.$geheugen.'-'.$laatsterecord.'-*<br />';
                $laatsterecord = 0;
                $geheugen = $dtomzet;
            } else {
                $geheugen = $dtomzet;
                $vlag = 0;
            }
            if ($dtomzet != date("Y-m-d 23:59:59", strtotime($dtomzet))) {
                $string .= "('" . $dtomzet . $_SESSION['Wie'] . "','" . $dtomzet . "',";
                $laatsterecord += $param['coefficient'] * ($data->sheets[0]['cells'][$i][2]) / (1000 * 60 / $param['isorteren']);
                $string .= $data->sheets[0]['cells'][$i][2] . "," . $laatsterecord . ",'" . $_SESSION['Wie'] . "'),";
                $buitvoeren = 1;
                //echo $data->sheets[0]['cells'][$i][2].",".$laatsterecord.",'".$_SESSION['Wie']."'),"."<br />";
            }
        }
    }
    //echo '$buitvoeren:'.$buitvoeren;echo "<br />";
    if ($buitvoeren == 1) {
        $vlag = 1;
        if (date("y-m-d", strtotime($dlaatstedagmaand)) < date("y-m-d", strtotime($geheugen))) {
            $stringMaand .= "('" . date("Y-m-d 23:59:59", strtotime($geheugen)) . $_SESSION['Wie'] . "','" . date("Y-m-d", strtotime($geheugen)) . "',";
            $stringMaand .= $laatsterecord . ",'" . $_SESSION['Wie'] . "'),";
            $bgegevensgevonden = 1;
            //echo '*-'.$geheugen.'-'.$laatsterecord.'-*<br />';
            //echo $stringMaand.'-*<br />';
        }
        $string = substr($string, 0, -1);
        $stringMaand = substr($stringMaand, 0, -1);
        //echo $string;echo "<br />";echo "<br />";
        //echo $stringMaand;echo "<br />";echo "<br />";

        mysqli_query($con, $string) or die("Query failed. schrijf dag: " . mysqli_error($con));
        //echo '$bgegevensgevonden:'.$bgegevensgevonden;
        if ($bgegevensgevonden == 1) {
            //echo '1'. $stringMaand;echo "<br />";echo "<br />";
            mysqli_query($con, $stringMaand) or die("Query failed. ERROR: " . mysqli_error($con));
        }
    }
    //echo 'vlag:'.$vlag;echo "<br />";echo '$dcontrolebijvoeg:'.$dcontrolebijvoeg;echo "<br />";
    if ($vlag == 0 && $dcontrolebijvoeg != "geen") {
        $stringMaand = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values";
        $stringMaand .= "('" . date("Y-m-d 23:59:59", strtotime($dcontrolebijvoeg)) . $_SESSION['Wie'] . "','" . date("Y-m-d", strtotime($dcontrolebijvoeg)) . "',";
        $stringMaand .= $fcontrolebijvoeg . ",'" . $_SESSION['Wie'] . "')";
        //echo '2'.$stringMaand;echo "<br />";echo "<br />";
        mysqli_query($con, $stringMaand) or die("Query failed. ERROR: " . mysqli_error($con));
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
	WHERE Naam='" . $_SESSION['Wie'] . "'
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
function excel2mysql_date($days)
{
    // extract the decimal part and calculate time
    $hour_frac = ($days - floor($days)) * 24;
    $hour = floor($hour_frac);

    $minute_frac = ($hour_frac - floor($hour_frac)) * 60;
    $minute = floor($minute_frac);

    $seconds = round(($minute_frac - floor($minute_frac)) * 60);

    if ($days < 1) return "";
    if ($days == 60) {
        return array('day' => 29, 'month' => 2, 'year' => 1900);
    } else {
        if ($days < 60) {
            // Because of the 29-02-1900 bug, any serial date
            // under 60 is one off... Compensate.
            ++$days;
        }
        // Modified Julian to DMY calculation with an addition of 2415019
        //echo $days."<br />";
        $l = floor($days + 68569 + 2415019);//echo $l."<br />";
        $n = floor((4 * $l) / 146097);//echo $n."<br />";
        $l = $l - floor((146097 * $n + 3) / 4);//echo $l."<br />";
        $i = floor((4000 * ($l + 1)) / 1461001);//echo $i."<br />";
        $l = $l - floor((1461 * $i) / 4) + 31;//echo $l."<br />";
        $j = floor((80 * $l) / 2447);//echo $j."<br />";
        $nDay = floor(0.5 + ($l - floor((2447 * $j) / 80)));//echo $nDay."<br />";
        $l = floor($j / 11);
        $nMonth = $j + 2 - (12 * $l);
        $nYear = 100 * ($n - 49) + $i + $l;
        return date("Y-m-d H:i:s", mktime($hour, $minute, $seconds, $nMonth, $nDay, $nYear));

    }
}

?>       