<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/month_chart.php";


$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$nextmonth = strftime("%Y-%m-%d", strtotime("+1 month", $chartdate));
$prevmonth = strftime("%Y-%m-%d", strtotime("-1 month", $chartdate));

?>

<?php include "menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <?php
                echo '<a class="myButton" href="month_overview.php?maand=' . $prevmonth . '"> < </a>';
                echo " " . $datum . " ";
                echo '<a class="myButton" href="month_overview.php?maand=' . $nextmonth . '"> > </a>';
                ?>
            </h2>
        </div>

        <div id="month_chart_<?php echo $inverter ?>" style="width:100%; height:100%;"></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
    </div>

    <div id="kalender">
        <?php

        echo '<table><tbody>';
        echo '<tr>';
        echo '<td width=40><a href="month_overview.php?maand=' . date("Y-m-1", strtotime("-1 year", $chartdate)) . '"><b>' .
            (strftime("%Y", strtotime("-1 year", $chartdate))) . '</b></a></td>';
        $dstartjaar = strtotime(date("Y-01-01", $chartdate));
        for ($i = 0; $i <= 11; $i++) {
            echo '<td width=50><a href="month_overview.php?maand=' . date("Y-m-1", strtotime("+" . $i . " months", $dstartjaar)) . '">' .
                strftime("%b", strtotime("+" . $i . " months", $dstartjaar)) . '</a></td>';
        }
        echo '<td width=40><a href="month_overview.php?maand=' . date("Y-m-1", strtotime("+1 year", $chartdate)) .
            '"><b>' . strftime("%Y", strtotime("+1 year", $chartdate)) . '</b></a></td>';
        echo '</tr>';
        echo '</tbody>
		</table>';
        ?>
    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <?php
                if ($datum != $txt["nodata"] . date("M-Y", $chartdate)) {
                    echo("<tr>
						<td><b>" . $txt["totaal"] . "</b></td>
						<td><b>" . number_format(array_sum($agegevens), 0, ',', '.') . "</b>kWh</td>
						<td><b>" . number_format(array_sum($agegevens) * $current_euroval, 0, ',', '.') . "</b>&euro;</td>
						<td><b>" . number_format(100 * array_sum($agegevens) / ($frefmaand * count($adatum)), 1, ',', '.') . "</b>%</td>
						<td></td>
						<td><b>" . number_format(1000 * array_sum($agegevens) / $ieffectiefkwpiek, 1, ',', '.') . "</b>kWhp</td>
						</tr>");
                }
                ?>
                <tr>
                    <td width=90><?php echo $txt["datum"]; ?></td>
                    <td width=105><?php echo $txt["behaaldkwh"]; ?></td>
                    <td width=105><?php echo $txt["behaaldeuro"]; ?></td>
                    <td width=105><?php echo $txt["procentverwacht"]; ?></td>
                    <td width=105><?php echo $txt["procentmaand"]; ?></td>
                    <td width=105><?php echo $txt["genormeerdkwhp"]; ?></td>
                </tr>
                <?php
                $iaantaldagen = cal_days_in_month(CAL_GREGORIAN, date("m", $chartdate), date("Y", $chartdate));
                if ($geengevmaand != 0) {
                    for ($i = 1; $i <= $iaantaldagen; $i++) {
                        if ($param['izonphpse'] == 0) {
                            $slinkseversiekl = "<a href='day_overview.php?dag=" . $i . date("-m-Y", $chartdate) . "'><b>" . $i . date("-m-Y", $chartdate) . "</b></a>";
                            $slinkseversie = "<a href='day_overview.php?dag=" . $i . date("-m-Y", $chartdate) . "'>" . $i . date("-m-Y", $chartdate) . "</a>";
                        } else {
                            $slinkseversiekl = "<b>" . $i . date("-m-Y", $chartdate) . "</b>";
                            $slinkseversie = "" . $i . date("-m-Y", $chartdate) . "";
                        }
                        if (!isset($agegevens[$i])) $agegevens[$i] = 0;
                        if ($agegevens[$i] == max($agegevens)) {
                            echo("<tr>
								<td>" . $slinkseversiekl . "</td>
								<td><b>" . number_format($agegevens[$i], 2, ',', '.') . "</b></td>
								<td><b>" . number_format($agegevens[$i] * $current_euroval, 2, ',', '.') . " &euro;</b></td>
								<td><b>" . number_format($agegevens[$i] / $frefmaand * 100, 1, ',', '.') . "</b></td>
								<td><b>" . number_format($agegevens[$i] / array_sum($agegevens) * 100, 1, ',', '.') . "</b></td>
								<td><b>" . number_format(1000 * $agegevens[$i] / $ieffectiefkwpiek, 2, ',', '.') . "</b></td>
								</tr>");
                        } else {
                            echo("<tr>
								<td>" . $slinkseversie . "</td>
								<td>" . number_format($agegevens[$i], 2, ',', '.') . "</td>
								<td>" . number_format($agegevens[$i] * $current_euroval, 2, ',', '.') . " &euro;</td>
								<td>" . number_format($agegevens[$i] / $frefmaand * 100, 1, ',', '.') . "</td>
								<td>" . number_format($agegevens[$i] / array_sum($agegevens) * 100, 1, ',', '.') . "</td>
								<td>" . number_format(1000 * $agegevens[$i] / $ieffectiefkwpiek, 2, ',', '.') . "</td>
								</tr>");
                        }
                    }
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