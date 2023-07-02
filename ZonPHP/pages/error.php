<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/error_header.php";

$error = "";
if (isset($_GET["fout"])) {
    $error = $_GET["fout"];
}

?>

<div id="menus">
    <?php include ROOT_DIR . "/inc/error_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytext">
        <div class="inside">
            <h1 class="notopgap" align="center"><?= getTxt("bestezonphp"); ?>,</h1>
            <center>
                Uw Taal:<a href='?language=nl&amp;fout=<?= $error ?>' TARGET='_self'><img
                            src="../inc/image/nl.svg" alt="nl" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Your language:<a href='?language=en&amp;fout=<?= $error ?>' TARGET='_self'><img
                            src="../inc/image/en.svg" alt="en" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Votre langue:<a href='?language=fr&amp;fout=<?= $error ?>' TARGET='_self'><img
                            src="../inc/image/fr.svg" alt="fr" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Ihre Sprache:<a href='?language=de&amp;fout=<?= $error; ?>' TARGET='_self'><img
                            src="../inc/image/de.svg" alt="de" border="0" width="16" height="11"></a>
            </center>
            <hr>
            <br/>
            <?php
            echo getTxt("volfout") . ".<br /><br />";

            if (isset($_GET["fout"])) {
                if ($_GET["fout"] == "connect") {
                    echo getTxt("foutconnect");
                } else if ($_GET["fout"] == "database") {
                    echo getTxt("foutdatabase");
                } else if ($_GET["fout"] == "table") {
                    echo getTxt("fouttable");
                } else if ($_GET["fout"] == "ref") {
                    echo getTxt("foutref");
                } else if ($_GET["fout"] == "parameter") {
                    echo getTxt("foutparameter");
                } else {
                    echo "<br />" . getTxt("undefined");
                }
            } else {
                echo "<br />" . getTxt("undefined");
            }
            echo "<br />";
            ?>
        </div>
    </div>
</div>
</body>
</html>
