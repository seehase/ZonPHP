<?php


include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

if (!isset($_SESSION['passok']))
    header('location:par_welcome.php');

include_once "../inc/connect.php";

// check if tables exists
$tablename = $table_prefix . "_parameters";
$result = mysqli_query($con, "SHOW TABLES LIKE '" . $tablename . "'");
$tables_exists = ($result->num_rows == 1);

if ($tables_exists) {
    $sqllaatstedag = "SELECT *
	FROM " . $table_prefix . "_dag 
	ORDER BY Datum_Dag DESC
	LIMIT 0, 10";

    $resultlaatstedag = mysqli_query($con, $sqllaatstedag) or die("Query failed. ERROR: " . mysqli_error($con));
    if (mysqli_num_rows($resultlaatstedag) == 0) {
        $alaatstedag[date('d/m/y G:i', time())] = "Geen data";
        $alaatstedagkWh[date('d/m/y G:i', time())] = "Geen data";
    } else {
        while ($row = mysqli_fetch_array($resultlaatstedag)) {
            $alaatstedag[date('d/m/y G:i', strtotime($row['Datum_Dag']))] = $row['Geg_Dag'];
            $alaatstedagkWh[date('d/m/y G:i', strtotime($row['Datum_Dag']))] = $row['kWh_Dag'];
        }
    }
//echo '<pre>'.print_r($alaatstedag, true).'</pre>';
    $sqleerstedag = "SELECT *
	FROM " . $table_prefix . "_dag 
	ORDER BY Datum_Dag ASC
	LIMIT 0, 10";

    $resulteerstedag = mysqli_query($con, $sqleerstedag) or die("Query failed. ERROR: " . mysqli_error($con));
    if (mysqli_num_rows($resulteerstedag) == 0) {
        $aeerstedag[date('d/m/y G:i', time())] = "Geen data";
        $aeerstedagkWh[date('d/m/y G:i', time())] = "Geen data";
    } else {
        while ($row = mysqli_fetch_array($resulteerstedag)) {
            $aeerstedag[date('d/m/y G:i', strtotime($row['Datum_Dag']))] = $row['Geg_Dag'];
            $aeerstedagkWh[date('d/m/y G:i', strtotime($row['Datum_Dag']))] = $row['kWh_Dag'];
        }
    }

    $sqleuro = "SELECT *
	FROM " . $table_prefix . "_euro 
	ORDER BY Datum_Euro ASC";

    $resulteuro = mysqli_query($con, $sqleuro) or die("Query failed. ERROR: " . mysqli_error($con));
    if (mysqli_num_rows($resulteuro) == 0) {
        $aeuro[date('d/m/y G:i', time())] = "Geen data";
    } else {
        while ($row = mysqli_fetch_array($resulteuro)) {
            $aeuro[date('d/m/y G:i', strtotime($row['Datum_Euro']))] = $row['Geg_Euro'];
        }
    }
//echo '<pre>'.print_r($aeuro, true).'</pre>';
    $sqlrefer = "SELECT *
	FROM " . $table_prefix . "_refer 
	ORDER BY Datum_Refer ASC";

    $resultrefer = mysqli_query($con, $sqlrefer) or die("Query failed. ERROR: " . mysqli_error($con));
    if (mysqli_num_rows($resultrefer) == 0) {
        $arefer[date('d/m/y G:i', time())] = "Geen data";
        $areferdag[date('d/m/y G:i', time())] = "Geen data";
    } else {
        while ($row = mysqli_fetch_array($resultrefer)) {
            $arefer[date('d/m/y G:i', strtotime($row['Datum_Refer']))] = $row['Geg_Refer'];
            $areferdag[date('d/m/y G:i', strtotime($row['Datum_Refer']))] = $row['Dag_Refer'];
        }
    }
    include_once "../inc/load_cache.php";
} else {
    echo "<h1>no database connection or tables do not exist</h1>";
}
include "par_header.php";
?>


<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytext">
        <div class="top-left"></div>
        <div class="top-right"></div>
        <div class="inside">

            <?php
            echo "<h2>Server parameter: </h2>";
            echo 'Current SQL version: ' . mysqli_get_server_info($con) . "<br />";
            echo 'Current PHP version: ' . phpversion() . "<br />";
            echo 'display_errors = ' . ini_get('display_errors') . "<br />";
            echo 'register_globals = ' . ini_get('register_globals') . "<br />";
            echo 'post_max_size = ' . ini_get('post_max_size') . "<br />";
            echo 'post_max_size in bytes = ' . return_bytes(ini_get('post_max_size')) . "<br />";

            function return_bytes($val)
            {
                $val = trim($val);
                $last = strtolower($val[strlen($val) - 1]);
                switch ($last) {
                    // The 'G' modifier is available since PHP 5.1.0
                    case 'g':
                        $val = floatval($val)*1024;
                    case 'm':
                        $val = floatval($val)*1024;
                    case 'k':
                        $val = floatval($val)*1024;
                }

                return $val;
            }

            echo "<hr><h2>Connection parameter: </h2>";
            echo "sserver: $sserver<br />";
            echo "susername: $susername<br />";
            echo "sdatabase_name: $sdatabase_name<br />";
            echo "table_prefix: $table_prefix<br />";

            echo "<hr><h2>Parameter: </h2>";
            echo date_default_timezone_get() . ' => ' . date('P') . ' => ' . date('Z');
            echo "<br />";
            echo date("Y-m-d H:i:s", time());
            // echo "<br />locale: ";
            // echo locale_get_default();
            echo "<br />";

            if ($tables_exists) {
                echo 'isorteren' . '=' . $param['isorteren'];
                echo "<br />";
                echo '$table_prefix=' . $table_prefix;
                echo "<br />";
                echo '$dstartdatum=' . $dstartdatum;
                echo "<br />";

                echo '$sNaamSaveDatabase=';
                foreach ($sNaamSaveDatabase as $key => $snaam)
                    echo $snaam . ',';
                echo "<br />";
                echo '$ieffectief_kwpiek=';
                foreach ($ieffectief_kwpiek as $key => $fw)
                    echo $fw . ',';
                echo "<br />";
                echo 'coefficient' . '=' . $param['coefficient'];
                echo "<br />";
                echo 'plantname' . '=' . $param['plantname'];
                echo "<br />";
                echo 'sInvullen_gegevens' . '=' . $param['sInvullen_gegevens'];
                echo "<br />";
                echo 'sURL_link' . '=' . $param['sURL_link'];
                echo "<br />";
                echo 'color_chartbackground' . '=' . $colors['color_chartbackground'];
                echo "<br />";
                echo 'color_chartbar1' . '=' . $colors['color_chartbar1'];
                echo "<br />";
                echo "color_chartbar_piek1" . '=' . $colors['color_chartbar_piek1'];
                echo "<br />";
                echo '$iveromvormers=' . $iveromvormers;
                echo "<br />";
                echo 'sNaamVoorOpWebsite' . '=' . $param['sNaamVoorOpWebsite'];
                echo "<br />";
                echo 'sWebsite_Installateur' . '=' . $param['sWebsite_Installateur'];
                echo "<br />";
                echo 'sNaam_Installateur' . '=' . $param['sNaam_Installateur'];
                echo "<br />";
                echo 'sSoort_pannel_aantal' . '=' . $param['sSoort_pannel_aantal'];
                echo "<br />";
                echo 'sOrientatie' . '=' . $param['sOrientatie'];
                echo "<br />";
                echo 'sData_Captatie' . '=' . $param['sData_Captatie'];
                echo "<br />";
                echo 'sPlaats' . '=' . $param['sPlaats'];
                echo "<br />";
                echo "<hr><h2>Database sample values: </h2>";
            };
            ?>

            <div id="eerste10">
                <br/>
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">
                            <center>Eerste 10 records tabel tgeg_dag</center>
                        </td>
                    </tr>
                    <tr>
                        <td><font size="-1">Datum_Dag</font></td>
                        <td><font size="-1">Geg_Dag</font></td>
                        <td><font size="-1">kWh_Dag</font></td>
                    </tr>
                    <?php
                    if ($tables_exists) {
                        foreach ($aeerstedag as $axas => $ayas) {
                            echo("<tr>
						<td><font size='-1'>" . $axas . "</font></td>
						<td><font size='-1'>" . $ayas . "</font></td>
						<td><font size='-1'>" . $aeerstedagkWh[$axas] . "</font></td>
						</tr>");
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div id="laatste10">
                <br/>
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">
                            <center>Laatste 10 records tabel tgeg_dag</center>
                        </td>
                    </tr>
                    <tr>
                        <td><font size="-1">Datum_Dag</font></td>
                        <td><font size="-1">Geg_Dag</font></td>
                        <td><font size="-1">kWh_Dag</font></td>
                    </tr>
                    <?php
                    if ($tables_exists) {
                        foreach ($alaatstedag as $axas => $ayas) {
                            echo("<tr>
						<td><font size='-1'>" . $axas . "</font></td>
						<td><font size='-1'>" . $ayas . "</font></td>
						<td><font size='-1'>" . $alaatstedagkWh[$axas] . "</font></td>
						</tr>");
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div id="refer">
                <br/>
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">
                            <center>Records tabel tgeg_refer</center>
                        </td>
                    </tr>
                    <tr>
                        <td><font size="-1">Datum_refer</font></td>
                        <td><font size="-1">Geg_refer</font></td>
                        <td><font size="-1">Dag_Refer</font></td>
                    </tr>
                    <?php
                    if ($tables_exists) {
                        foreach ($arefer as $axas => $ayas) {
                            echo("<tr>
						<td><font size='-1'>" . $axas . "</font></td>
						<td><font size='-1'>" . $ayas . "</font></td>
						<td><font size='-1'>" . $areferdag[$axas] . "</font></td>
						</tr>");
                        };
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div id="euro">
                <br/>
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">
                            <center>Records tabel tgeg_euro</center>
                        </td>
                    </tr>
                    <tr>
                        <td><font size="-1">Datum_Euro</font></td>
                        <td><font size="-1">Geg_Euro</font></td>
                    </tr>
                    <?php
                    if ($tables_exists) {
                        foreach ($aeuro as $axas => $ayas) {
                            echo("<tr>
						<td><font size='-1'>" . $axas . "</font></td>
						<td><font size='-1'>" . $ayas . "</font></td>
						</tr>");
                        };
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div id="maand">
                <br/>
                <table>
                    <tbody>
                    <tr>
                        <td colspan="3">
                            <center>Records first 10 entries of tabel tgeg_maand</center>
                        </td>
                    </tr>
                    <tr>
                        <td><font size="-1">Datum_maand</font></td>
                        <td><font size="-1">Geg_maand</font></td>
                        <td><font size="-1">WR</font></td>
                    </tr>
                    <?php

                    if ($tables_exists) {
                        $sqlmaand = "SELECT * FROM " . $table_prefix . "_maand ORDER BY Datum_maand ASC limit 10";
                        $resultmaand = mysqli_query($con, $sqlmaand) or die("Query failed. ERROR: " . mysqli_error($con));
                        if (mysqli_num_rows($resultmaand) == 0) {
                            echo("<tr>
						<td><font size='-1'> &nbsp;</font></td>
						<td><font size='-1'>\"Geen data\"</font></td> 						
						</tr>");

                        } else {
                            while ($row = mysqli_fetch_array($resultmaand)) {

                                echo("<tr>
						<td><font size='-1'>" . date('d/m/y G:i', strtotime($row['Datum_Maand'])) . "</font></td>
						<td><font size='-1'>" . $row['Geg_Maand'] . "</font></td> 
						<td><font size='-1'>" . $row['Naam'] . "</font></td>
						</tr>");
                            }
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <?php
            echo "<hr><h2>PHP info: </h2>";
            phpinfo(INFO_ALL);
            ?>
            <p class="nobottomgap"></p>
        </div>
        <div class="bottom-left"></div>
        <div class="bottom-right"></div>
    </div>


</div>
</body>
</html>
