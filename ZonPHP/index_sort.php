<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/header.php";

?>


<?php include "menu.php"; ?>

<div id="page-content">

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

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/web-animations/2.3.1/web-animations.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/muuri@0.6.3/dist/muuri.min.js"></script>

    <link type="text/css" rel="stylesheet" href="index_sort.css">
    <script type="text/javascript" src="index_sort.js"></script>

    <!-- here comes all the charts-->
    <div class="board">
        <div class="board-column todo">
            <div class="board-column-header">Available Charts</div>
            <div class="board-column-content-wrapper">
                <div class="board-column-content">
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_monthoverview'] ?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_dayoverview']?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_yearoverview'] ?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_allyearoverview'] ?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_cumulativeoverview'] ?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_lastyearoverview']?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['slechtste']?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['beste']?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_indoor']?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_all_temp']?></div></div>
                    <div class="board-item"><div class="board-item-content"><?php echo $txt['chart_all_humidity']?></div></div>
                </div>
            </div>
        </div>
        <div class="board-column working">
            <div class="board-column-header">Active Charts</div>
            <div class="board-column-content-wrapper">
                <div class="board-column-content">
                    <div class="board-item"><div class="board-item-content"><span>Item #</span>8</div></div>
                    <div class="board-item"><div class="board-item-content"><span>Item #</span>9</div></div>
                    <div class="board-item"><div class="board-item-content"><span>Item #</span>10</div></div>
                </div>
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