<?php
//***********************************************
//Regel de export naar PV Output
//zet API en SysID in parameters database
//wijziging ingevoerd op 30-1-2016
//wijziging 6 maart 2019 ombouw naar Mysqli
//variabele prefix gecorrigeerd 20 maart
$con = $mysqli
?>
<?php
//https://mariolurig.com/coding/mysqli_result-function-to-match-mysql_result/
function mysqli_result($res, $row = 0, $col = 0)
{
    $numrows = mysqli_num_rows($res);
    if ($numrows && $row <= ($numrows - 1) && $row >= 0) {
        mysqli_data_seek($res, $row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])) {
            return $resrow[$col];
        }
    }
    return false;
}

?>
<?php
//*************************************************
//Output to PVOutput
//*************************************************
$sid = explode(",", $params['EMU']['PVO_SYS_ID']);
for ($j = 0; $j < count(PLANTS); ++$j) {
    $sqlpvt = "SELECT T_1 FROM logger ORDER BY Datum_Dag DESC LIMIT 1";
    $PVT = mysqli_query($con, $sqlpvt);
    $temp = mysqli_fetch_row($PVT);
//echo $temp[0],"<BR>";
    $PVtemp = 0.1 * $temp[0];

//echo TABLE_PREFIX,"<BR>";
//echo TABLE_PREFIX;
    $sql = "SELECT Geg_Dag, Datum_Dag FROM " . TABLE_PREFIX . "_dag where Naam = '" . PLANTS[$j] . "' ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
//echo $sql;
    $PV1v2 = mysqli_query($con, $sql);
    $row1 = mysqli_fetch_array($PV1v2);
    $watt_act = $row1[0];
    $uploadtijd = substr($row1[1], 11, 5);
    $uploaddatum = str_replace("-", "", substr($row1[1], 0, 10));

    $sql = "SELECT Geg_Maand FROM " . TABLE_PREFIX . "_maand where Naam = '" . PLANTS[$j] . "' ORDER BY Datum_Maand DESC LIMIT 1";
    $PV1v1 = mysqli_query($con, $sql);
    $row2 = mysqli_fetch_array($PV1v1);
    $wattuur = 1000 * $row2[0];
    $pvOutputURL = "http://pvoutput.org/service/r2/addstatus.jsp?"
        . "key=" . $params['EMU']['PVO_API']
        . "&sid=" . $sid[$j]
        . "&d=" . $uploaddatum
        . "&t=" . $uploadtijd
        . "&v1=" . $wattuur
        . "&v5=" . $PVtemp
        . "&v2=" . $watt_act;
//echo $pvOutputURL,"<BR>";
    file_get_contents(trim($pvOutputURL));
    mysqli_query($con, $sql) or die ('SQL Error fout:' . mysqli_error($con));
}
?>

<?php
//$webdir=getcwd();
$webdir = $params['EMU']['ZonPhp'];
//echo $webdir.'/export/months.js',"<BR>";
//echo $Path_Zonphp;
/******************************************************************************
 * maak months.js bestand aan revisie september 2012
 * ****************************************************************************/
$sql = "
select  DATE_FORMAT(ultimo_maand, '%d.%m.%y'), cast(group_concat(concat( productie ,'') separator '|') as char(30))
from (
SELECT  MAX(Datum_Maand) ultimo_maand,  round(sum(Geg_Maand)*1000 ) productie
    FROM tgeg_maand
    where datum_maand > subdate(curdate(), interval 3 month) 
    GROUP BY Naam, DATE_FORMAT(Datum_Maand,'%y-%m')
    ORDER BY ultimo_maand desc , naam aSC
) inline_vw
group by ultimo_maand
order by ultimo_maand desc
";
//echo $sql;
$i = 0;
$result = mysqli_query($con, $sql);
$rows = mysqli_num_rows($result);
$fp = fopen($webdir . '/export/months.js', "w+");
while ($i < $rows) {
    //echo 'mo[mx++]="' . mysqli_result($result, $i, 0) ."|". mysqli_result($result, $i, 1) . '"' . "\n <BR>" ;
    fwrite($fp, 'mo[mx++]="' . mysqli_result($result, $i, 0) . "|" . mysqli_result($result, $i, 1) . '"' . "\n");
    $i++;
}
fclose($fp);
?>
<?php
/******************************************************************************
 * maak days_hist.js bestand aan revisie sept 2012
 * ****************************************************************************
 */
$i = 0;
$sql = "
SELECT CONCAT( DATE_FORMAT(Datum_Maand, '%d.%m.%y'), GROUP_CONCAT( CONCAT(  '|', ROUND( 1000 * Geg_Maand, 0 ) ,  ';',  '1000' ) 
ORDER BY Naam
SEPARATOR  '' ) ) 
FROM tgeg_maand
GROUP BY Datum_Maand
ORDER BY Datum_Maand DESC 
";
$result = mysqli_query($con, $sql);
@$rows = mysqli_num_rows($result);
$fp = fopen($webdir . '/export/days_hist.js', "w+");
while ($i < $rows) {
    //echo 'da[dx++]="' . mysqli_result($result, $i, 0) . '"' . "\n <BR>" ;
    fwrite($fp, 'da[dx++]="' . mysqli_result($result, $i, 0) . '"' . "\n");
    $i++;
}
fclose($fp);
?>
<?php
/******************************************************************************
 * maak min_day.js bestand aan revisie oktober 2012
 * http://stackoverflow.com/questions/5032360/running-sums-for-multiple-categories-in-mysqli
 * ****************************************************************************
 */
$i = 0;
$sql = "
SELECT CONCAT( Datum_Dag, GROUP_CONCAT( CONCAT(  '|', Geg_Dag,  ';',  round(1.1*Geg_Dag) ,  ';', ROUND( WhTotal ) ,  ';',  round(FLOOR(207 + (RAND() * 35))) ) ORDER BY Naam SEPARATOR  '' ) ) 
FROM (
    SELECT Naam, Geg_Dag, DATE_FORMAT( Datum_Dag, '%d.%m.%y %H:%i:%s' ) Datum_Dag, kWh_Dag, @sum := 
    IF (@cat = Naam, @sum , 0) +1000 * kWh_Dag AS WhTotal, @cat := Naam
    FROM tgeg_dag
    CROSS JOIN (SELECT @cat :=  '', @sum :=0) AS InitVarsAlias
    WHERE Datum_Dag > CURDATE( ) 
    ORDER BY Naam, Datum_Dag
    ) AS SubQueryAlias
GROUP BY Datum_Dag
ORDER BY Datum_Dag DESC , Naam";
//echo $sql;
$result = mysqli_query($con, $sql);
@$rows = mysqli_num_rows($result);
$fp = fopen($webdir . '/export/min_day.js', "w+");
while ($i < $rows) {
    //echo 'm[mi++]="' . mysqli_result($result, $i, 0) . '"' . "\n <BR>" ;
    fwrite($fp, 'm[mi++]="' . mysqli_result($result, $i, 0) . '"' . "\n");
    $i++;
}
fclose($fp);
?>
