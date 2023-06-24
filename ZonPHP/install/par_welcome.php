<?php

include_once "../inc/init.php";
include_once "../inc/sessionstart.php";

include "par_header.php";
$_SESSION['passok'] = "passinorder";

?>

<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <br/>
            <h1 class="notopgap" align="center"><?php echo getTxt("bestezonphp"); ?>,</h1>

            <center>
                <?php
                if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder")
                    echo "";
                else
                    echo
                        "<br /><br /><hr>" .
                        "Uw taal:&nbsp" .
                        "<a href='?taal=nl' TARGET='_self'><img src='../inc/image/nl.svg' alt='nl' border='0' width='16' height='12'></a>" .
                        "&nbsp&nbspYour language:&nbsp" .
                        "<a href='?taal=en' TARGET='_self'><img src='../inc/image/en.svg' alt='en' border='0' width='16' height='12'></a>" .
                        "&nbsp&nbspVotre langue:&nbsp" .
                        "<a href='?taal=fr' TARGET='_self'><img src='../inc/image/fr.svg' alt='fr' border='0' width='16' height='12'></a>" .
                        "&nbsp&nbspIhre Sprache:&nbsp" .
                        "<a href='?taal=de' TARGET='_self'><img src='../inc/image/de.svg' alt='de' border='0' width='16' height='12'></a>"
                        . "<br />";

                ?>
            </center>
            <hr>
            <br/>
            <?php echo getTxt("welkomconf"); ?><br/>
            <br/>
            <?php
            if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder") {
                echo getTxt("welkomlinks"), '<BR>';
                echo "<dl>";
                echo "<dt><b>" . "Index" . "</b></dt>";
                echo "<dd>" . getTxt("welkomIndex") . "</dd>";
                echo "<dt><b>" . getTxt("insteltabel") . "</b></dt>";
                echo "<dd>" . getTxt("welkomcre") . "</dd>";
                echo "<dt><b>" . getTxt("deletevalues") . "</b></dt>";
                echo "<dd>" . "Wis gegevens (txt variable to be added to language files)" . "</dd>";
                echo "<dt><b>" . getTxt("parameters") . "</b></dt>";
                echo "<dd>" . getTxt("welkomvis") . "</dd>";
                echo "<dt><b>" . getTxt("parref") . "</b></dt>";
                echo "<dd>" . getTxt("welkomref") . "</dd>";
                echo "<dt><b>" . "Debug" . "</b></dt>";
                echo "<dd>" . getTxt("welkomdebug") . "</dd>";
                echo "<dt><b>" . getTxt("pardelete") . "</b></dt>";
                echo "<dd>" . "Delete (txt variable to be added to language files)" . "</dd>";
                echo "<dt><b>" . getTxt("parupdate") . "</b></dt>";
                echo "<dd>" . "Update (txt variable to be added to language files)" . "</dd>";
                echo "</dl>";
            }
            ?>
            <hr>
            <b>
                <?php
                if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder")
                    echo "";
                else
                    echo getTxt("welkominlog");

                if ($admin_password == "" || $admin_username == "") {
                    echo " <br /><br />" . getTxt("noadminpassword");
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
                        <input name="savecontrole" type="submit" value="' . getTxt("save") . '>">                        
                    </form>
                    ';
                }
                ?>

                <br/>
                <?php
                if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder")
                    echo "<br /><br /><b>" . getTxt("welkominl") . ".</b><br />";
                else
                    echo "<br /><br /><b>" . getTxt("welkomverk") . ".</b><br />";
                ?>
                <br/>
        </div>
    </div>
</div>
</body>
</html>
