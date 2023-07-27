<?php
global $params, $sum_per_year, $colors, $big_chart_height;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/all_years_chart.php";
// force reload caches
if (isset($_SESSION['lastupdate'])) {
    $_SESSION['lastupdate'] = 0;
}

$footer_display_style = "clear:both; ";
$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';

$myKeys = array_keys($sum_per_year);
?>
<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(136px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>
        <div id="chart_header" class="<?= HEADER_CLASS ?>">
            <h2><?= getTxt("chart_allyearoverview") . "&nbsp;" . min($myKeys) . " - " . max($myKeys); ?></h2>
        </div>
        <div id="total_chart"
             style="width:100%;background-color: <?= $colors['color_chartbackground'] ?>;height:100%; <?= $corners; ?>">
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <br>
</div><!-- closing ".page-content" -->
<script>
    $(document).ready(function () {
        $("#resize ").height(<?= BIG_CHART_HIGHT ?>);
    });
</script>
</body>
</html>