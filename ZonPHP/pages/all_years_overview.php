<?php

include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/import_data.php";

include_once ROOT_DIR."/inc/header.php";

include_once "../charts/all_years_chart.php";

// force reload caches
if (isset($_SESSION['lastupdate'])) {
    $_SESSION['lastupdate'] = 0;
}

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

?>

<div id="page-content">
    <?php $myKeys = array_keys($sum_per_year); ?>

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 41px;">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2><?php echo $txt["totaaloverzicht"] . "&nbsp;" . min($myKeys) . " - " . max($myKeys); ?></h2>
        </div>

        <div id="total_chart_<?php echo $inverter_id ?>" style="width:100%; height:100%;"></div>
    </div>


</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#resize ").height(500);
    });
</script>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->
<?php include_once ROOT_DIR."/inc/footer.php"; ?>

</body>
</html>
