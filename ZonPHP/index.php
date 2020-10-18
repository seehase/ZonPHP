<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";

include_once "inc/import_data.php";


$aoplopendkwdag[] = 0;

include_once "inc/header.php";

$daytext =  $txt['chart_dayoverview'];
if (isset($use_weewx) && $use_weewx==true){
    $daytext =  $txt['chart_solar_temp'];
}

?>

<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxscrollbar.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxpanel.js"></script>


<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxchart.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxgauge.js"></script>
<!--<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxknob.js"></script>-->

<!-- jQuery Modal -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
-->


<?php include "menu.php"; ?>

<link type="text/css" rel="stylesheet" href="index_sort.css">


<div id="page-content">

    <div style="float: unset; margin-top: 5px;">
        <button class="btn btn-primary" type="submit" id="txt" name="dag" onclick="myTest()" value="test">Test Save</button>
    </div>
    <!--
        todo:
        * Liste of available/selected
        * Show popup (kanban board) to activate/deactivate chatst
        * save layout
        ** default in DB
        ** user specific in session
        * save in JS via POST
    -->

    <script type="text/javascript">
        $(document).ready(function () {

        });

        $(function () {
            // pass txt to JavaScript
            txt = <?php echo json_encode($txt); ?>;
            daytext = <?php echo '"' . $daytext . '"'; ?>;
            charts = <?php echo json_encode($charts); ?>;
            colors = <?php echo json_encode($colors); ?>;
        });

    </script>



    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/velocity/1.5.0/velocity.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/muuri/0.4.0/muuri.min.js"></script>

    <script type="text/javascript" src="index_charts.js"></script>


    <!-- here comes all the charts-->
    <div class="grid">
        <!-- The Modal -->


    </div>


    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="board">
            </div>
        </div>
    </div>

</div><!-- closing ".page-content" -->
<div id="spacer" style="float: left; height: 40px; width: 400px; display: block">
    <br />&nbsp;
</div>

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>