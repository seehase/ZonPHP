<?php
//###################################################################
// Version 2.0 Import EMU S0-logger CSV-data  E.M. Plankeel        ##
// Date  2019-04-04                                                ##
// Script to load csv data into a ZonPHP database                  ##
// Written for PHP 7.2 en MySQLi                                   ##
// For security script removed from public_html                    ##
//                                                                 ##
//###################################################################
//Connect to the database
$sserver = "localhost";//localhost
$sdatabase_name = "xxx";//database name
$susername = "yyy";// database username
$spassword = "zzz";// database password
$table_prefix = "tgeg";
$mysqli = new mysqli($sserver,$susername,$spassword,$sdatabase_name);
try {
    $conn = new PDO("mysql:host=$sserver;dbname=$sdatabase_name", $susername, $spassword);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    echo "Connected successfully"; 
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?>
<?php
//load parameters light
$teller = 0;
$param = array();
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters";
$resultpar = mysqli_query($mysqli, $sqlpar) or die("Error in SQL");
if (mysqli_num_rows($resultpar) != 0) 
		{
    while ($row = mysqli_fetch_array($resultpar)) {
        	$var = $row['Variable'];
        	$value = $row['Waarde'];
        	if (stripos($var, "color") === 0){}      
        	else if (stripos($var, "chart") === 0){} 
        	else 
        		{
            	$param[$var] = $row['Waarde'];
        		}
    		}
$dstartdatum = $param['jaar'] . "-" . $param['maand'] . "-" . $param['dag'];
		} 
else 	{
		die("Error reading table parameters");
		}
foreach (explode(',', $param['sNaamSaveDatabasest']) as $key => $stringnaam) {
        $sNaamSaveDatabase[] = $stringnaam;
        $teller++;
		}
?>
<?php
//###################################################################
//Look for last update table logger, last day will always be rewritten.
//###################################################################
$sql="SELECT * FROM logger ORDER BY Datum_Dag DESC LIMIT 1"; 
$result = $mysqli->query($sql) or die("Fout in SQL: ".mysqli_error($mysqli));
if (mysqli_num_rows($result) == 0) 
{
Echo "Databasetabel logger is leeg, wordt gevuld vanaf startdatum $dstartdatum<br/>"; 
$dateTime=$dstartdatum;
//echo $dateTime;echo '<BR>';
}
else
{
	while($row = mysqli_fetch_array($result))
	{
	$dateTime = $row['Datum_Dag']; 
	}
}
$date=$datum = date('Y-m-d', strtotime($dateTime));
$now=date('Y-m-d', time());
//###################################################################
//Load CSV data per day in table logger                           ##
//###################################################################
$file=$datum.'.csv';
 
//echo $file;
while ($datum <= $now)
{
$datum = date( 'Y-m-d',strtotime(date("Y-m-d", strtotime($datum)) . " +1 day"));
$filename = $param['Path_EMU'].$file;
if (($h = fopen("{$filename}", "r")) !== FALSE) 
{
  while (($column = fgetcsv($h, 1000, ";")) !== FALSE) 
  {
    $sqldata = "REPLACE into logger (Datum_Dag,S0_1,S0_2,S0_3,S0_4,S0_5,S0_6,S0_7,S0_8,S0_9,T_1)
values ('" . $column[0] . "','" . $column[1] . "','" . $column[2] . "','" . $column[3] . "','" . $column[4] . "','" . $column[5] . "','" . $column[6] . "','" . $column[7] . "','" . $column[8] . "','" . $column[9] . "','" . $column[10] . "')";	
  $result = mysqli_query($mysqli,$sqldata) or die("Error replacing records in database: ".mysqli_error($mysqli));
  }
  // Close the file
  fclose($h);
}
$file=$datum.'.csv';
}
?>
<?php
//###################################################################
//skip records between sundown and sunset                          ##
//correct for DST and UTC                                          ##
//###################################################################
date_default_timezone_set('Europe/Amsterdam');
$z= date ('I');
$y=($z+1);
$maand=date('n', strtotime($dateTime));
$zonop= array("0","08:30","07:30","07:00","06:00","05:30","05:30","06:00","06:00","06:30","07:30","08:00","08:30");
$zononder= array("0","17:30","18:30","20:30","21:00","22:00","22:00","22:00","21:30","20:30","19:00","17:00","17:00");
//###################################################################
//Calculations per inverter                                        ##
//###################################################################
for ($k = 0; $k < count($sNaamSaveDatabase); ++$k)
    {
//###################################################################
//Start from the correct EMU channel                               ##
//###################################################################
$o=$param['EMU_Offset'];
//###################################################################
//Calculate months between startdate and enddate for day table     ##
//###################################################################
	$startDate=strtotime($dateTime);
	$endDate=time();
	$numberOfMonths = abs((date('Y', $endDate) - date('Y', $startDate))*12 + (date('m', $endDate) - date('m', $startDate)))+1;
	$h=$maand;
      	for ( $hh = 0; $hh < $numberOfMonths ; ++$hh)
      	{
      	if ($h==13) {$h=1;}
		//echo $sNaamSaveDatabase[$k]," ",$hh," ",$h," ","<BR>";//controlestring
		//###################################################################
		//einde aanpassingen											   ##
		//###################################################################
		$sql="
		Replace into tgeg_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)
		select concat(t2.Datum_Dag,'".$sNaamSaveDatabase[$k]."'),t2.Datum_Dag,
		concat(round(3600000*(t2.S0_".($k+$o)." - t1.S0_".($k+$o).")/TIME_TO_SEC(TIMEDIFF( t2.Datum_Dag,t1.Datum_Dag )))), t2.S0_".($k+$o)." - t1.S0_".($k+$o).", '".$sNaamSaveDatabase[$k]."'
		from
		(select @rownumt1:=@rownumt1+1 samplenr, DATE_ADD(Datum_Dag, interval ".($z+1)." HOUR)Datum_Dag, S0_".($k+$o)." from logger, (select @rownumt1:=0) dummy1 order by Datum_Dag)t1,
		(select @rownumt2:=@rownumt2+1 samplenr, DATE_ADD(Datum_Dag, interval ".($z+1)." HOUR)Datum_Dag, S0_".($k+$o)." from logger, (select @rownumt2:=0) dummy2 order by Datum_Dag)t2
		where t2.samplenr = t1.samplenr +1 and t1.Datum_Dag >= '$dateTime' and MONTH(t1.Datum_Dag) = '$h' and TIME(t1.Datum_Dag) > '$zonop[$h]' and TIME(t1.Datum_Dag) < '$zononder[$h]' ";
		//echo $sql;
		mysqli_query($mysqli,$sql)or die ('SQL Error dagfout:'. mysqli_error($mysqli));
		++$h;
		} //end inverter loop 
	} //end sundown/sunset loop
//###################################################################
//Calculate data for month table                                   ##
//###################################################################
for ($k = 0; $k < count($sNaamSaveDatabase); ++$k)
{
$sql="
Replace into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)
select concat(DATE_FORMAT(Tijd_1,'%y-%m-%d'),'".$sNaamSaveDatabase[$k]."') as IndexMaand, Datum_Dag, Result as Geg_Maand, '".$sNaamSaveDatabase[$k]."' as Naam
from (
SELECT MAX( Datum_Dag ) AS Tijd_1, MIN( Datum_Dag ) AS Datum_Dag, MIN( S0_".($k+$o)." ) , MAX( S0_".($k+$o)." ) , MAX( S0_".($k+$o)." ) - MIN( S0_".($k+$o)." ) Result
FROM logger
GROUP BY DATE( Datum_Dag ) 
ORDER BY Datum_Dag)t3
where DATE(Datum_Dag) >= '$date' ";
//echo $sql;
mysqli_query($mysqli,$sql)or die ('SQL Error maandfout:'. mysqli_error($mysqli));
}
?>
<?php
include "Export.php";
?>
