<?php
global $params, $colors, $month_local, $visibleInvertersJS, $years, $sort, $selectedMonths, $selectedYears;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

unset($agegevens);
unset($adatum);
unset($fgemiddelde);
unset($amaxref);

include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/ranking_chart.php";

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
        <div id="chart_header" class="<?= HEADER_CLASS ?>" style=" align-content: center; ">
            <h2><?= getTxt("ranking"); ?></h2>
            <div class="block2">
                <div class="inner" id="Sort" style="z-index: 999 !important; position:relative">
                    <a onclick="toggleText()" class="p-1 btn btn-zonphp" data-bs-toggle="collapse"
                       id="toggle" role="button" aria-expanded="false"
                       aria-controls="collapseExample"
                       style="border-top-width: 1px; border-bottom-width: 1px; height: 27px;  vertical-align: top; "><?= getTxt($sort); ?></a>
                </div>
                <div class="inner" id="filter" style="z-index: 999 !important; position:relative">
                    <a onclick="myPrompt()" class="p-1 btn btn-zonphp" data-bs-toggle="collapse"
                       id="myPrompt" role="button" aria-expanded="false"
                       aria-controls="collapseExample"
                       style="border-top-width: 1px; border-bottom-width: 1px; height: 27px;  vertical-align: top; "><?= getTxt("filter"); ?></a>
                </div>
            </div>
            <script>
                $(document).ready(function () {
                    $('.selectpicker').selectpicker('toggle');
                    updateCheckedYears();
                    updateCheckedMonths();
                });
            </script>
            <script>
                let sort = "<?= $sort ?>";

                function myOK() {
                    window.location.href = "?sort=" + sort +
                        "&inverters=" + "<?= $visibleInvertersJS ?>" +
                        "&months=" + getSelectedMonths() +
                        "&years=" + getSelectedYears();
                }

                function toggleText() {
                    let x = document.getElementById("toggle");
                    if (sort === "desc") {
                        x.innerHTML = "<?= getTxt("asc"); ?>";
                        sort = "asc";
                    } else {
                        x.innerHTML = "<?= getTxt("desc"); ?>";
                        sort = "desc";
                    }

                    window.location.href = "?sort=" + sort +
                        "&inverters=" + "<?= $visibleInvertersJS ?>" +
                        "&months=" + getSelectedMonths() +
                        "&years=" + getSelectedYears();
                }
            </script>
        </div><!--.chart_header-->
        <dialog id="prompt" role="alertdialog" aria-labelledby="prompt-dialog-heading">
            <h2 id="prompt-dialog-heading"><?= getTxt("filter"); ?></h2>
            <div class="table_component">
                <table>
                    <thead>
                    <tr>
                        <th><?= getTxt("month"); ?></th>
                        <th><?= getTxt("year"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <input type='checkbox' id='all_months' name='all_months' value='all_months'
                                   onclick="checkMonths()">
                            <label for='all_months'> <?= getTxt("all") ?></label><br>
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
                            for ($k = 1; $k <= count($month_local); $k++) {
                                echo "<input type='checkbox' id='$k' name='months' value='$k'" . getIsCheckedString($k, $selectedMonths) . " onclick='updateCheckedMonths()'> ";
                                echo "<label for='$k'>" . $month_local[$k]['MMMM'] . "</label><br>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            foreach ($years as $item) {
                                echo "<input type='checkbox' id='$item' name='years' value='$item' " . getIsCheckedString($item, $selectedYears) . "  onclick='updateCheckedYears()'> ";
                                echo "<label for='$item'> " . $item . "</label><br>";
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
        <div id="top31_chart"
             style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>; height: <?= BIG_CHART_HIGHT ?>px; <?= $corners; ?>">
            <canvas id="day_ranking_chart_canvas"></canvas>
        </div>

        <div>
            <?php include_once ROOT_DIR . "/inc/footer.php" ?>
        </div>
        <br>
    </div>
</div><!-- closing ".page-content" -->
</body>
</html>
