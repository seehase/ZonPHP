<?php
global $version, $new_version_label, $params;
include_once "../inc/init.php";

if (isset($_POST['action']) && ($_POST['action'] == "debugEnabled")) {
    $params['debugEnabled'] = "1";
    $_SESSION['params'] = $params;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="ZonPHP,Sonne,Zon,sun PV, Photovoltaik, Datenlogger, SMA, Solar, Analyse">
    <meta name="description" content="PV Anlagen Monitoring">
    <meta name="author" content="seehase">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <title>ZonPHP Parameter Validation</title>
    <link type="text/css" rel="stylesheet" href="<?= HTML_PATH ?>inc/styles/validate_style.css">
</head>
<body>

<div id="menus">
    <div id="container1">
        <div id="area"></div>
        <div id="afstand">
            <div class="inside">
                <br>
                <ul id="nav">
                    <li><a href="..">&raquo;&nbsp;Index</a></li>
                    <?php
                    echo '                    
					<li><a href="' . HTML_PATH . '/inc/destroy.php">&raquo;&nbsp;' . getTxt("clearsession") . '</a></li>					
					';
                    ?>
                </ul>
                <hr>
                <?= $version ?>
                <br>
                <br>
            </div>
        </div>
    </div>
    <br>
</div>
<div id="container">
    <div id="bodytext">
        <div class="inside" style="text-align: left">
            <h1 style="text-align: center"><?= getTxt("bestezonphp"); ?>,</h1>
            Uw Taal: <a href='?language=nl' TARGET='_self'><img
                        src="../inc/images/nl.svg" alt="nl" width="16" height="11"></a>&nbsp;&nbsp;
            Your language: <a href='?language=en' TARGET='_self'><img
                        src="../inc/images/en.svg" alt="en" width="16" height="11"></a>&nbsp;&nbsp;
            Votre langue: <a href='?language=fr' TARGET='_self'><img
                        src="../inc/images/fr.svg" alt="fr" width="16" height="11"></a>&nbsp;&nbsp;
            Ihre Sprache: <a href='?language=de' TARGET='_self'><img
                        src="../inc/images/de.svg" alt="de" width="16" height="11"></a>
            <hr>
            <br>
            <?php
            echo getTxt("paths") . ".<br>";
            echo '<pre>';
            echo "<p>HTML_PATH: " . HTML_PATH . "</p>";
            echo "<p>ROOT_DIR: " . ROOT_DIR . "</p>";
            echo "<p>DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
            echo "</pre><hr><br>";

            echo getTxt("errors") . ".<br><br>";
            echo "<p> ERRORS </p>";
            echo '<pre>';
            foreach ($_SESSION['params']['check']['ERROR'] as $msg) {
                echo "<p> $msg </p>";
            }
            echo '</pre>';
            echo "<p> WARNINGS </p>";
            echo '<pre>';
            foreach ($_SESSION['params']['check']['WARN'] as $msg) {
                echo "<p> $msg </p>";
            }
            echo '</pre>';
            echo "<p> INFO </p>";
            echo '<pre>';
            foreach ($_SESSION['params']['check']['INFO'] as $msg) {
                echo "<p> $msg </p>";
            }
            echo '</pre>';
            if ($params['debugEnabled']) {
                echo "<p> DEBUG </p>";

                $copyOfParam = array_merge(array(), $params);
                // clear password, not to be exposed by accident
                $copyOfParam['database']['password'] = "***********";
                if (isset($copyOfParam['weewx']['password'])) {
                    $copyOfParam['weewx']['password'] = "***********";
                }
                echo 'Parameters:';
                echo '<pre>';
                print_r($copyOfParam);
                echo '</pre>';
                if (isset($_SESSION['params']['check']['DEBUG'])) {
                    echo '<hr><pre>';
                    foreach ($_SESSION['params']['check']['DEBUG'] as $msg) {
                        echo "<p> $msg </p>";
                    }
                    echo '</pre>';
                }
            }


            if (strlen($new_version_label) > 0) {
                $newversion = getTxt("newversion") . " " . getTxt("available");
                echo <<<EOT
                       <br><hr><br>
                        <a  onclick="target='_blank'"
                           href="https://github.com/seehase/ZonPHP/releases">$newversion</a>    
                           <br>                    
                      EOT;
            }
            ?>
            <br>
        </div>
    </div>
</div>
</body>
</html>
