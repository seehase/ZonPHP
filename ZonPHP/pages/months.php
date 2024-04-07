<?php
global $chartdate, $params, $colors;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/months_chart.php";

$inverter = $_SESSION['plant'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}
$nextyear = date("Y-m-d", strtotime("+1 year", $chartdate));
$prevyear = date("Y-m-d", strtotime("-1 year", $chartdate));

$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';

?>
<script>
    $(document).ready(function () {
        $("#resize ").height(<?= BIG_CHART_HIGHT ?>);
    });
</script>

<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(136px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>

        <div id="chart_header" class="<?= HEADER_CLASS ?>" style="display: grid; align-content: center; ">
            <h2>
                <?php
                echo getTxt("chart_months_view");
                ?>
            </h2>
        </div>
        <div id="years_chart_<?= $inverter ?>"
             style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>;height: <?= BIG_CHART_HIGHT ?>px; <?= $corners; ?>">
            <canvas id="last_year_chart_canvas"></canvas>
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <br>
</div><!-- closing ".page-content" -->
</body>
</html>
