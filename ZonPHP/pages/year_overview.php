<?php
global $locale, $params, $datum, $chartcurrentdate, $colors, $chartdate;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/year_chart.php";
?>
<script>
    var start = '<?= date('Y-m-d', $_SESSION['date_minimum']) ?>';
    var language = '<?= substr($locale, 0, 2) ?>';
    $(document).ready(function () {
        $('#datepicker').datepicker({
            setDate: new Date(),
            startDate: start,
            endDate: '+0d',
            language: language,
            startView: "years",
            minViewMode: "years",

            autoclose: true,
        });

        $('#datepicker').datepicker().on('changeYear', function (e) {
            var d = new Date(e.date.valueOf());
            var zonP = (d.getFullYear() + '-' + (d.getMonth() + 1));
            var url = "year_overview.php?date=" + zonP;
            window.open(url, "_self");
            //alert(zonP);
        });
    });
</script>

<?php

$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';

?>
<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(136px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>
        <div id="chart_header" class="<?= HEADER_CLASS ?>">
            <h2>
                <button class="btn btn-zonphp "
                        onclick="window.location.href='<?php echo '?date=' . date('Y-m-d', strtotime("-1 year", $chartdate)) . '\'"';
                        if ($_SESSION['date_minimum'] >= $chartdate) echo " hidden"; ?> >
                                <i class=" fa fa-angle-left fa-lg
                "></i>
                </button>
                <?= $datum ?>
                <button class="btn btn-zonphp"
                        onclick="window.location.href='<?php echo '?date=' . date('Y-m-d', strtotime("+1 year", $chartdate)) . '\'"';
                        if ($_SESSION['date_maximum'] <= $chartdate) echo " hidden"; ?> >
                                <i class=" fa fa-angle-right fa-lg
                "></i>
                </button>
            </h2>
            <div class="block2">
                <div class="inner">
                    <button class="btn btn-zonphp"
                            onclick="window.location.href='<?= '?date=' . date('Y-m-d', $chartcurrentdate); ?>'"><?= getTxt("back_to_today") ?>
                    </button>
                    <div class="inner">
                        <div class="input-group date" id="datepicker" data-date-format="yyyy-mm-dd">
                            <input type='hidden' id='untilDate' class="form-control">
                            <button class="btn btn-zonphp"><i class="fa fa-calendar"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="year_chart" class="demo"
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
