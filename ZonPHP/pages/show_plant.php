<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/load_cache.php";
include_once ROOT_DIR . "/inc/header.php";
?>

<div id="page-content">
    <div id='id_about' style="<?= WINDOW_STYLE ?>;  ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>

        <div id="chart_header" class="<?= HEADER_CLASS ?>">
            <h2>I N S T A L L A T I O N</h2>
        </div>
        <div id='content'>
            <div >
                <h1>Anlage von <?= $params['farm']['name'] ?></h1>
                <?= '
                Website: ' . $params['farm']['website'] . '<br>
                Standort: ' . $params['farm']['location'] . '<br>
                Data Logger: ' . $params['farm']['importer'] . '<br> <br>';
                ?>
            </div>
            <div id="foto" style="float:none;">
                <?php
                foreach (PLANTS as $plant) {
                    echo "<p>" . $params[$plant]['name'] . "<br>" .
                        "<img src=" . HTML_PATH . $params[$plant]['image'] . " alt=" . $params[$plant]['name'] .
                        " style=\" border: 2px solid #000000; border-radius: 10px 10px 10px 10px; width: 600px\">
                        </p>";
                }
                ?>
            </div>
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <br>
</div><!-- closing ".page-content" -->

</body>
</html>