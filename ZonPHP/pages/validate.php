<?php
global $version, $new_version_label, $params, $con;
include_once "../inc/init.php";

if (isset($_POST['action']) && ($_POST['action'] == "debugEnabled")) {
    $params['debugEnabled'] = "1";
    $_SESSION['params'] = $params;
    unset($_SESSION['lastupdate']);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link type="text/css" rel="stylesheet" href="<?= HTML_PATH ?>inc/styles/validate_style.css">
</head>
<body>
<br>
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
					<li><a href="' . HTML_PATH . 'inc/destroy.php">&raquo;&nbsp;' . getTxt("clearsession") . '</a></li>					
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
            <br>
            <h1 style="text-align: center"><?= getTxt("bestezonphp"); ?></h1>
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
            <h2> <?= getTxt("paths") ?> </h2>
            <pre>
                <p> HTML_PATH: <?= HTML_PATH ?> </p><br><p> ROOT_DIR: <?= ROOT_DIR ?> </p><br><p> DOCUMENT_ROOT: <?= $_SERVER['DOCUMENT_ROOT'] ?></p>
            </pre>
            <hr>
            <br>
            <h2> ERRORS </h2>
            <pre>
                <?php
                foreach ($_SESSION['params']['check']['ERROR'] as $msg) {
                    echo "<p> $msg </p><br>";
                }
                ?>
            </pre>
            <h2> WARNINGS </h2>
            <pre>
                <?php
                foreach ($_SESSION['params']['check']['WARN'] as $msg) {
                    echo "<p> $msg </p><br>";
                }
                ?>
            </pre>
            <h2> INFO </h2>
            <pre>
            <?php foreach ($_SESSION['params']['check']['INFO'] as $msg) {
                echo "<p> $msg </p><br>";
            }
            ?>
            </pre>
            <?php
            if ($params['debugEnabled']) {
                $copyOfParam = array_merge(array(), $params);
                // clear password, not to be exposed by accident
                $copyOfParam['database']['password'] = "***********";
                if (isset($copyOfParam['weewx']['password'])) {
                    $copyOfParam['weewx']['password'] = "***********";
                }
                echo "<hr><h2> Debug messages </h2>";
                if (isset($_SESSION['debugMessages'])) {
                    echo "<pre><br>";
                    foreach ($_SESSION['debugMessages'] as $msg) {
                        echo "<p> $msg </p><br>";
                    }
                    echo "</pre>";
                }
                echo "<hr>";
                echo '<a href="#parameters" data-bs-toggle="collapse">Show parameters</a>';
                echo "&nbsp;&nbsp;&nbsp;";
                echo '<a href="#dbcheck" data-bs-toggle="collapse">Show DBcheck</a>';
                echo "&nbsp;&nbsp;&nbsp;";
                echo '<a href="#phpInfo" data-bs-toggle="collapse">Show phpInfo()</a>';

                echo '<div id="parameters" class="collapse">';
                echo "<pre>";
                print_r($copyOfParam);
                echo "</pre>";
                echo "</div> 
                ";

                echo '<div id="dbcheck" class="collapse">';
                echo "<pre><br>";
                
                if (isset($_SESSION['dbMessages'])) {
                    foreach ($_SESSION['dbMessages'] as $msg) {
                        echo "<p> $msg </p><br>";
                    }
                }
                echo "</pre>";
                echo "</div>
                    ";

                echo '<div id="phpInfo" class="collapse">';
                echo getPhpInfo();
                echo "</div> <br>";

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
    <br>

</div>
</body>
</html>
