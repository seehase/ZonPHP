<?php
include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/import_data.php";

include_once ROOT_DIR."/inc/header.php";
include_once "../charts/year_chart.php";

$nextyearvisible = false;
$nextyear = strtotime("+1 year", $chartdate);
$nextyearstring = date("Y-01-01", strtotime("+1 year", $chartdate));
if ($nextyear <= $date_maximum) {
    $nextyearvisible = true;
}
$prevyear = strtotime("-1 year", $chartdate);
$prevyearstring = date("Y-m-d", strtotime("-1 year", $chartdate));

$date_minimum1 = strtotime("-1 year", $date_minimum);

if ($prevyear >= $date_minimum1) {
    $prevyearvisible = true;
} else {
    $prevyearvisible = false;
}

if ($nextyear <= $date_maximum) {
    $nextyearvisible = true;
}

$chartyeardatestring = date("Y-01-01", strtotime("+0 year", $date_maximum));
#$prevyear = date("Y-m-d", strtotime("-1 year", $chartdate));

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

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2>
                <?php
                if ($prevyearvisible) {
                    echo '<a class="btn btn-zonphp" href="year_overview.php' . $paramstr_day . 'jaar=' . $prevyearstring . '"> < </a>';
                }
                echo " " . $txt["jaar"] . " " . $datum . " ";
                if ($nextyearvisible) {
                    echo '<a class="btn btn-zonphp" href="year_overview.php' . $paramstr_day . 'jaar=' . $nextyearstring . '"> > </a>';
                }
                ?>
            </h2>
        </div>
        <div class="backtoday" style="float:none; position: absolute;  top: 15px;  left: 15px;">
            <a href="<?php echo "year_overview.php" . $paramstr_day . "jaar=" . $chartyeardatestring ?>" target="_self">
                <button class="btn btn-zonphp"><?php echo $txt["back_to_today"] ?></button>
            </a>
        </div>
        <div id="year_chart" style=":width100%; height:100%;"></div>
    </div>

</div>
<script>
    $(document).ready(function () {
        $("#resize ").height(<?php echo $big_chart_height ?>);
    });
</script>
</div><!-- closing ".page-content" -->
</div><!-- closing ".container" -->
<?php include_once ROOT_DIR."/inc/footer.php"; ?>
</body>
</html>