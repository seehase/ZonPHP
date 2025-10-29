<?php
global $locale, $params, $chartdate, $datum, $colors, $nice_max_date;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
$isIframe = true;
include_once "../charts/day_chart.php";
?>

<script>
    const start = '<?= date('Y-m-d', $_SESSION['date_minimum']) ?>';
    const language = '<?= substr($locale, 0, 2) ?>';
    $(document).ready(function () {
        $('#datepicker').datepicker({
            setDate: new Date(),
            startDate: start,
            endDate: '+0d',
            language: language,
            todayHighlight: true,
            autoclose: true,
        })
            .datepicker().on('changeDate', function (e) {
            const url = "day.php?date=" + e.format();
            window.open(url, "_self");

        });
        $("#resize ").height(<?= BIG_CHART_HIGHT ?>);
    });
</script>
<?php
$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';
?>

<canvas id="day_chart_canvas"></canvas>

</body>
</html>
