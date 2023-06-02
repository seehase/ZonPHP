<?php

include_once "../parameters.php";
include_once "../inc/sessionstart.php";
include_once "../inc/connect.php";

// check if tables exists
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));

if (!isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder")
    header('location:install/par_welcome.php');


include "par_header.php";


$sqlpar = "SELECT * 
	FROM " . $table_prefix . "_parameters
	WHERE Variable = 'sNaamSaveDatabasest'";
$resultpar = mysqli_query($con, $sqlpar) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($resultpar) != 0) {
    while ($row = mysqli_fetch_array($resultpar)) {
        $anamen = $row['Waarde'];
    }
    $akommanamen = explode(',', $anamen);
} else {
    die(header('location:opstart_installatie.php?fout=parameter'));
}

$current_name = $akommanamen[0];

if (isset($_GET['naam'])) {
    $current_name = $_GET['naam'];
}


$arefer[date('d/m/y G:i', time())] = "Geen data";
$areferdag[date('d/m/y G:i', time())] = "Geen data";

$sqlrefer = "SELECT *
	FROM " . $table_prefix . "_refer 
	WHERE Naam='" . $current_name . "'
	ORDER BY Datum_Refer ASC";

$resultrefer = mysqli_query($con, $sqlrefer) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($resultrefer) != 0) {
    while ($row = mysqli_fetch_array($resultrefer)) {
        $arefer[date('n', strtotime($row['Datum_Refer']))] = $row['Geg_Refer'];
    }
}

$bkannietsave = 0;
for ($i = 1; $i <= 12; $i++) {
    if (!empty($_POST[$i])) {
        if (!is_numeric($_POST[$i])) {
            $_POST[$i] = $txt["verkeerd"];
            $bkannietsave = 1;
        }
    } else {
        if (!isset($arefer[$i])) $arefer[$i] = 0;
        $_POST[$i] = $arefer[$i];
    }
}

?>
<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center"><?php echo $txt["reference"]; ?></h2>
            <hr>
            <?php echo $txt["refwaarden"]; ?>
            <a href="https://re.jrc.ec.europa.eu/pvg_tools/en/#api_5.1" TARGET="_blank"><?php echo $txt["klik"]; ?>
                .</a><br/><br/>
            <?php echo "Name: " . $current_name . "<br />"; ?>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if ($bkannietsave == 0) {
                    $sqldel = "DELETE FROM " . $table_prefix . "_refer WHERE Naam = '" . $current_name . "'";
                    mysqli_query($con, $sqldel) or die("Query failed. sql_wis: " . mysqli_error($con));
                    $sqlsave = "INSERT INTO " . $table_prefix . "_refer(Datum_Refer,Geg_Refer,Dag_Refer,Naam)values";
                    for ($i = 1; $i <= 12; $i++) {
                        $sqlsave .= "('2009-" . $i . "-01'," . $_POST[$i] . "," . ($_POST[$i] / cal_days_in_month(CAL_GREGORIAN, $i, 2009)) . ",'" . $current_name . "'),";
                    }
                    $sqlsave = substr($sqlsave, 0, -1);
                    mysqli_query($con, $sqlsave) or die("Query failed. sql_save: " . mysqli_error($con));
                    echo "<font size='0'color='#00AA00'>" . $txt["opgeslagen"] . "</font><br />";
                    $sqlrefer = "SELECT *
							FROM " . $table_prefix . "_refer 
							WHERE Naam='" . $current_name . "'
							ORDER BY Datum_Refer ASC";
                    $resultrefer = mysqli_query($con, $sqlrefer) or die("Query failed. ERROR: " . mysqli_error($con));
                    if (mysqli_num_rows($resultrefer) == 0) {
                        $arefer[date('d/m/y G:i', time())] = "Geen data";
                        $areferdag[date('d/m/y G:i', time())] = "Geen data";
                    } else {
                        while ($row = mysqli_fetch_array($resultrefer)) {
                            $arefer[date('n', strtotime($row['Datum_Refer']))] = $row['Geg_Refer'];
                        }
                    }
                } else
                    echo "<font size='0'color='#ff0000'>" . $txt["verkeerd"] . "</font><br />";
            }
            ?>

            <?php if (count($akommanamen) > 1) {
                echo '<br /><label>Kies een installatie of naam:</label>
					<select name="keuzenaam" onchange="if(this.selectedIndex > 0){location.href = this.options[this.selectedIndex].value;}">
					<option  SELECTED>';
                if (isset($_GET['naam'])) echo $_GET['naam'];
                foreach ($akommanamen as $key => $naam)
                    echo '<option value="?naam=' . $naam . '">' . $naam . ' </option>';
                echo '
			        </select>
			        ';
            }
            ?>

            <br/>
            <hr>
            <FORM METHOD="post" ACTION="">
                <table>
                    <tbody>
                    <tr>
                        <td><?php echo $txt["datum"]; ?></td>
                        <td><?php echo $txt["waarde"]; ?></td>
                        <td><?php echo $txt["nieuwewaarde"]; ?></td>
                    </tr>
                    <?php
                    $teller = 1;
                    for ($i = 1; $i <= 12; $i++) {
                        if (!isset($arefer[$i])) $arefer[$i] = 0;
                        echo "<tr>
									<td>" . date("M", strtotime("2009-" . $i . "-01")) . "</td>
									<td>" . number_format($arefer[$i], 0, ',', '.') . "</td>
									<td><input type='text' name='" . $teller . "' id='id" . $teller . "'value='";
                        if (!empty($_POST[$i]))
                            echo $_POST[$i];
                        echo "'></td>
									</tr>";
                        $teller++;
                    }
                    ?>
                    </tbody>
                </table>
                <br/>
                <INPUT TYPE="submit" VALUE="<?php echo $txt["save"]; ?>" onClick="return confirmSubmit()">
            </FORM>


        </div>
    </div>
</div>
</body>
</html>