<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/sessionstart.php";
include_once ROOT_DIR . "/inc/load_cache.php";
include_once ROOT_DIR . "/inc/header.php";
include_once ROOT_DIR . "/charts/cumulative_chart.php";

$footer_display_style = "clear:both; ";
if ($params['hideFooter'] == true) {
    $padding = '- 35px';
    $corners = 'border-bottom-left-radius: 9.5px; border-bottom-right-radius: 9.5px;';
} else {
    $padding = '- 0px';
    $corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';
}
?>
<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(148px <?php echo $padding; ?>); ">
        <div id="menu_header" class="<?= MENU_CLASS ?>" style="height: 45px; background: #222; vertical-align: middle;">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>

        <div id="chart_header" class="<?= HEADER_CLASS ?>" style="display: grid; align-content: center; ">
            <h2>
                <?php echo getTxt("omvormer") . " " . $title; ?>
            </h2>

            <div class="backtoday" style="float:none;">
                <form method="POST">
                    <button class="btn btn-zonphp" name='add' type="submit" value='+'>+</button>
                </form>
            </div>

        </div>

        <div id="universal"
             style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>;height:100%; <?php echo $corners; ?>">
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <script>
        $(document).ready(function () {
            $("#resize ").height(<?php echo $big_chart_height ?>);
        });
    </script>
</div><!-- closing ".page-content" -->
</div><!-- closing "container" -->

</body>
</html>
