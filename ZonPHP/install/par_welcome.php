<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

include "par_header.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (($_POST['pass'] == $admin_password) && ($_POST['user'] == $admin_username)) {
        $_SESSION['passok'] = "passinorder";
    } else {
        $_SESSION['passok'] = "fail";
    }
}
?>

<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <br/>
            <h1 class="notopgap" align="center"><?php echo $txt["bestezonphp"]; ?>,</h1>

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
            <br/><br/>
            <?php echo $txt["welkomconf"]; ?>.<br/>
            <?php echo $txt["welkomlinks"]; ?>:<br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomcre"]; ?>.
            <br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomvis"]; ?>.
            <br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomref"]; ?>.
            <br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomeuro"]; ?>.
            <br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomverbruik"]; ?>.
            <br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomdebug"]; ?>.
            <br/><br/>
            &nbsp;&nbsp;&nbsp;-<?php echo $txt["welkomIndex"]; ?>.
            <br/><br/>
            <hr>
            <b>
                <?php
                echo $txt["welkominlog"];
                if ($admin_password == "" || $admin_username == "") {
                    echo " <br /><br />" . $txt["noadminpassword"];
                } else {
                    echo '
                    </b><br /><br />
                    
                    <FORM METHOD="post" ACTION="">
                    <table>
                        <tr>
                            <td>Username:&nbsp;</td><td><input name="user" value="" SIZE="20"></td>
                        </tr> 
                        <tr>
                        <td>Password:&nbsp;</td><td><input type="password" name="pass" value="" SIZE="20"></td>
                        </tr>
                        <table>
                        <br />
                        <input name="savecontrole" type="submit" value="' . $txt["save"] . '>">                        
                    </form>
                    ';
                }
                ?>

                <br/>
                <?php
                if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder")
                    echo "<br /><br /><b>" . $txt["welkominl"] . ".</b><br />";
                else
                    echo "<br /><br /><b>" . $txt["welkomverk"] . ".</b><br />";
                ?>
                <br/>
        </div>
    </div>
</div>
</body>
</html>
