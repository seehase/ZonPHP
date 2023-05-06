<?php

include_once  "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/header.php";

?>


<?php include "menu.php";?>

<div id="container" >

    <div id="page-content">

        <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 72px; ">

            <div id="week_chart_header" class="<?= HEADER_CLASS ?>">

                <h2>
                    <?php echo $txt["dagboekmenu"] ?>
                </h2>

            </div>
            <p style="text-align: left; margin-left: 20px">
            <?php include "tagebuch.txt" ?>
            </p>
        </div>

    </div>
</div>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>
