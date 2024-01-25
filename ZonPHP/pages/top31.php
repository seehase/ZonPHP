<?php
global $params, $colors, $locale, $years;
include_once "../inc/init.php";
include_once ROOT_DIR . "/inc/connect.php";

unset($agegevens);
unset($adatum);
unset($fgemiddelde);
unset($amaxref);

include_once ROOT_DIR . "/inc/header.php";
include_once "../charts/top31_chart.php";
$padding = '- 0px';
$corners = 'border-bottom-left-radius: 0px !important; border-bottom-right-radius: 0px;';

$month_local = array();
for ($nn = 1; $nn <= 12; ++$nn) {
    $date = '2024-' . $nn . '-01'; // date only used for loop
    $ts = new DateTime($date);
    $formatter = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG);
    $parts = array('M', 'MMM', 'MMMM');
    $month_local_inner = array();
    foreach ($parts as $cd) {
        $formatter->setPattern($cd);
        $month_local_inner[] = $formatter->format($ts);
    }
    $month_local_inner = str_replace('.', '', $month_local_inner);//strips dot from abbreviation
    $month_local[] = $month_local_inner;
}
?>
<!-- Multiple Item Picker -->
<div id="page-content">
    <div id='resize' class="bigCharts"
         style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: calc(136px <?= $padding; ?>); ">
        <div id="menu_header">
            <?php include_once ROOT_DIR . "/inc/topmenu.php"; ?>
        </div>
        <div id="chart_header" class="<?= HEADER_CLASS ?>" style="display: grid; align-content: center; ">
            <h2><?= getTxt("chart_31days"); ?></h2>
            <div class="block2">
                <div class="inner" id="Months" style="z-index: 999 !important; position:relative">
                    <select id="coffee" name="roles" class="selectpicker show-tick"
                            title="<?= getTxt("maand"); ?>"
                            multiple="multiple"
                            data-width="fit"
                            data-selected-text-format="count > 5"
                            data-count-selected-text="{0} <?= getTxt("maand_sel"); ?>">
                        <?php for ($k = 0; $k < count($month_local); $k++) { ?>
                            <option title=<?= $month_local[$k][1] ?> value= '<?= $month_local[$k][0] ?>'><?= $month_local[$k][2] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="inner" id="Years" style="z-index: 999 !important; position:relative">
                    <select id="tea" name="roles" class="selectpicker show-tick"
                            title="<?= getTxt("jaar") ?>"
                            multiple="multiple"
                            data-width="fit"
                            data-selected-text-format="count > 3"
                            data-count-selected-text="{0} <?= getTxt("jaar_sel"); ?>">
                        <?php
                        foreach ($years as $item) {
                            echo "<option value='$item'>$item</option>";
                        }
                        ?>
                    </select>
                </div><!--.jumbotron-->
            </div>
            <script> // fills the empty dropdown on first load
                $(document).ready(function () {
                    $('.selectpicker').selectpicker('toggle');
                });
            </script>

            <script>
                var allselected;
                var allselectedtea;
                $.fn.selectpicker.Constructor.BootstrapVersion = '5';
                $('.selectpicker').selectpicker({iconBase: 'fa', tickIcon: 'fa-check', style: 'btn btn-zonphp'});
                $('.selectpicker').change(function () {
                    var selectedItem = $('#coffee').val();
                    var selectedItem2 = $('#tea').val();
                    if (Object.keys(selectedItem).length > 0) {
                        var allselected = selectedItem.toString();
                    }
                    if (Object.keys(selectedItem2).length > 0) {
                        var allselectedtea = selectedItem2.toString();
                    }
                    $.ajax({
                        type: 'POST',
                        url: '../charts/top31_chart.php',
                        data: {'allselected': allselected, 'allselectedtea': allselectedtea},
                        success: function (data) {
                            $('#top31_chart').html(data);
                        }
                    });
                });
            </script>
        </div><!--.chart_header-->
        <div id="top31_chart"
             style="width:100%; background-color: <?= $colors['color_chartbackground'] ?>;height:100%; <?= $corners; ?>">
        </div>
        <div>
            <?php
            include_once ROOT_DIR . "/inc/footer.php"
            ?>
        </div>
        <br>
    </div>
</div><!-- closing ".page-content" -->
<script>
    $(document).ready(function () {
        $("#resize").height(<?= BIG_CHART_HIGHT ?>);
    });
</script>
</body>
</html>