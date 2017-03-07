<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";
include_once "../inc/connect.php";

if (!isset($_SESSION['passok']))
    header('location:install/par_welcome.php');

include "par_header.php";

// check if tables exists
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));

if (!isset($_SESSION['Wie']))
{
    die(header('location:opstart_installatie.php?fout=parameter'));
}

$sqlverbruik = "SELECT *
	FROM " . $table_prefix . "_verbruik
	WHERE Naam='" . $_SESSION['Wie'] . "'
	ORDER BY Datum_Verbruik DESC
	LIMIT 0 , 13 ";
//echo$sqlverbruik;
$resultverbruik = mysqli_query($con, $sqlverbruik) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($resultverbruik) == 0) {
    $iniets = 0;
} else {
    $iniets = 1;
    $adag = array();
    $anacht = array();
    while ($row = mysqli_fetch_array($resultverbruik)) {
        $adag[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_MeterDag'];
        $anacht[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_MeterNacht'];
        $adagverb[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_Verbruik_Dag'];
        $anachtverb[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_Verbruik_Nacht'];
    }
    ksort($adag);
}
$bkannietsave = 0;
//echo "<pre>".print_r($_POST,true)."/<pre>";
if ($iniets != 0) {
    foreach ($adag as $key => $fmeterstand) {
        if (!empty($_POST[$key . 'dag'])) {
            //echo $_POST[$key.'dag'];
            if (!is_numeric($_POST[$key . 'dag'])) {
                $_POST[$key . 'dag'] = $txt["verkeerd"];
                $bkannietsave = 1;
            }
        } else {
            $_POST[$key . 'dag'] = $fmeterstand;
        }
    }
    foreach ($anacht as $key => $fmeterstand) {
        if (!empty($_POST[$key . 'nacht'])) {
            if (!is_numeric($_POST[$key . 'nacht'])) {
                $_POST[$key . 'nacht'] = $txt["verkeerd"];
                $bkannietsave = 1;
            }
        } else {
            //if(!isset($arefer[$i])) $arefer[$i]=0;
            $_POST[$key . 'nacht'] = $fmeterstand;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['ndatum'])) {
        $control = controledatum($_POST['ndatum']);
        if ($control == "nok") {
            $txt["verkeerd"] .= "<br />" . $txt["verbuikdatum"];
            $bkannietsave = 1;
        } else {
            if (!empty($_POST['ndag']) && !is_numeric($_POST['ndag'])) {
                $txt["verkeerd"] .= "<br />" . $txt["verbuikdag"];
                $bkannietsave = 1;
            }
            if (!empty($_POST['nnacht']) && !is_numeric($_POST['nnacht'])) {
                $txt["verkeerd"] .= "<br />" . $txt["verbuiknacht"];
                $bkannietsave = 1;
            }
        }

    }
}
?>
<?php
function controledatum($date)
{
    $date = str_replace(array('.', '/ ', '-'), '/', $date);
    $d_m_j = explode("/", $date);
    if (!isset($d_m_j[0]) || !is_numeric($d_m_j[0])) return "nok";
    if (!isset($d_m_j[1]) || !is_numeric($d_m_j[1])) return "nok";
    if (!isset($d_m_j[2]) || !is_numeric($d_m_j[2])) return "nok";

    If (!checkdate($d_m_j[1], $d_m_j[0], $d_m_j[2])) {
        return "nok";
    } else {
        return ($d_m_j[2] . "-" . $d_m_j[1] . "-" . $d_m_j[0]);
    }
}

?>

<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytext">
        <div class="inside">
            <?php echo $txt["verbruikhoofding"]; ?>
            <br /><br />
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if ($bkannietsave == 0) {
                    $dummydag = 0;
                    $dummynacht = 0;
                    if ($iniets != 0) {
                        foreach ($adag as $key => $fmeterstand) {
                            if ($dummydag != 0) {
                                $sqlsave = "UPDATE " . $table_prefix . "_verbruik SET Geg_MeterDag=" . $_POST[$key . 'dag'] . ",
											Geg_MeterNacht =" . $_POST[$key . 'nacht'] . ",Geg_Verbruik_Dag=" . ($_POST[$key . 'dag'] - $dummydag) . "
											,Geg_Verbruik_Nacht=" . ($_POST[$key . 'nacht'] - $dummynacht) . "
											WHERE Datum_Verbruik like '" . date('Y-m', strtotime($key)) . "%'and Naam='" . $_SESSION['Wie'] . "'";
                                //echo $sqlsave;
                                mysqli_query($con, $sqlsave, $con) or die("Query failed. sql_save: " . mysqli_error($con));
                            }
                            $dummydag = $_POST[$key . 'dag'];
                            $dummynacht = $_POST[$key . 'nacht'];
                        }
                    }
                    if (!empty($_POST['ndag'])) {
                        $sqldel = "DELETE FROM " . $table_prefix . "_verbruik WHERE Naam = '" . $_SESSION['Wie'] . "'and Datum_Verbruik like'" . date('Y-m', strtotime($control)) . "%'";
                        mysqli_query($con, $sqldel, $con) or die("Query failed. sql_wis: " . mysqli_error($con));

                        $sqlprev = "SELECT *
							FROM " . $table_prefix . "_verbruik
							WHERE Naam='" . $_SESSION['Wie'] . "' and Datum_Verbruik < '" . $control . "'
							ORDER BY `Datum_Verbruik` DESC 
							LIMIT 1";
                        //echo $sqlprev;
                        $resultprev = mysqli_query($con, $sqlprev) or die("Query failed. ERROR: " . mysqli_error($con));
                        if (mysqli_num_rows($resultprev) != 0) {
                            while ($row = mysqli_fetch_array($resultprev)) {
                                $fdagprev = $row['Geg_MeterDag'];//echo $row['Geg_MeterDag']."----";
                                $fnachtprev = $row['Geg_MeterNacht'];//echo $row['Geg_MeterNacht']."<br />";
                            }
                        } else {
                            $fdagprev = $_POST['ndag'];
                            $fnachtprev = $_POST['nnacht'];
                        }

                        $sqlsave = "INSERT INTO " . $table_prefix . "_verbruik(IndexVerbruik,Datum_Verbruik,Geg_Verbruik_Dag,Geg_Verbruik_Nacht,
								Naam,Geg_MeterDag,Geg_MeterNacht)
								VALUES ('" . $control . $_SESSION['Wie'] . "','" . $control . "'," . ($_POST['ndag'] - $fdagprev) . "," . ($_POST['nnacht'] - $fnachtprev) . ",'" . $_SESSION['Wie'] . "'," . $_POST['ndag'] . "," . $_POST['nnacht'] . ")";
                        //echo $sqlsave;
                        mysqli_query($con, $sqlsave, $con) or die("Query failed. sql_save: " . mysqli_error($con));

                        $sqlnex = "SELECT *
							FROM " . $table_prefix . "_verbruik
							WHERE Naam='" . $_SESSION['Wie'] . "' and Datum_Verbruik > '" . $control . "'
							ORDER BY `Datum_Verbruik` ASC 
							LIMIT 1";
                        //echo $sqlnex;
                        $resultnex = mysqli_query($con, $sqlnex) or die("Query failed. ERROR: " . mysqli_error($con));
                        if (mysqli_num_rows($resultnex) != 0) {
                            while ($row = mysqli_fetch_array($resultnex)) {
                                $fdagpnex = $row['Geg_MeterDag'];
                                $fnachtnex = $row['Geg_MeterNacht'];
                                $datumnext = $row['Datum_Verbruik'];
                            }
                            $sqlsave = "UPDATE " . $table_prefix . "_verbruik SET Geg_Verbruik_Dag=" . ($fdagpnex - $_POST['ndag']) . "
										,Geg_Verbruik_Nacht=" . ($fnachtnex - $_POST['nnacht']) . "
										WHERE Datum_Verbruik= '" . $datumnext . "'and Naam='" . $_SESSION['Wie'] . "'";
                            //echo $sqlsave;
                            mysqli_query($con, $sqlsave, $con) or die("Query failed. sql_save: " . mysqli_error($con));
                        }


                    }
                    echo "" . $txt["opgeslagen"] . "<br />";//header('location:install/par_powerusage.php?fout=connect');

                    $sqlverbruik = "SELECT *
							FROM " . $table_prefix . "_verbruik
							WHERE Naam='" . $_SESSION['Wie'] . "'
							ORDER BY Datum_Verbruik ASC
							LIMIT 0 , 12 ";
                    $resultverbruik = mysqli_query($con, $sqlverbruik) or die("Query failed. ERROR: " . mysqli_error($con));
                    if (mysqli_num_rows($resultverbruik) == 0) {
                        $iniets = 0;
                    } else {
                        $adag = array();
                        $anacht = array();
                        $iniets = 1;
                        while ($row = mysqli_fetch_array($resultverbruik)) {
                            $adag[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_MeterDag'];
                            $anacht[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_MeterNacht'];
                            $adagverb[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_Verbruik_Dag'];
                            $anachtverb[date('Y-m-d', strtotime($row['Datum_Verbruik']))] = $row['Geg_Verbruik_Nacht'];
                            $_POST[date('Y-m-d', strtotime($row['Datum_Verbruik'])) . 'dag'] = $row['Geg_MeterDag'];
                            $_POST[date('Y-m-d', strtotime($row['Datum_Verbruik'])) . 'nacht'] = $row['Geg_MeterNacht'];
                        }
                        ksort($adag);
                    }
                } else
                    echo "" . $txt["verkeerd"] . "<br />";
            }
            ?>
            <FORM METHOD="post" ACTION="">
                <table>
                    <tbody>
                    <tr>
                        <td><?php echo $txt["datum"]; ?></td>
                        <td><?php echo $txt["verbruikd"]; ?></td>
                        <td><?php echo $txt["verbruikn"]; ?></td>
                        <td><?php echo $txt["verbruikdv"]; ?></td>
                        <td><?php echo $txt["verbruiknv"]; ?></td>
                        <td><?php echo $txt["verbruikdm"]; ?></td>
                        <td><?php echo $txt["verbruiknm"]; ?></td>
                    </tr>
                    <?php
                    $dummydag = 0;
                    if ($iniets != 0) {
                        //echo "<pre>".print_r($adag,true)."</pre>";
                        foreach ($adag as $key => $fmeterstand) {

                            echo "<tr>
											<td>" . date('d-m-Y', strtotime($key)) . "</td>
											<td>" . number_format($fmeterstand, 0, ',', '.') . "</td>
											<td>" . number_format($anacht[$key], 0, ',', '.') . "</td>
											<td>" . number_format($adagverb[$key], 0, ',', '.') . "</td>
											<td>" . number_format($anachtverb[$key], 0, ',', '.') . "</td>";
                            if ($dummydag != 0) {
                                echo "<td><input style='width: 80px;' type='text' name='" . $key . "dag' value='";
                                if (!empty($_POST[$key . 'dag']))
                                    echo $_POST[$key . 'dag'];
                                echo "'></td>
											<td><input style='width: 80px;'type='text' name='" . $key . "nacht' value='";
                                if (!empty($_POST[$key . 'nacht']))
                                    echo $_POST[$key . 'nacht'];
                                echo "'></td>
											</tr>";
                            }
                            $dummydag = $fmeterstand;
                        }
                    }
                    echo "<tr>
								<td><input style='width: 80px;'type='text' name='ndatum' value='" . date('d-m-Y', time()) . "'></td>
								<td>" . $txt["verbruikdatum"] . "</td>
								<td></td>
								<td></td>
								<td>" . $txt["verbruiknieuw"] . "</td>
								<td><input style='width: 80px;'type='text' name='ndag' value=''></td>
								<td><input style='width: 80px;'type='text' name='nnacht' value=''></td>
								</tr>";
                    ?>
                    </tbody>
                </table>
                <br />
                <INPUT TYPE="submit" VALUE="<?php echo $txt["save"]; ?>" onClick="return confirmSubmit()">
            </FORM>
        </div>
    </div>
</div>
</body>
</html>