<?php
include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/import_data.php";
include_once ROOT_DIR."/inc/header.php";
include_once "../charts/day_chart.php";
?>
<?php include_once ROOT_DIR."/inc/menu.php"; ?>
<script>
	var start = '<?php echo date('Y-m-d',$date_minimum) ?>';
	var language = '<?php echo substr($locale,0,2) ?>';
    $(document).ready(function(){
	$('#datepicker').datepicker( {
    setDate: new Date(),
    startDate: start,
    endDate: '+0d',
    language: language ,
    todayHighlight: true,
    autoclose: true,
 	});
	$('#datepicker').datepicker().on('changeDate', function (e) {
    var url = "day_overview.php?dag=" + e.format();
                window.open(url, "_self");
	//alert(language);
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
?>
<div id="page-content">
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 57px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
                <h2>
                <button class="btn btn-zonphp " onclick="window.location.href='<?php echo '?dag=' . date('Y-m-d', strtotime("-1 day", $chartdate)) . '\'"' ;
				if ($date_minimum >= $chartdate) echo " hidden";	?>><i class="fa fa-angle-left fa-lg"></i></button>
				<?php echo $datum ?>
                <button class="btn btn-zonphp" onclick="window.location.href='<?php echo '?dag=' . date('Y-m-d', strtotime("+1 day", $chartdate)) . '\'"' ;
				if ($date_maximum <= $chartdate) echo " hidden";	?>><i class="fa fa-angle-right fa-lg"></i></button>
               </h2>
        </div>
  		<div class="backtoday align-top" style="none:left; position: absolute;  left: 15px;">
           		<button class="btn btn-zonphp" onclick="window.location.href='<?php echo '?dag='.date('Y-m-d', $chartcurrentdate); ?>'"><?php echo getTxt()"back_to_today") ?></button>
		    <div class="buttonbox" >
  				<div class="input-group date" id="datepicker" data-date-format="yyyy-mm-dd">	
  					<input type='hidden' id='untilDate' class="form-control"   />
    				<button class="btn btn-zonphp" ><i class="fa fa-calendar"></i></button>
   				</div>
	 		</div>
 		</div>
        <div id="mycontainer" style="width:100%; height:100%;"></div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#resize ").height(<?php echo $big_chart_height ?>);
    });
</script>
</div><!-- closing ".page-content" -->
<?php include_once ROOT_DIR."/inc/footer.php"; ?>
</body>
</html>
