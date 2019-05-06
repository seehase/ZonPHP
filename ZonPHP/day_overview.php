<?php
include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

$chartcurrentdate=@mktime();
$chartdate=$chartcurrentdate;
    if(isset($_GET['dag']))
    $chartdate=strtotime($_GET['dag'])    ;

include_once "inc/header.php";
include_once "charts/day_chart.php";
?>
<?php include_once "menu.php"; ?>
<script type="text/javascript">

    $(function () {

        $("#startdate, #enddate").datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: "button",
            gotoCurrent: false,
            showButtonPanel: true,
            buttonImage: "inc/image/calendar.gif",
            buttonImageOnly: true,
            buttonText: "Select date",
            onSelect: function () {
                var url = "day_overview.php?dag=" + $("#startdate").val();
                window.open(url, "_self");
            },
        });
        var _gotoToday = jQuery.datepicker._gotoToday;
        jQuery.datepicker._gotoToday = function (a) {
            var target = jQuery(a);
            var inst = this._getInst(target[0]);
            _gotoToday.call(this, a);
            jQuery.datepicker._selectDate(a, jQuery.datepicker._formatDate(inst, inst.selectedDay, inst.selectedMonth, inst.selectedYear));
        };
    });
</script>
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
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 72px; ">

        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">

            <?php
            if ($multiple_inverters) echo $choose_inverter_dropdown;
            ?>
			<form action = "<?php $_SERVER['PHP_SELF']; ?>" method = "GET">
				<h2 align="center" class="notopgap" >
					<?php if($date_minimum<$chartcurrentdate)
    				echo '<button class="btn btn-primary" type="submit" name="dag" value= ' .date('Y-m-d',strtotime("-1 day", $chartdate)). '  >  <  </button>';
    				echo " ".$datum." ";
    				if(date("y-m-d",$date_maximum)> date("y-m-d",$chartdate))
    				echo '<button class="btn btn-primary" type="submit" name="dag" value= ' .date('Y-m-d',strtotime("+1 day", $chartdate)). '  >  > </button>';
    				?>
				</h2>
			</form>
        </div>
    			<div id="mycontainer_<?php echo $inverter_id ?>" style="width:100%; height:100%;"></div>
    			</div>
    <div style="float: unset; margin-top: 5px;">
        <form action = "<?php $_SERVER['PHP_SELF']; ?>" method = "GET">
		<button class="btn btn-primary" type="submit" id = "txt" name="dag"  value="<?php echo date('Y-m-d',$chartcurrentdate); ?>"><?php echo $txt['back_to_today'] ?></button></form>
    </div>
    <div id="kalender">
    </div>
    <div id="tabelgeg">
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
