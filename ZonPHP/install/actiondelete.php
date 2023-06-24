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

?>
<div id="menus">
    <?php include "par_menu.php"; ?>
</div>

<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center"><u><?php echo getTxt("pardelete"); ?></u></h2>
            <?php

            $year = $_GET['year'];
            $mon = $_GET['month'];
            $day = $_GET['day'];
            $wr = $_GET['wr'];
            $finish = false;

            if ($wr == "" or $year == "") {
                echo getTxt("pardeleteinput1") . $year . "<br>";
                echo getTxt("pardeleteinput2") . $wr . "<br>" . "<br>";
                echo getTxt("pardeleteerror") . "<br>" . "<br>";

                ?>
                <form>
                    <input type="button" value="<?php echo getTxt("parback"); ?>"
                           onclick="window.location.href='update.php'"/>
                </form>
                <?php
            }

            if ($day !== "") $day = str_pad($day, 2, 0, STR_PAD_LEFT);
            if ($mon !== "") $mon = str_pad($mon, 2, 0, STR_PAD_LEFT);

            $wrdat = $year . "-" . $mon . "-" . $day;
            if ($mon == "") $wrdat = $year . "-";

            echo getTxt("pardeleteinput1") . $wrdat . "<br>";
            echo getTxt("pardeleteinput2") . $wr . "<br>" . "<br>";
            ?>
            <?php
            $tableday = $table_prefix . "_dag";
            $tablemonth = $table_prefix . "_maand";

            if ($mon == "" and $finish == false) {
                $delstring = "DELETE FROM $tableday WHERE Datum_Dag LIKE '$wrdat%' AND Naam like '$wr'";
                $delstringmaand = "DELETE FROM $tablemonth WHERE Datum_Maand like '$wrdat%' AND Naam= '$wr'";

                mysqli_query($con, $delstring) or die(getTxt("pardeletesqlerror") . $delstring . " <--" . mysqli_error($delstring));
                mysqli_query($con, $delstringmaand) or die(getTxt("pardeletesqlerror") . $delstring . " <--" . mysqli_error($delstring));
                $deldat = getTxt("jaar");
                $finish = true;
            }

            if ($day == "" and $finish == false) {
                $delstring = "DELETE FROM $tableday WHERE Datum_Dag LIKE '$wrdat%' AND Naam like '$wr'";
                $delstringmaand = "DELETE FROM $tablemonth WHERE Datum_Maand like '$wrdat%' AND Naam= '$wr'";

                mysqli_query($con, $delstring) or die(getTxt("pardeletesqlerror") . $delstring . " <--" . mysqli_error($delstring));
                mysqli_query($con, $delstringmaand) or die(getTxt("pardeletesqlerror") . $delstring . " <--" . mysqli_error($delstring));
                $deldat = getTxt("maand");
                $finish = true;
            }

            if ($day !== "" and $finish == false) {
                $delstring = "DELETE FROM $tableday WHERE Datum_Dag LIKE '$wrdat%' AND Naam like '$wr'";
                $delstringmaand = "DELETE FROM $tablemonth WHERE Datum_Maand like '$wrdat%' AND Naam= '$wr'";

                mysqli_query($con, $delstring) or die(getTxt("pardeletesqlerror") . $delstring . " <--" . mysqli_error($delstring));
                mysqli_query($con, $delstringmaand) or die(getTxt("pardeletesqlerror") . $delstring . " <--" . mysqli_error($delstring));
                $deldat = getTxt("dag");
                $finish = true;
            }

            echo "<br>" . "<br>" . "<br>" . "<br>";
            echo getTxt("pardeleteready") . $deldat . "<br>";
            echo getTxt("omvormer") . ": " . $wr . "<br>";
            ?>
        </div>
    </div>
</div>


