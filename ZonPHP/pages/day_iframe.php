<?php
global $locale, $params, $chartdate, $datum, $colors, $nice_max_date;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
$isIframe = true;
include_once "../charts/day_chart.php";
?>

<?php
$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';
?>

<canvas id="day_chart_canvas"></canvas>

</body>
</html>
