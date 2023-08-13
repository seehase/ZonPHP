<?php
//***********************************************
//New Version, compatible with ZonPHP Release 2023.08.09
//Exports solar data to PV Output
//Reads API en SysID from ZonLive (or separate file)
//*************************************************
//Output to PVOutput
//*************************************************
$sid= explode(",",$PVO_SYS_ID);
for ($j = 0; $j < count(PLANT_NAMES); ++$j)
{
$sql="SELECT Geg_Dag, Datum_Dag FROM ".$table_prefix."_dag where Naam = '".PLANT_NAMES[$j]."' ORDER BY Datum_Dag DESC LIMIT 1";
//echo $sql;
$PV1v2=mysqli_query($mysqli,$sql);
$row1= mysqli_fetch_array($PV1v2);
$watt_act=$row1[0];
$uploadtijd = substr($row1[1],11,5);
$uploaddatum= str_replace("-","",substr($row1[1],0,10));

$sql="SELECT Geg_Maand FROM ".$table_prefix."_maand where Naam = '".PLANT_NAMES[$j]."' ORDER BY Datum_Maand DESC LIMIT 1";
$PV1v1=mysqli_query($mysqli,$sql);
$row2= mysqli_fetch_array($PV1v1);
$wattuur=1000*$row2[0];
$pvOutputURL = "http://pvoutput.org/service/r2/addstatus.jsp?"
                . "key=" . $PVO_API
                . "&sid=" . $sid[$j]
                . "&d=" . $uploaddatum
                . "&t=" .  $uploadtijd
                . "&v1=" . $wattuur
				//. "&v5=" . $PVtemp
                . "&v2=" . $watt_act;
//echo $pvOutputURL;
//echo "\n";
file_get_contents(trim($pvOutputURL));
mysqli_query($mysqli,$sql)or die ('SQL Error fout:'. mysqli_error($mysqli));
}
?>
