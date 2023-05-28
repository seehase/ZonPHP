<?php

include_once "../parameters.php";
include_once "../inc/sessionstart.php";

if (!isset($_SESSION['passok']) || $_SESSION['passok'] != "passinorder")
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
            <hr>
            <?php echo $txt["installdattab"]; ?><br/>
            <br/>

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


                echo "<b>" . $txt["installuitg"] . ".</b><br /><br />";
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