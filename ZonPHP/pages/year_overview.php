<?php
include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/import_data.php";
include_once ROOT_DIR."/inc/header.php";
include_once "../charts/year_chart.php";
?>
<?php include ROOT_DIR."/inc/menu.php"; ?>
<?php
$paramstr_choose = '';
$paramstr_day = '';
# remove naam parameter
if (sizeof($_GET) > 0) {
    foreach ($_GET as $key => $value) {
        if (!(($key == "naam") || ($key == "type"))) {
            $paramstr_choose .= $key . "=" . $value . "&";
        }
        if ($key != "jaar") {
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
?>
<div id="page-content">
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 56px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2>
                <button class="btn btn-zonphp" onclick="window.location.href='<?php echo '?jaar=' . date('Y-m', strtotime("-1 year", $chartdate)) . '\'"' ;
				if (date('Y', $date_minimum) >= date('Y', $chartdate)) echo " hidden";	?>><i class="fa fa-angle-left fa-lg"></i></button>
				<?php echo $datum ?>
                <button class="btn btn-zonphp" onclick="window.location.href='<?php echo '?jaar=' . date('Y-m', strtotime("+1 year", $chartdate)) . '\'"' ;
				if (date('Y-m', $date_maximum) <= date('Y-m', $chartdate)) echo " hidden";	?>><i class="fa fa-angle-right fa-lg"></i></button>
            </h2>
        </div>
        <div class="backtoday" style="float:none; position: absolute;  top: 15px;  left: 15px;">
            <button class="btn btn-zonphp" onclick="window.location.href='<?php echo '?jaar='.date('Y', $chartcurrentdate); ?>'"><?php echo getTxt("back_to_today") ?></button>
        </div>
        <div id="year_chart" style="width:100%; height:100%;"></div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#resize ").height(<?php echo $big_chart_height ?>);
    });
</script>
</div><!-- closing ".page-content" -->
<?php include_once ROOT_DIR."/inc/footer.php"; ?>
</body>
</html>
