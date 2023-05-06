<?php
include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";
include_once "inc/header.php";
include_once "charts/cumulative_chart.php";
if (isset($_POST['add'])){
echo '
<script type="text/javascript">
location.reload();
</script>';
}
?>
<?php include "menu.php"; ?>
<div id="page-content">
    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 66px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <form action = "<?php $_SERVER['PHP_SELF']; ?>" method = "POST">
                <button class="btn btn-primary" name='add' type="submit" value='+'>+</button>
                <?php echo "&nbsp";	echo "  " .$title." ";?>
				</form>	
            </h2>
        </div>
        <div id="universal" style="width:100%; !important; height:100%; !important;"></div>
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