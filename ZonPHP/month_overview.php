<?php
include_once "parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/month_chart.php";
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

<?php include "inc/menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 66px; ">

        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <form action="<?php $_SERVER['PHP_SELF']; ?>" method="GET">
                    <?php if (date('Y-m', $date_minimum) < date('Y-m', $chartdate))
                        echo '<button class="btn btn-primary" type="submit" name="maand" value= ' . date('Y-m-d', strtotime("-1 months", $chartdate)) . '  >  < </button>';
                    echo " " . $datum . " ";
                    if (date('Y-m', $date_maximum) > date('Y-m', $chartdate))
                        echo '<button class="btn btn-primary" type="submit" name="maand" value= ' . date('Y-m-d', strtotime("+1 months", $chartdate)) . '  >  > </button>';
                    ?>
                </form>
            </h2>
        </div>
        <div class="backtoday" style="float:none; position: absolute;  top: 15px;  left: 15px;">
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="GET">
                <button class="btn btn-primary" type="submit" id="txt" name="dag"
                        value="<?php echo date('Y-m-d', $chartcurrentdate); ?>"><?php echo $txt["terugnaarvandaag"] ?></button>
            </form>
        </div>
        <div id="month_chart_<?php echo $inverter_id ?>" style="width:100%; !important; height:100%; !important;"></div>
    </div>


    <div id="kalender">

    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#resize ").height(<?php echo $big_chart_height ?>);
    });
</script>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>
