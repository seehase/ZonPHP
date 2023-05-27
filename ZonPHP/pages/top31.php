<?php
include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/import_data.php";

unset($agegevens);
unset($frefmaand);
unset($adatum);
unset($fgemiddelde);
unset($amaxref);
unset($adatum);

include_once ROOT_DIR."/inc/header.php";
include_once "../charts/top31_chart.php";
$myLabel = "Top ";

$currentview = "top";
$myLabel = " " . $txt["beste"] . "  " . $txt["chart_31days"] . " ";
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
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 41px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">

            <h2 align="center"><?php echo $myLabel; ?></h2>
        </div>
        <div id="top31_chart" style="width:100%; height:100%;"></div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#resize").height(<?php echo $big_chart_height ?>);
    });
</script>
</div><!-- closing ".page-content" -->
</div><!-- closing ".container" -->
<?php include_once "inc/footer.php"; ?>
</body>
</html>
