<?php
include_once "../inc/init.php";

$error = "";
if (isset($_GET["fout"])) {
    $error = $_GET["fout"];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <meta name="keywords" content="slaper, veracx, zon, SolarLog, Sunny Beam, Datenlogger, Solar, Solaranlage, Photovoltaik,
	 		SMA,Sanyo, Online, Internet, Banner, Email, SMS, PV, zonnepanelen">
    <meta name="description" content="Deze site geeft U veel info over het monitoren van PV zonnepanelen,
			met data en veel interessante info over Belgische zonnepanelen">
    <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">

    <title>ZonPHP Installation</title>
    <link type="text/css" rel="stylesheet" href="<?= HTML_PATH ?>inc/styles/validate_style.css">
</head>
<body>

<div id="menus">
    <div id="container1">
        <div id="area"></div>
        <div id="afstand">
            <div class="inside">
                <ul id="nav">
                    <br/>
                    <li><a href="..">&raquo;&nbsp;Index</a></li>
                    <?php
                    echo '                    
					<li><a href="' . HTML_PATH . '/inc/destroy.php">&raquo;&nbsp;' . getTxt("clearsession") . '</a></li>					
					';
                    ?>
                </ul>
                <hr>
                <?= $version ?>
                <br/>
                <hr>
            </div>
        </div>
    </div>
    <br/>
</div>
<div id="container">
    <div id="bodytext">
        <div class="inside">
            <h1 class="notopgap" align="center"><?= getTxt("bestezonphp"); ?>,</h1>
            <center>
                Uw Taal:<a href='?language=nl&amp;fout=<?= $error ?>' TARGET='_self'><img
                            src="../inc/images/nl.svg" alt="nl" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Your language:<a href='?language=en&amp;fout=<?= $error ?>' TARGET='_self'><img
                            src="../inc/images/en.svg" alt="en" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Votre langue:<a href='?language=fr&amp;fout=<?= $error ?>' TARGET='_self'><img
                            src="../inc/images/fr.svg" alt="fr" border="0" width="16" height="11"></a>&nbsp;&nbsp;
                Ihre Sprache:<a href='?language=de&amp;fout=<?= $error; ?>' TARGET='_self'><img
                            src="../inc/images/de.svg" alt="de" border="0" width="16" height="11"></a>
            </center>
            <hr>
            <br/>
            <?php
            echo getTxt("volfout") . ".<br /><br />";
            echo "<p> ERRORS </p>";
            foreach ($_SESSION['params']['check']['ERROR'] as $msg) {
                echo "<p> $msg </p>";
            }
            echo "<p> WARNINGS </p>";
            foreach ($_SESSION['params']['check']['WARN'] as $msg) {
                echo "<p> $msg </p>";
            }
            echo "<p> INFO </p>";
            foreach ($_SESSION['params']['check']['INFO'] as $msg) {
                echo "<p> $msg </p>";
            }

            echo "<br />";
            ?>
        </div>
    </div>
</div>
</body>
</html>
