<?php
include_once "../parameters.php";
include_once "../inc/sessionstart.php";
include_once "../inc/connect.php";

// check if tables exists
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));

if (!isset($_SESSION['passok']) || $_SESSION['passok'] != "passinorder")
    header('location:par_welcome.php');


include_once "par_header.php";


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
?>

<div id="menus">
    <?php include "par_menu.php"; ?>
</div>

<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center"><?php echo getTxt("parupdate"); ?></h2>
            <hr>
            <div id="bodytextloschen">
                <form action="actionupdate.php" method="get">

                    <p></p>
                    <br>
                    <?php echo getTxt("parinverter"); ?>
                    <label>
                        <select name="wr">
                            <option SELECTED>
                                <?php
                                if (isset($_GET['naam'])) echo $_GET['naam'];
                                foreach ($akommanamen as $key => $naam)
                                    echo '<option value="' . $naam . '">' . $naam . ' </option>';

                                ?>
                        </select>


                        <br/>
                        <br/>
                        <label><?php echo getTxt("datum"); ?> </label>
                        <select name="year">
                            <option SELECTED><?php if (!empty($_POST['year'])) echo $_POST['year']; ?>
                                <?php
                                for ($i = date('Y'); $i >= 2006; $i--) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                }
                                ?>
                        </select>

                        <select name="month">
                            <option SELECTED><?php if (!empty($_POST['month'])) echo $_POST['month']; ?>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                }
                                ?>
                        </select>
                        <select name="day">
                            <option SELECTED><?php if (!empty($_POST['day'])) echo $_POST['day']; ?>
                                <?php
                                for ($i = 1; $i <= 31; $i++) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                }
                                ?>
                        </select>


                        <p><?php echo getTxt("parupdatetime"); ?><input type="text" name="time"/></p>
                        <p><?php echo getTxt("parupdatetimeinfo") ?></p>

                        <p><?php echo getTxt("parupdatevalue") ?><input type="text" name="value"/></p>

                        <p><?php echo getTxt("parupdateearningsinfo") ?></p>
                        <p><?php echo getTxt("parupdateearnings") ?><input type="text" name="earnings"/></p>


                        <p><input type="submit" VALUE="<?php echo getTxt("save"); ?>"/></p>
                </form>


            </div>
        </div>
    </div>
</div>

