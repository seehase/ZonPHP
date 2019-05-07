<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/year_chart.php";


$nextyearvisible = false;
$nextyear = strtotime("+1 year", $chartdate);
$nextyearstring = strftime("%Y-01-01", strtotime("+1 year", $chartdate));
if ($nextyear <= $date_maximum) {
    $nextyearvisible = true;
}
$prevyear = strtotime("-1 year", $chartdate);
$prevyearstring = strftime("%Y-%m-%d", strtotime("-1 year", $chartdate));

$date_minimum1 = strtotime("-1 year", $date_minimum);

if ($prevyear >= $date_minimum1) {
    $prevyearvisible = true;
} else {
    $prevyearvisible = false;
}

if ($nextyear <= $date_maximum) {
    $nextyearvisible = true;
}

$chartyeardatestring = strftime("%Y-01-01", strtotime("+0 year", $date_maximum));
#$prevyear = strftime("%Y-%m-%d", strtotime("-1 year", $chartdate));

?>

<?php include "menu.php"; ?>

<?php
$choose_inverter_dropdown = "";
$multiple_inverters = false;
$choose_inverter_items = "";
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
foreach ($sNaamSaveDatabase as $key => $sdbnaam) {
    $choose_inverter_items .= '<li><a href="#" onclick="myDropdownFunction(\'' . $sdbnaam . '\')">' . $sdbnaam . '</a></li>';
}
if (strlen($choose_inverter_items) > 0) {
    $choose_inverter_dropdown = '
                        <div style="position: absolute; z-index: 50">
        
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" style="margin-top: 15px;margin-left: 20px;">' .
        $txt['choose_inverter'] . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu"> ' .
        $choose_inverter_items .
        '<li><a href="#" onclick="myDropdownFunction(\'all\')">' . $txt['all_inverters'] . '</a></li>' . '
                            </ul>
                        </div>
                
                ';
    $multiple_inverters = true;
}
?>

<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">

            <?php
            if ($multiple_inverters) echo $choose_inverter_dropdown;
            ?>

            <h2 align="center">
                <?php
                if ($prevyearvisible) {
                    echo '<a class="btn btn-primary" href="year_overview.php' . $paramstr_day . 'jaar=' . $prevyearstring . '"> < </a>';
                }
                echo " " . $txt["jaar"] . " " . $datum . " ";
                if ($nextyearvisible) {
                    echo '<a class="btn btn-primary" href="year_overview.php' . $paramstr_day . 'jaar=' . $nextyearstring . '"> > </a>';
                }
                ?>
            </h2>
        </div>

        <div id="year_chart_<?php echo $inverter_id ?>" style=":width100%; height:100%;"></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button class="btn btn-primary" id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
        <a href="<?php echo "year_overview.php" . $paramstr_day . "jaar=" . $chartyeardatestring ?>" target="_self">
            <button class="btn btn-primary"><?php echo $txt['back_to_today'] ?></button>
        </a>
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