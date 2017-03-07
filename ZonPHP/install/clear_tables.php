<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

if (!isset($_SESSION['passok']))
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
            <?php echo $txt["cleartables"] . "<br /><br />" .
                $table_prefix . "_dag<br /> " .
                $table_prefix . "_maand<br />";
            ?>
            <FORM METHOD="post" ACTION="">
                <?php echo "<br />" . $txt["prefix_name"] . ": <b>" . $table_prefix . "</b><br /><br />" ?>


                <INPUT name="savecontrole" TYPE="submit" VALUE="<?php echo $txt["save"]; ?>"
                       onClick="return confirmSubmit()">

            </form>
            <br />
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stringdelete = "DELETE FROM " . $table_prefix . "_dag";
                $stringdelete2 = "DELETE FROM " . $table_prefix . "_maand";

                mysqli_query($con, $stringdelete) or die ('SQL Error stringdelete:' . mysqli_error($con));
                mysqli_query($con, $stringdelete2) or die ('SQL Error stringdelete2:' . mysqli_error($con));

                echo "<br /><hr><br />";
                echo '<b><a href="par_edit.php">' . $txt["installtping"] . '.</a></b><br /><br />';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>