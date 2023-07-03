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
            <h2>H E A D E R</h2>
        </div>
        <div id='content'>
            <p>Hello world</p>
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <br>
</div><!-- closing ".page-content" -->

</body>
</html>
