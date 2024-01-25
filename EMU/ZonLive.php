<?php
//#######################################################################################
// New Version, compatible with ZonPHP Release 2023.08.09                              ##
// Importer for CSV-data  E.M. Plankeel                                                ##
// Date  2023-08-13                                                                    ##
// Script to load csv data into a ZonPHP database                                      ##
// Written for PHP 8 onwards en MySQLi                                                 ##
// For security script removed from public_html                                        ##
// Better fault handling, improved routine to load data                                ##
// csv times are in UTC !!!!!, database times also                                     ##
// csv example for two inverters.                                                      ##
// DateTime ; kWh1 ; kWh2
// Field separator = ;                                                                 ##
// Decimal = .                                                                         ##
// No header row                                                                       ##
// 2023-08-12 07:05:00;10691.000;14119.599                                             ##
// 2023-08-12 07:10:00;10691.006;14119.606                                             ##
// 2023-08-12 07:15:00;10691.015;14119.618                                             ##
// 2023-08-12 07:20:00;10691.020;14119.625                                             ##
// 2023-08-12 07:25:00;10691.024;14119.630                                             ##
//#######################################################################################
// variables to be given in section below. All entries between " " signs               ## 
//#######################################################################################
$sserver = "localhost";                                  // localhost
$sdatabase_name = " ";                                   // database name
$susername = " ";                                        // database username
$spassword = " ";                                        // database password
$table_prefix = "tgeg";                                  // database prefix
$plantNames = " ";                                       // plants separated by comma
$csv_prefix = "/home/deb12345/domains/abcd.nl/csvdt/";   // full path to your csv files
$PVO_SYS_ID = " ";                                       // PV Output System ID
$PVO_API = " ";                                          // PV Output API key
$dstartdate = " ";                                       // Installation date yyyy-mm-dd
//#######################################################################################
// Do not change anything below                                                        ##
//#######################################################################################

$sql_dag = "Replace into `" .  $table_prefix . "_dag` (`IndexDag`, `Datum_Dag`, `Geg_Dag`, `kWh_Dag`, `Naam`) VALUES";
$sql_maand = "Replace into `" .  $table_prefix . "_maand` (`IndexMaand`, `Datum_Maand`, `Geg_Maand`, `Naam`) VALUES";
$mysqli = new mysqli($sserver,$susername,$spassword,$sdatabase_name);
try {
    $conn = new PDO("mysql:host=$sserver;dbname=$sdatabase_name", $susername, $spassword);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully \n";
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?>
 <?php
//###################################################################
//
//Look for last update table, last day will always be rewritten.
//
//###################################################################
define("PLANT_NAMES", explode(",",$plantNames));
$sql="SELECT * FROM " .  $table_prefix . "_dag ORDER BY Datum_Dag DESC LIMIT 1"; 
$result = $mysqli->query($sql) or die("Fout in SQL: ".mysqli_error($mysqli));
if (mysqli_num_rows($result) == 0) 
{
Echo "Database is empty, being filled from date $dstartdate \n"; 
$dateTime=$dstartdate;
}
else
{
	while($row = mysqli_fetch_array($result))
	{
	$dateTime = $row['Datum_Dag']; 
	}
}
// Start over from some $dateTime? 
// Remove // below temporarely and adjust $dateTime accordingly
// $dateTime='2019-02-23';
//
$date=$datum = date('Y-m-d', strtotime($dateTime));
$now=date('Y-m-d', time());
//###################################################################
//	Load CSV data per day in table   
//###################################################################
$file=$datum.'.csv';
$maandtot ="";
while ($datum <= $now)
{
$sun_info = date_sun_info(strtotime($datum), 52.38, 4.88);
$sunrise=gmdate("Y-m-d H:i:s",$sun_info['sunrise']);
$sunset=gmdate("Y-m-d H:i:s",$sun_info['sunset']);
$sun=gmdate("Y-m-d",$sun_info['sunset']);
$suns=gmdate("Ymd",$sun_info['sunset']);
$datum = date( 'Y-m-d',strtotime(date("Y-m-d", strtotime($datum)) . " +1 day"));
$filename = $csv_prefix . $file;

if (file_exists($filename)) 
	{
    echo "Finding file $file \n ";

	if ( strpos(file_get_contents($filename), "\"") || strpos(file_get_contents($filename), ",") !== false) 
			{    	
			echo "The file $file contains commas or apostrophs \n";	
			}


			else 	
			{
			 echo "Pre-processing file $file \n";// do stuff
	
		$opbr="";
		$dagopbr="";
		$row = 1;
		//$row2=$row+1;
		$result2 = "";
			if ($text = file_get_contents("{$filename}"))
				{
				$handle = fopen('data://text/plain,' . $text,'r');
				$data1=array();
				$data2=array();
				$data3=array();
				
				while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) 
				{
					
					$num = count($data);
					$data3[$row] = $data;
					if ($sun == date('Y-m-d',strtotime($data[0])) && $sunrise < $data[0] && $data[0] < $sunset ) 
							{
							$data2[$row] = $data;
							$first = $data2[array_key_first($data2)];
							//if ($data2[$row][1]-$data3[$row-1][1] == $data2[$row][1])
							$opbr="";
							//else
							for ($k = 0; $k < count(PLANT_NAMES); ++$k)
								{
								$opbr = "('" . $data2[$row][0] .PLANT_NAMES[$k] . "', '" . $data2[$row][0]  . "', "  . ($data2[$row][$k+1]-$data3[$row-1][$k+1])* (60/((strtotime($data2[$row][0]) - strtotime($data3[$row-1][0])) /60))*1000 . ", " . $data2[$row][$k+1]-$data3[$row-1][$k+1].   ", '"   . PLANT_NAMES[$k]  . "'), " ;
								$dagopbr .= $opbr;
								}				
							}
					$row++;
				}
				fclose($handle);
				$dagopbr = substr($dagopbr, 0, -2);
				
				//################## Dagcijfers klopt #################
				//echo 'SQL_dag',$sql_dag . $dagopbr,'<BR>','<BR>','<BR>';
				mysqli_query($mysqli,$sql_dag . $dagopbr)or die ('SQL Error dagfout:'. mysqli_error($mysqli));
				// time in UTC 
				//############# dagcijfers #################
				
				
				for ($k = 0; $k < count(PLANT_NAMES); ++$k)
						{
						$datumkort = date('Y-m-d', strtotime($data3[1][0]));
						$dagtotaal = "('" . $datumkort .  PLANT_NAMES[$k] . "', '" . $data3[$row-1][0]. "', " .     $data3[$row-1][$k+1] - $data3[1][$k+1]   .", '" . PLANT_NAMES[$k] . "'),";
						//echo $dagtotaal;
						$maandtot .= $dagtotaal;
						}
				}
				
				else 	
				{		throw new Exception('Error reading csv file.');	

				}
			}
	} 
	else 
	{    
	echo "The file $file does not exist",'<BR>'; 
	}

//if needed, go to next day
$file=$datum.'.csv';
}
$maandtot = substr($maandtot,0,-1);

mysqli_query($mysqli,$sql_maand . $maandtot )or die ('SQL Error maandfout:'. mysqli_error($mysqli));
?>
<?php
echo "Ready processing file \n";
?>
<?php
include "Exporter.php";
echo 'Exported data to PV Output';
?>
