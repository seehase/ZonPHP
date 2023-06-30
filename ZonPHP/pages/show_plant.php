<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/sessionstart.php";
include_once ROOT_DIR . "/inc/load_cache.php";
include_once ROOT_DIR . "/inc/header.php";
?>

<div id="page-content">
    <div id='id_install' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(148px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>

        <div class="<?= HEADER_CLASS ?>"><h2>I N S T A L L A T I O N</h2></div>
        <div id='id_install_content' class="<?= CONTENT_CLASS ?>">
            <div>
                <h1>Anlage von <?= $params['plant']['name'] ?></h1>
                <?= '
                Website: ' . $params['plant']['website']. '<br>
                Standort: ' . $params['plant']['location'] . '<br>
                Module: ' . $params['plant']['panels'] . '<br>
                Wechselrichter: ' . $params['plant']['converter']. '<br>
                Inbetriebnahme: ' . $params['plant']['installationDate'] . '<br>
                Ausrichtung: ' . $params['plant']['orientation']. '<br>
                Data Logger: ' . $params['plant']['importer'] . '<br> <br>';
                ?>
            </div>
            <div id="foto" style="float:none;">
                <?php
                foreach (PLANTS as $plant) {
                    echo "<p>" . $params[$plant]['name'] . "<br>" .
                        "<img src=" . HTML_PATH . $params[$plant]['image'] . " alt=" . $params[$plant]['name'] .
                        "style=\"border: 2px solid #000000; border-radius: 10px 10px 10px 10px; width: 600px\">
                        </p>";
                     }
                ?>
            </div>
            <div id="mycontainer" class="demo"
                 style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>;height:100%; <?= $corners; ?>">
            </div> <?php include_once ROOT_DIR . "/inc/footer.php"; ?> </div>
        <br>
    </div><!-- closing ".page-content" -->
</div>
</div><!-- closing ".container" -->

</body>
</html>