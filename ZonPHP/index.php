<?php

include_once "parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";

include_once "inc/import_data.php";

$aoplopendkwdag[] = 0;

include_once "inc/header.php";

$daytext = getTxt("chart_dayoverview");
if (isset($use_weewx) && $use_weewx == true) {
    $daytext = getTxt("chart_solar_temp");
}

?>
<script src="https://jqwidgets.com/public/jqwidgets/jqxcore.js"></script>
<script src="https://jqwidgets.com/public/jqwidgets/jqxscrollbar.js"></script>
<script src="https://jqwidgets.com/public/jqwidgets/jqxbuttons.js"></script>
<script src="https://jqwidgets.com/public/jqwidgets/jqxpanel.js"></script>
<script src="https://jqwidgets.com/public/jqwidgets/jqxchart.js"></script>
<script src="https://jqwidgets.com/public/jqwidgets/jqxgauge.js"></script>

<?php include "inc/menu.php"; ?>

<div id="page-content">

    <script>
        $(function () {
            // pass txt to JavaScript
            txt = <?php echo json_encode($txt); ?>;
            daytext = <?php echo '"' . $daytext . '"'; ?>;
            charts = <?php echo json_encode($charts); ?>;
            colors = <?php echo json_encode($colors); ?>;
        });

    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/velocity/1.5.0/velocity.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/muuri/0.4.0/muuri.min.js"></script>

    <script src="inc/js/index_charts.js"></script>
    <script>
        $(document).ready(function () {
            docReady(load_charts());
        });
    </script>
    <!-- here comes all the charts-->
    <div class="grid">
        <!-- The Modal -->
    </div>


</div><!-- closing ".page-content" -->
<div id="spacer" style="float: left; height: 40px; width: 400px; display: block">
    <br>&nbsp;
</div>

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>
