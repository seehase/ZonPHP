<?php
global $chartdate, $params, $colors;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/last_years_chart.php";

$inverter = $_SESSION['plant'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}
$nextyear = date("Y-m-d", strtotime("+1 year", $chartdate));
$prevyear = date("Y-m-d", strtotime("-1 year", $chartdate));

$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';

?>
<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(136px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>


        <div id="chart_header" class="<?= HEADER_CLASS ?>" style="display: grid; align-content: center; ">
            <h2>
                <?php
                //echo '<a class="btn btn-zonphp" href="last_years_overview.php' . $paramstr_day .'date=' . $prevyear . '"> < </a>';
                echo getTxt("chart_lastyearoverview");
                //echo '<a class="btn btn-zonphp" href="last_years_overview.php' . $paramstr_day .'date=' . $nextyear . '"> > </a>';
                ?>
            </h2>
        </div>
        <div id="all_years_chart_<?= $inverter ?>"
             style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>;height:100%; <?= $corners; ?>">
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
