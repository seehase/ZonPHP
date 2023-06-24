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
            <h2 class="notopgap" align="center"><?php echo getTxt("installscript"); ?></h2>
            <hr>
            <?php echo getTxt("cleartables") . "<br /><br />" .
                $table_prefix . "_dag<br /> " .
                $table_prefix . "_maand<br />";
            ?>
            <FORM METHOD="post" ACTION="">
                <?php echo "<br />" . getTxt("prefix_name") . ": <b>" . $table_prefix . "</b><br /><br />" ?>


                <INPUT name="savecontrole" TYPE="submit" VALUE="<?php echo getTxt("save"); ?>"
                       onClick="return confirmSubmit()">

            </form>
            <br/>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stringdelete = "DELETE FROM " . $table_prefix . "_dag";
                $stringdelete2 = "DELETE FROM " . $table_prefix . "_maand";

                mysqli_query($con, $stringdelete) or die ('SQL Error stringdelete:' . mysqli_error($con));
                mysqli_query($con, $stringdelete2) or die ('SQL Error stringdelete2:' . mysqli_error($con));

                echo "<br /><hr><br />";
                echo '<b><a href="par_edit.php">' . getTxt("installtping") . '.</a></b><br /><br />';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>