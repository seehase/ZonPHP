<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/day_chart.php";


$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$inverter_id = $inverter;
$add_params = "";
if ((isset($_POST['type']) && ($_POST['type'] == "all")) ||
    (isset($_GET['type']) && ($_GET['type'] == "all"))) {
    $inverter_id = "all";
    $add_params = "&type=all";
}


$nextdatevisible = false;
$nextdate = strtotime("+1 day", $chartdate);
$nextdatestring = strftime("%Y-%m-%d", $nextdate);
if ($nextdate <= $date_maximum) {
    $nextdatevisible = true;
}

$prevdatevisible = false;
$prevdate = strtotime("-1 day", $chartdate);
$prevdatestring = strftime("%Y-%m-%d", $prevdate);
if ($prevdate >= $date_minimum) {
    $prevdatevisible = true;
}
$chartdaydatestring = strftime("%Y-%m-%d", strtotime("+0 day", $date_maximum));


?>

<?php include_once "menu.php"; ?>

<script type="text/javascript">

    $(function () {

        $("#startdate, #enddate").datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: "button",
            gotoCurrent: false,
            showButtonPanel: true,
            buttonImage: "inc/image/calendar.gif",
            buttonImageOnly: true,
            buttonText: "Select date",
            onSelect: function () {
                var url = "day_overview.php?dag=" + $("#startdate").val();
                window.open(url, "_self");
            },
        });
        var _gotoToday = jQuery.datepicker._gotoToday;
        jQuery.datepicker._gotoToday = function (a) {
            var target = jQuery(a);
            var inst = this._getInst(target[0]);
            _gotoToday.call(this, a);
            jQuery.datepicker._selectDate(a, jQuery.datepicker._formatDate(inst, inst.selectedDay, inst.selectedMonth, inst.selectedYear));
        };
    });
</script>

<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 72px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <?php
                if ($prevdatevisible) {
                    echo '<a class="myButton" href="day_overview.php?dag=' . $prevdatestring . $add_params . '"> < </a>';
                }
                echo " " . $datum . " " . '  <input type="hidden" id="startdate" value="' . strftime("%d-%m-%Y", time()) . '" readonly>  ';
                if ($nextdatevisible) {
                    echo '<a class="myButton" href="day_overview.php?dag=' . $nextdatestring . $add_params . '"> > </a>';
                }
                ?>
            </h2>

            <?php echo("<b>" . $txt["actueel"] . " " . date("H:i", $tlaatstetijd) . " : " . number_format(end($agegevens), 0, ',', '.') . "W="
                . number_format(100 * end($agegevens) / $ieffectiefkwpiek, 0, ',', '.') . "%</b>"); ?>
            <?php echo("<b>" . $txt["totaal"] . ": </b>" . number_format(end($aoplopendkwdag), 2, ',', '.') . "kWh="
                . number_format(end($aoplopendkwdag) / ($ieffectiefkwpiek / 1000), 1, ',', '.') . "kWhp="
                . number_format(100 * end($aoplopendkwdag) / $frefmaand, 0, ',', '.') . "%"); ?>
            <?php echo("<b>" . $txt["max"] . ": </b>" . number_format(max($agegevens), 0, ",", ".") . "W="
                . number_format(100 * max($agegevens) / $ieffectiefkwpiek, 0, ",", ".") . "%"); ?>
        </div>

        <div id="mycontainer_<?php echo $inverter_id ?>" style="width:100%; height:100%;"></div>

    </div>

    <div style="float: unset; margin-top: 5px;">
        <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
        <a href="<?php echo "day_overview.php?jaar=".$chartdaydatestring ?>" target="_self"><button><?php echo $txt['back_to_today'] ?></button>
        </a>
    </div>

    <div id="kalender">
        <?php
        $iaantaldagen = cal_days_in_month(CAL_GREGORIAN, date("m", $chartdate), date("Y", $chartdate));
        echo '<table><tbody>';
        echo '<tr>';
        echo '<td width=60><a href="day_overview.php?dag=' . date("Y-m-d", strtotime("-1 months", $chartdate)) . '"><b>' . strftime("%B", strtotime("-1 months", $chartdate)) . '</b></a></td>';
        $maxdayreached = false;
        for ($i = 1; $i <= $iaantaldagen; $i++) {

            $currentdatestring = date("Y-m-", $chartdate) . $i;
            $currentdate = strtotime($currentdatestring);
            if ($currentdate <= $date_maximum) {
                echo '<td width=25><b>' . strftime("%a", $currentdate) . '</b><br />';
                echo '<a href="day_overview.php?dag=' . date("Y-m-", $chartdate) . $i . '">' . $i . '</a></td>';
            } else {
                echo '<td width=25><b>' . strftime("%a", $currentdate) . '</b><br />';
                echo '<span style="color: #C00000;"> ' . $i . '</span></td>';
                $maxdayreached = true;
            }

        }
        if ($maxdayreached) {
            echo '<td width=60><span style="color: #C00000;"> <b>' . strftime("%B", strtotime("+1 months", $chartdate)) . '</b></span></td>';
        } else {
            echo '<td width=60><a href="day_overview.php?dag=' . date("Y-m-d", strtotime("+1 months", $chartdate)) . '"><b>' . strftime("%B", strtotime("+1 months", $chartdate)) . '</b></a></td>';
        }

        echo '</tr>';
        echo '</tbody>
		</table>';
        ?>
    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <tr>
                    <td width=20><b><?php echo $txt["uur"]; ?></b></td>
                    <?php
                    for ($i = 0; $i < (60 / $param['isorteren']); $i++) {
                        $auurtabel[] = $param['isorteren'] * $i;
                        echo '<td width=60><b>' . $param['isorteren'] * $i . '</b></td>';
                    }
                    //echo "<pre>".print_r($auurtabel,true)."</pre>";
                    ?>
                </tr>
                <?php
                if ($geengevdag != 0) {
                    $bstart = true;
                    $tabelstr = "";
                    foreach ($agegevens as $tuur => $fw) {
                        $min = date("i", strtotime($tuur));
                        if ($min != 00) {
                            if ($bstart) {
                                $tabelstr .= "<td><b>" . date("H", strtotime($tuur)) . "</b></td>";
                                for ($i = 1; $i <= array_search($min, $auurtabel); $i++) {
                                    $tabelstr .= "<td>--</td>";
                                }
                                $bstart = false;
                            }
                            if ($agegevens[$tuur] == max($agegevens))
                                $tabelstr .= "<td><b>" . number_format($fw, 0, ",", ".") . "</b></td>";
                            else
                                $tabelstr .= "<td>" . number_format($fw, 0, ",", ".") . "</td>";
                        } else {
                            $bstart = false;
                            if ($tabelstr == "") $tabelstr = "<td></td>";
                            echo("<tr>" . $tabelstr . "</tr>");
                            $tabelstr = "";
                            $tabelstr .= "<td><b>" . date("H", strtotime($tuur)) . "</b></td>";
                            if ($agegevens[$tuur] == max($agegevens))
                                $tabelstr .= "<td><b>" . number_format($fw, 0, ",", ".") . "</b></td>";
                            else
                                $tabelstr .= "<td>" . number_format($fw, 0, ",", ".") . "</td>";
                        }
                    }
                    if ($tabelstr == "") $tabelstr = "<td></td>";
                    echo("<tr>" . $tabelstr . "</tr>");
                }
                ?>
                </tbody>
            </table>
        </div>
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