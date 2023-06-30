<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/sessionstart.php";
include_once ROOT_DIR . "/inc/load_cache.php";
include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/all_years_chart.php";
// force reload caches
if (isset($_SESSION['lastupdate'])) {
    $_SESSION['lastupdate'] = 0;
}

$paramstr_choose = '';
$paramstr_day = '';
# remove naam parameter
if (sizeof($_GET) > 0) {
    foreach ($_GET as $key => $value) {
        if (!(($key == "naam") || ($key == "type"))) {
            $paramstr_choose .= $key . "=" . $value . "&";
        }
        if ($key != "dag") {
            $paramstr_day .= $key . "=" . $value . "&";
        }
    }
}
if (strpos($paramstr_day, "?") == 0) {
    $paramstr_day = '?' . $paramstr_day;
}
if (strpos($paramstr_choose, "?") == 0) {
    $paramstr_choose = '?' . $paramstr_choose;
}
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
    <?php $myKeys = array_keys($sum_per_year); ?>
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(148px <?php echo $padding; ?>); ">
        <div id="menu_header" class="<?= MENU_CLASS ?>" style="height: 45px; background: #222; vertical-align: middle;">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>


        <div id="chart_header" class="<?= HEADER_CLASS ?>" style="display: grid; align-content: center; ">
            <h2><?php echo getTxt("totaaloverzicht") . "&nbsp;" . min($myKeys) . " - " . max($myKeys); ?></h2>
        </div>
        <div id="total_chart"
             style="width:100%;background-color: <?= $colors['color_chartbackground'] ?>;height:100%; <?php echo $corners; ?>">
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <script>
        $(document).ready(function () {
            $("#resize ").height(500);
        });
    </script>
</div><!-- closing ".page-content" -->
</div><!-- closing "container" -->

</body>
</html>
