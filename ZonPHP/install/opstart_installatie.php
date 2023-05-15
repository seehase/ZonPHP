<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

include_once "par_header.php";

$error = "";
if (isset($_GET["fout"])) {
    $error = $_GET["fout"];
}

?>

<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytext">
        <div class="inside">
            <h1 class="notopgap" align="center"><?php echo $txt["bestezonphp"]; ?>,</h1>
            <center>
                Uw Taal:<a href='?taal=nl&amp;fout=<?php echo $error ?>' TARGET='_self'><img
                            src="../inc/image/nl.svg" alt="nl" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Your language:<a href='?taal=en&amp;fout=<?php echo $error ?>' TARGET='_self'><img
                            src="../inc/image/en.svg" alt="en" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Votre langue:<a href='?taal=fr&amp;fout=<?php echo $error ?>' TARGET='_self'><img
                            src="../inc/image/fr.svg" alt="fr" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Ihre Sprache:<a href='?taal=de&amp;fout=<?php echo $error; ?>' TARGET='_self'><img
                            src="../inc/image/de.svg" alt="de" border="0" width="16" height="11"></a>
            </center>
            <hr>
            <br/>
            <?php
            echo $txt["volfout"] . ".<br /><br />";

            if (isset($_GET["fout"])) {
                if ($_GET["fout"] == "connect") {
                    echo $txt["foutconnect"];
                } else if ($_GET["fout"] == "database") {
                    echo $txt["foutdatabase"];
                } else if ($_GET["fout"] == "table") {
                    echo $txt["fouttable"];
                } else if ($_GET["fout"] == "ref") {
                    echo $txt["foutref"];
                } else if ($_GET["fout"] == "parameter") {
                    echo $txt["foutparameter"];
                } else {
                    echo "<br />" . $txt["undefined"];
                }
            } else {
                echo "<br />" . $txt["undefined"];
            }
            echo "<br />";
            ?>
        </div>
    </div>
</div>
</body>
</html>
