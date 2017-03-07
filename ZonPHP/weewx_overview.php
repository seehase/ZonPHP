<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";

$id = "";
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

?>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxscrollbar.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxpanel.js"></script>


<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxchart.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxgauge.js"></script>


<?php include "menu.php"; ?>
<div id="page-content">
    <div id="container">
        <div id="bodytext">

            <div class="ui-widget-header headerbox">
                <h2 align="center">

                </h2>
                <br/>

            </div>
        </div>
        <div id="resize">
            <div id="gauge_chart1" style="width:100%; height:100%;">
                <?php include_once "charts/weewx_all_values.php"; ?>
            </div>
        </div>
    </div>


</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>