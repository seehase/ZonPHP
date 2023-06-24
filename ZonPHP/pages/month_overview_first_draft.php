<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/sessionstart.php";
include_once ROOT_DIR . "/inc/load_cache.php";
include_once ROOT_DIR . "/inc/import_data.php";

include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/month_chart.php";
?>

<?php
$paramstr_choose = '';
$paramstr_day = '';
# remove naam parameter
if (sizeof($_GET) > 0) {
    foreach ($_GET as $key => $value) {
        if (!(($key == "naam") || ($key == "type"))) {
            $paramstr_choose .= $key . "=" . $value . "&";
        }
        if ($key != "maand") {
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

<?php include ROOT_DIR . "/inc/menu.php"; ?>
<?php
$link_back = "window.location.href='" . $_SERVER['PHP_SELF'] . "?maand=" . date('Y-m-d', strtotime("-1 months", $chartdate)) . "'\";>";
$link_next = "window.location.href='" . $_SERVER['PHP_SELF'] . "?maand=" . date('Y-m-d', strtotime("+1 months", $chartdate)) . "'\";>";
$link_today = "window.location.href='" . $_SERVER['PHP_SELF'] . "?maand=" . date('Y-m-d', $chartcurrentdate) . "'\";>";
?>
<div id="page-content">
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 66px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2>
                <?php if (date('Y-m', $date_minimum) < date('Y-m', $chartdate)) {
                    echo '<button class="btn btn-zonphp" onclick="' . $link_back . '<- </button>';
                }
                echo " " . $datum . " ";
                if (date('Y-m', $date_maximum) > date('Y-m', $chartdate)) {
                    echo '<button class="btn btn-zonphp" onclick="' . $link_next . '-></button>';
                }
                ?>
            </h2>
        </div>
        <div class="backtoday" style="float:none; position: absolute;  top: 15px;  left: 15px;">
            <button class="btn btn-zonphp" onclick="<?php echo $link_today ?><?php echo getTxt("terugnaarvandaag") ?>
            </button>
        </div>
        <div id="month_chart" style="width:100%; !important; height:100%; !important;"></div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#resize ").height(<?php echo $big_chart_height ?>);
    });
</script>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once ROOT_DIR . "/inc/footer.php"; ?>

</body>
</html>
