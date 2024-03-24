<?php
global $params, $title, $colors, $years, $visibleInvertersArray, $selectedYears;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";
include_once ROOT_DIR . "/inc/header.php";
include_once ROOT_DIR . "/charts/chart_support.php";
include_once ROOT_DIR . "/charts/years_cumulative_chart.php";

$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';
?>
<script>
    $(document).ready(function () {
        $("#resize ").height(<?= BIG_CHART_HIGHT ?>);
    });
</script>

<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(136px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>
        <div id="chart_header" class="<?= HEADER_CLASS ?>">
            <h2>
                <?= getTxt("chart_years_cumulative_view") ?>
            </h2>
            <div class="inner" id="filter" style="z-index: 999 !important; position:relative">
                <a onclick="myPrompt()" class="p-1 btn btn-zonphp" data-bs-toggle="collapse"
                   id="myPrompt" role="button" aria-expanded="false"
                   aria-controls="collapseExample"
                   style="border-top-width: 1px; border-bottom-width: 1px; height: 27px;  vertical-align: top; "><?= getTxt("filter"); ?></a>
            </div>
        </div>
        <script>

            function myOK() {
                window.location.href =
                    "?inverters=" + getSelectedInverters() +
                    "&years=" + getSelectedYears();
            }


        </script>
        <dialog id="prompt" role="alertdialog" aria-labelledby="prompt-dialog-heading">
            <script>
                $(document).ready(function () {
                    updateCheckedYears();
                    updateCheckedInverters();
                });
            </script>

            <h2 id="prompt-dialog-heading"><?= getTxt("filter"); ?></h2>
            <div class="table_component">
                <table>
                    <thead>
                    <tr>
                        <th><?= getTxt("inverter"); ?></th>
                        <th><?= getTxt("year"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <input type='checkbox' id='all_inverter' name='all_inverters' value='all_inverter'
                                   onclick="checkInverters()">
                            <label for='all_inverter'> <?= getTxt("all") ?></label><br>
                        </td>
                        <td>
                            <input type='checkbox' id='all_year' name='all_years' value='all_year'
                                   onclick="checkYears()">
                            <label for='all_year'> <?= getTxt("all") ?></label><br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            foreach (PLANT_NAMES as $inverter) {
                                echo "<input type='checkbox' id='$inverter' name='inverters' value='$inverter'" . getIsCheckedString($inverter, $visibleInvertersArray) . " onclick='updateCheckedInverters()' >";
                                echo "<label for='$inverter'>" . $inverter . "</label><br>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            foreach ($years as $year) {
                                echo "<input type='checkbox' id='$year' name='years' value='$year'" . getIsCheckedString($year, $selectedYears) . " onclick='updateCheckedYears()' >";
                                echo "<label for='$year'> " . $year . "</label><br>";
                            }
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <br>
            <p class="button-row">
                <button class="p-1 btn btn-zonphp" name="cancel" onclick="myCancel()"><?= getTxt("cancel"); ?></button>
                &nbsp; &nbsp;
                <button class="p-1 btn btn-zonphp" name="ok" onclick="myOK()"><?= getTxt("ok"); ?></button>
            </p>
        </dialog>
        <div id="universal"
             style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>; height: <?= BIG_CHART_HIGHT ?>px; <?= $corners; ?>">
            <canvas id="cumulative_chart_canvas"></canvas>
        </div>
        <?php include_once ROOT_DIR . "/inc/footer.php"; ?>
    </div>
    <br>
</div><!-- closing ".page-content" -->
</body>
</html>