<?php
include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/import_data.php";
include_once ROOT_DIR."/inc/header.php";
include_once "../charts/cumulative_chart.php";
?>
<?php include ROOT_DIR."/inc/menu.php"; ?>
<div id="page-content">
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 57px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>" style = "display: grid; align-content: center; " >
            <h2>
  				<?php echo $txt["omvormer"]," ", $title;?>
            </h2>
        </div>
        <div class="backtoday" style="float:none; position: absolute;  top: 15px;  left: 15px;">
        	<form method="POST">
                   <button class="btn btn-zonphp" name='add' type="submit" value='+'>+</button>
        	</form>    
        </div>
        <div id="universal" style="width:100%; !important; height:100%; !important;"></div>
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
