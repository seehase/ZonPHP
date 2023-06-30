<?php
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/sessionstart.php";
include_once ROOT_DIR . "/inc/load_cache.php";
include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/year_chart.php";
?>
<script>
    var start = '<?php echo date('Y-m-d', $date_minimum) ?>';
    var language = '<?php echo substr($locale, 0, 2) ?>';
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
            var url = "year_overview.php?jaar=" + zonP;
            window.open(url, "_self");
            //alert(zonP);
        });
    });
</script>

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
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(148px <?php echo $padding; ?>); ">

        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>

        <div id="chart_header" class="<?= HEADER_CLASS ?>">
            <h2>
                <button class="btn btn-zonphp"
                        onclick="window.location.href='<?php echo '?jaar=' . date('Y-m', strtotime("-1 year", $chartdate)) . '\'"';
                        if (date('Y', $date_minimum) >= date('Y', $chartdate)) echo " hidden"; ?>><i class=" fa
                        fa-angle-left fa-lg
                "></i></button>
                <?php echo $datum ?>
                <button class="btn btn-zonphp"
                        onclick="window.location.href='<?php echo '?jaar=' . date('Y-m', strtotime("+1 year", $chartdate)) . '\'"';
                        if (date('Y-m', $date_maximum) <= date('Y-m', $chartdate)) echo " hidden"; ?>><i class=" fa
                        fa-angle-right fa-lg
                "></i></button>
            </h2>
            <div class="block2">
                <div class="inner">
                    <button class="btn btn-zonphp"
                            onclick="window.location.href='<?php echo '?jaar=' . date('Y', $chartcurrentdate); ?>'"><?= getTxt("back_to_today") ?></button>
                    <div class="inner">
                        <div class="input-group date" id="datepicker" data-date-format="yyyy-mm-dd">
                            <input type='hidden' id='untilDate' class="form-control">
                            <button class="btn btn-zonphp"><i class="fa fa-calendar"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="year_chart"
             style="width:100%; background-color: #<?php echo $colors['color_chartbackground'] ?>;height:100%; <?php echo $corners; ?>">
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <script>
        $(document).ready(function () {
            $("#resize ").height(<?php echo $big_chart_height ?>);
        });
    </script>
</div><!-- closing ".page-content" -->
</div><!-- closing "container" -->

</body>
</html>
