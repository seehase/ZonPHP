<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/year_chart.php";



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
                <form action="<?php $_SERVER['PHP_SELF']; ?>" method="GET">
                <?php if (date("y", $date_minimum) < date("y", $chartdate))
                        echo '<button class="btn btn-primary" type="submit" name="jaar" value= ' . date('Y', strtotime("-1 year", $chartdate)) . '  >  <  </button>';
                    echo " " . $txt["jaar"] . " " . $datum . " " . '  <input type="hidden" id="startdate" value="' . strftime("%d-%m-%Y", time()) . '" readonly>  ';
                    if (date("y", $date_maximum) > date("y", $chartdate))
                        echo '<button class="btn btn-primary" type="submit" name="jaar" value= ' . date('Y', strtotime("+1 year", $chartdate)) . '  >  > </button>';
                    ?>
                
            </h2>
        </div>

        <div id="year_chart_<?php echo $inverter_id ?>" style=":width100%; height:100%;"></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <form action="<?php $_SERVER['PHP_SELF']; ?>" method="GET">
            <button class="btn btn-primary" type="submit" id="txt" name="dag"
                    value="<?php echo date('Y-m-d', $chartcurrentdate); ?>"><?php echo $txt["terugnaarvandaag"] ?></button>
        </form>
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