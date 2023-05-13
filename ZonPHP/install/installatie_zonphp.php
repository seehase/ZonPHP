<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

if ( !isset($_SESSION['passok']) ||  $_SESSION['passok'] != "passinorder")
    header('location:par_welcome.php');

include_once "../inc/connect.php";
include_once "par_header.php";
?>
<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytextparm">

        <div class="inside">
            <h2 class="notopgap" align="center"><?php echo $txt["installscript"]; ?></h2>
            <center>
                Uw Taal:<a href='?taal=nl' TARGET='_self'><img src="../inc/image/nl.png" alt="nl" border="0" width="16"
                                                               height="11"></a>&nbsp;&nbsp;
                Your language:<a href='?taal=en' TARGET='_self'><img src="../inc/image/en.png" alt="en" border="0"
                                                                     width="16" height="11"></a>&nbsp;&nbsp;
                Votre langue:<a href='?taal=fr' TARGET='_self'><img src="../inc/image/fr.png" alt="fr" border="0"
                                                                    width="16" height="11"></a>&nbsp;&nbsp;
                Ihre Sprache:<a href='?taal=de' TARGET='_self'><img src="../inc/image/de.png" alt="de" border="0"
                                                                    width="16" height="11"></a>
            </center>
            <hr>
            <?php echo $txt["installdattab"]; ?><br />
            <br />

            <FORM METHOD="post" ACTION="">
                <?php
                echo $txt["prefix"] . "<br /><br />database prefix:<b> " . $table_prefix . "</b><br /><br />";

                $sql = "SELECT * FROM " . $table_prefix . "_parameters";
                $result = mysqli_query($con, $sql);
                if ($result) {

                    echo '<br /><br /><label>' . $txt["installresettabel"] . '</label>
	<select NAME="resettabel">
			<option  SELECTED>';
                    if (!empty($_POST['resettabel'])) echo $_POST['resettabel'];
                    echo '<option value="' . $txt["installtdnt"] . '">' . $txt["installtdnt"] . ' </option>';
                    echo '<option value="' . $txt["installtd"] . '">' . $txt["installtd"] . ' </option>';
                    echo '<option value="' . $txt["installtm"] . '">' . $txt["installtm"] . ' </option>';
                    echo '<option value="' . $txt["installtp"] . '">' . $txt["installtp"] . ' </option>';
                    echo '<option value="' . $txt["installtr"] . '">' . $txt["installtr"] . ' </option>';
                    echo '<option value="' . $txt["installtpar"] . '">' . $txt["installtpar"] . ' </option>';
                    echo '<option value="' . $txt["installts"] . '">' . $txt["installts"] . ' </option>';
                    echo '</select><br /><br />';
                }
                ?>

                <INPUT name="savecontrole" TYPE="submit" VALUE="<?php echo $txt["save"]; ?>"
                       onClick="return confirmSubmit()">


            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo "<br /><br /><hr>";
                /****************************************************************************
                 * aanmaken tabel voor dag en nacht tarief
                 ****************************************************************************/
                $sql_wisdagnacht = "DROP TABLE IF EXISTS " . $table_prefix . "_verbruik";
                $sql_maakdagnacht = "CREATE TABLE " . $table_prefix . "_verbruik (
					  IndexVerbruik varchar(40) NOT NULL,
					  Datum_Verbruik datetime NOT NULL,
					  Geg_Verbruik_Dag float NOT NULL,
					  Geg_Verbruik_Nacht float NOT NULL,
					  Naam varchar(21) NOT NULL,
					  Geg_MeterDag float NOT NULL,
					  Geg_MeterNacht float NOT NULL,
					  UNIQUE KEY IndexVerbruik (IndexVerbruik)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                /****************************************************************************
                 * aanmaken tabel voor ref kW per maand
                 ****************************************************************************/
                $sql_wisRefkWMaand = "DROP TABLE IF EXISTS " . $table_prefix . "_refer";
                $sql_maakRefkWMaand = "CREATE TABLE IF NOT EXISTS " . $table_prefix . "_refer (
						  Datum_Refer datetime NOT NULL,
						  Geg_Refer float NOT NULL,
						  Dag_Refer float NOT NULL,
						  Naam varchar(21) NOT NULL
						) ENGINE=MyISAM DEFAULT CHARSET=latin1";


                /****************************************************************************
                 * aanmaken tabel voor prijs per kW
                 ****************************************************************************/
                $sql_wisPrijskW = "DROP TABLE IF EXISTS " . $table_prefix . "_euro";
                $sql_maakPrijskW = "CREATE TABLE IF NOT EXISTS " . $table_prefix . "_euro (
					  Datum_Euro datetime NOT NULL,
					  Geg_Euro float NOT NULL,
					  UNIQUE KEY Datum_Euro (Datum_Euro)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1";

                /****************************************************************************
                 * aanmaken tabel voor daggegevens
                 ****************************************************************************/
                $sql_wisDag = "DROP TABLE IF EXISTS " . $table_prefix . "_dag";
                $sql_maakDag = "CREATE TABLE IF NOT EXISTS " . $table_prefix . "_dag (
				  IndexDag varchar(40) NOT NULL,
				  Datum_Dag datetime NOT NULL,
				  Geg_Dag float NOT NULL,
				  kWh_Dag float NOT NULL,
				  Naam varchar(21) NOT NULL,
				  UNIQUE KEY IndexDag (IndexDag)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";

                /****************************************************************************
                 * aanmaken tabel voor maandgegevens
                 ****************************************************************************/
                $sql_wisMaand = "DROP TABLE IF EXISTS " . $table_prefix . "_maand";
                $sql_maakMaand = "CREATE TABLE IF NOT EXISTS " . $table_prefix . "_maand (
				  IndexMaand varchar(40) NOT NULL,
				  Datum_Maand datetime NOT NULL,
				  Geg_Maand float NOT NULL,
				  Naam varchar(21) NOT NULL,
				  UNIQUE KEY IndexMaand (IndexMaand)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";
                /****************************************************************************
                 * Tabel structuur voor tabel 'tgeg_parameters'
                 ****************************************************************************/
                $sql_wisParameters = "DROP TABLE IF EXISTS " . $table_prefix . "_parameters";
                $sql_maakParameters = "CREATE TABLE " . $table_prefix . "_parameters (
				  Variable varchar(60) NOT NULL,
				  Waarde varchar(120) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";

                /****************************************************************************
                 * Tabel structuur 'tgeg_sensordata'
                 ****************************************************************************/
                $sql_wisSensordata = "DROP TABLE IF EXISTS " . $table_prefix . "_sensordata";
                $sql_maakSensordata = "CREATE TABLE " . $table_prefix . "_sensordata (
				    id VARCHAR(20),
                    logtime DATETIME,
                    measurevalue FLOAT,
                    sensorid INT,
                    sensortype INT,
                    PRIMARY KEY (id),
                    CONSTRAINT tgeg_sensor_ix1 UNIQUE (logtime, sensorid, sensortype)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";


                /****************************************************************************
                 * Invullen kostprijs per kWh in Euro
                 ****************************************************************************/
                $sql_insertPrijskW = "INSERT INTO " . $table_prefix . "_euro (Datum_Euro, Geg_Euro) VALUES
					('2010-01-01', 0.4301),
					('2011-01-01', 0.4301),
					('2012-01-01', 0.4301),
					('2013-01-01', 0.4301),
					('2014-01-01', 0.4301),
					('2015-01-01', 0.4301),
					('2016-01-01', 0.4301),
					('2017-01-01', 0.4301),
					('2018-01-01', 0.4301),
					('2019-01-01', 0.4301),
					('2020-01-01', 0.4301),
					('2021-01-01', 0.4301),
					('2022-01-01', 0.4301),
					('2023-01-01', 0.4301),
					('2024-01-01', 0.4301),
					('2025-01-01', 0.4301),
					('2026-01-01', 0.4301),
					('2027-01-01', 0.4301),
					('2028-01-01', 0.4301),
					('2029-01-01', 0.4301)";


                echo "<b>" . $txt["installuitg"] . ".</b><br /><br />";
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installtdnt"]) {
                    mysqli_query($con, $sql_wisdagnacht) or die("Query failed. sql_wisDag: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakdagnacht) or die("Query failed. sql_wisDag: " . mysqli_error($con));
                    echo $txt["installtdnt"] . "ok<br />";
                }
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installtd"]) {
                    mysqli_query($con, $sql_wisDag) or die("Query failed. sql_wisDag: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakDag) or die("Query failed. sql_maakDag: " . mysqli_error($con));
                    echo $txt["installtd"] . "ok<br />";
                }
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installtm"]) {
                    mysqli_query($con, $sql_wisMaand) or die("Query failed. sql_wisMaand: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakMaand) or die("Query failed. sql_maakMaand: " . mysqli_error($con));
                    echo $txt["installtm"] . "ok<br />";
                }
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installtp"]) {
                    mysqli_query($con, $sql_wisPrijskW) or die("Query failed. sql_wisPrijskW: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakPrijskW) or die("Query failed. sql_maakPrijskW: " . mysqli_error($con));
                    mysqli_query($con, $sql_insertPrijskW) or die("Query failed. sql_insertRefkW: " . mysqli_error($con));
                    echo $txt["installtp"] . "ok<br />";
                }
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installtr"]) {
                    mysqli_query($con, $sql_wisRefkWMaand) or die("Query failed. sql_wisRefkWMaand: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakRefkWMaand) or die("Query failed. sql_maakRefkWMaand: " . mysqli_error($con));
                    echo $txt["installtr"] . "ok<br />";
                }
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installtpar"]) {
                    mysqli_query($con, $sql_wisParameters) or die("Query failed. sql_wisParameters: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakParameters) or die("Query failed. sql_maakParameters: " . mysqli_error($con));
                    echo $txt["installtpar"] . "ok<br />";
                }
                if (empty($_POST['resettabel']) || $_POST['resettabel'] == $txt["installts"]) {
                    mysqli_query($con, $sql_wisSensordata) or die("Query failed. sql_wisSensordata: " . mysqli_error($con));
                    mysqli_query($con, $sql_maakSensordata) or die("Query failed. sql_maakSensordata: " . mysqli_error($con));
                    echo $txt["installts"] . "ok<br />";
                }

                echo "<br /><br /><hr>";
                echo '<b><a href="par_edit.php">' . $txt["installtping"] . '.</a></b><br />';

            }
            ?>
        </div>
    </div>
</div>
</body>
</html>