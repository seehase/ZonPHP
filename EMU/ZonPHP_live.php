<?php
//###################################################################
// Version 5.1 Import from SBF-spot CSV-data  E.M. Plankeel        ##
// Date  2023-06-12                                                ##
// Script to load csv data into a ZonPHP database                  ##
// Written for PHP 8 onwards en MySQLi                             ##
// For security script removed from public_html                    ##
// Better fault handling, improved routine to load data            ##
// Routine skips records between sunset and sunrise                ##
// csv times are in UTC !!!!!, database times also                 ##
//###################################################################
//csv format                                                       ##
//	DateTime			Inv 2	Inv 2                              ##
// 2019-12-28 14:35:00;2211.091;2862.796						   ##
// 2019-12-28 14:40:00;2211.095;2862.802                           ##
// 2019-12-28 14:45:00;2211.099;2862.806	                       ##
// 2019-12-28 14:50:00;2211.101;2862.810                           ##
// No ,,, or """" allowed                                          ##
//###################################################################
// Known issues                                                    ##
// Absolute path to csv data files should be filled in manually    ##
$file_path = "/home/deb95965/domains/abcd.nl/dt/";//               ##
//                                                                 ##
//###################################################################
//For future use in par_menu, it should work but not yet tested    ##
//Purpose is to re-import one or multiple days                     ##
//format date like 2021-06-23                                      ##
//                                                                 ##
$import_start = null;//                                              ##
$import_end = null;//                                                ##
//                                                                 ##
//                                                                 ##
//###################################################################

include_once "../ZonPHP/inc/init.php";
//Connect to the database
$sserver = $params['database']['host']; //"localhost";//localhost
$sdatabase_name = $params['database']['database']; //"database name";//database name
$susername = $params['database']['username']; //"database username";// database username
$spassword = $params['database']['password']; //"database password";// database password

$sql_dag = "Replace into `" . TABLE_PREFIX . "_dag` (`IndexDag`, `Datum_Dag`, `Geg_Dag`, `kWh_Dag`, `Naam`) VALUES";
$sql_maand = "Replace into `" . TABLE_PREFIX . "_maand` (`IndexMaand`, `Datum_Maand`, `Geg_Maand`, `Naam`) VALUES";

$mysqli = new mysqli($sserver, $susername, $spassword, $sdatabase_name);
try {
    $conn = new PDO("mysql:host=$sserver;dbname=$sdatabase_name", $susername, $spassword);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully", '<BR>';
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<?php
STARTDATE = $params['installationDate'];

foreach (explode(',', $params['plants']) as $key => $stringnaam) {
    PLANTS[] = $stringnaam;
    $teller++;
}
?>
<?php
//###################################################################
//
//Look for last update table , last day will always be rewritten.
//
//###################################################################
$sql = "SELECT * FROM " . TABLE_PREFIX . "_dag ORDER BY Datum_Dag DESC LIMIT 1";
$result = $mysqli->query($sql) or die("Fout in SQL: " . mysqli_error($mysqli));
if (mysqli_num_rows($result) == 0) {
    echo "Databasetabel is leeg, wordt gevuld vanaf startdatum STARTDATE", '<BR>';
    $dateTime = STARTDATE;
} else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}
if (isset($import_start)) {
    $dateTime = $import_start;
}

$date = $datum = date('Y-m-d', strtotime($dateTime));

$now = date('Y-m-d', time());
 
if (isset($import_end)) {
    $now = $import_end;
}

//###################################################################
//	Load CSV data per day in table                                 ##
//                                                                 ##
//                                                                 ##
//###################################################################
$file = $datum . '.csv';
$maandtot = "";
while ($datum <= $now) {
    $sun_info = date_sun_info(strtotime($datum), 52.38, 4.88);
    $sunrise = gmdate("Y-m-d H:i:s", $sun_info['sunrise']);
    $sunset = gmdate("Y-m-d H:i:s", $sun_info['sunset']);
    $sun = gmdate("Y-m-d", $sun_info['sunset']);
    $suns = gmdate("Ymd", $sun_info['sunset']);
    $datum = date('Y-m-d', strtotime(date("Y-m-d", strtotime($datum)) . " +1 day"));

//###################################################################
// problems with getting the full path to the right directory      ##
// to be fixed manual                                              ##
//###################################################################
    $filename = $file_path . $file;

    if (file_exists($filename)) {
        echo "Finding file $file ";

        if (strpos(file_get_contents($filename), "\"") || strpos(file_get_contents($filename), ",") !== false) {
            echo "The file $file contains commas or apostrophs", '<BR>';
        } else {
            echo "pre-processing file $file", '<BR>';// do stuff

            $opbr = "";
            $dagopbr = "";
            $row = 1;
            //$row2=$row+1;
            $result2 = "";
            if ($text = file_get_contents("{$filename}")) {
                $handle = fopen('data://text/plain,' . $text, 'r');
                $data1 = array();
                $data2 = array();
                $data3 = array();

                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

                    $num = count($data);
                    $data3[$row] = $data;
                    if ($sun == date('Y-m-d', strtotime($data[0])) && $sunrise < $data[0] && $data[0] < $sunset) {
                        $data2[$row] = $data;
                        $first = $data2[array_key_first($data2)];
                        //if ($data2[$row][1]-$data3[$row-1][1] == $data2[$row][1])
                        $opbr = "";
                        //else
                        for ($k = 0; $k < count(PLANTS); ++$k) {
                            $opbr = "('" . $data2[$row][0] . PLANTS[$k] . "', '" . $data2[$row][0] . "', " . ($data2[$row][$k + 1] - $data3[$row - 1][$k + 1]) * (60 / ((strtotime($data2[$row][0]) - strtotime($data3[$row - 1][0])) / 60)) * 1000 . ", " . $data2[$row][$k + 1] - $data3[$row - 1][$k + 1] . ", '" . PLANTS[$k] . "'), ";
                            $dagopbr .= $opbr;
                        }
                    }
                    $row++;
                }
                fclose($handle);
                $dagopbr = substr($dagopbr, 0, -2);

                //################## Dagcijfers klopt #################
                //echo 'SQL_dag',$sql_dag . $dagopbr,'<BR>','<BR>','<BR>';
                mysqli_query($mysqli, $sql_dag . $dagopbr) or die ('SQL Error dagfout:' . mysqli_error($mysqli));
                // time in UTC
                //############# dagcijfers #################


                for ($k = 0; $k < count(PLANTS); ++$k) {
                    $datumkort = date('Y-m-d', strtotime($data3[1][0]));
                    $dagtotaal = "('" . $datumkort . PLANTS[$k] . "', '" . $data3[$row - 1][0] . "', " . $data3[$row - 1][$k + 1] - $data3[1][$k + 1] . ", '" . PLANTS[$k] . "'),";
                    //echo $dagtotaal;
                    $maandtot .= $dagtotaal;
                }
            } else {
                throw new Exception('Error reading csv file.');

            }
        }
    } else {
        echo "The file $file does not exist", '<BR>';
    }

//zonodig naar volgende dag
    $file = $datum . '.csv';
}
$maandtot = substr($maandtot, 0, -1);
//NB Tot op heden schrijft ZonPHP het dagmax weg op 00:00:00 van die dag !!!!!
//Dat matcht niet met UTC
//echo 'SQL_maand ',$sql_maand . $maandtot,'<BR>','<BR>','<BR>';	
mysqli_query($mysqli, $sql_maand . $maandtot) or die ('SQL Error maandfout:' . mysqli_error($mysqli));
?>
<?php
echo 'klaar';
?>