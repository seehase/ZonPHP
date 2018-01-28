<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/year_chart.php";


$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$nextyearvisible = false;
$nextyear = strtotime("+1 year", $chartdate);
$nextyearstring = strftime("%Y-01-01", strtotime("+1 year", $chartdate));
if ($nextyear <= $date_maximum) {
    $nextyearvisible = true;
}
$prevyear = strtotime("-1 year", $chartdate);
$prevyearstring = strftime("%Y-%m-%d", strtotime("-1 year", $chartdate));

$date_minimum1=strtotime("-1 year", $date_minimum);

if ($prevyear >= $date_minimum1) {
    $prevyearvisible = true;
}else{
	$prevyearvisible = false;
}
$chartyeardatestring = strftime("%Y-01-01", strtotime("+0 year", $date_maximum));
#$prevyear = strftime("%Y-%m-%d", strtotime("-1 year", $chartdate));

?>

<?php include "menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <?php
				if ($prevyearvisible) {
                echo '<a class="myButton" href="year_overview.php?jaar=' . $prevyearstring . '"> < </a>';
				}
                echo $txt["jaar"]." " . $datum . " ";
				if ($nextyearvisible) {
                echo '<a class="myButton" href="year_overview.php?jaar=' . $nextyearstring . '"> > </a>';
				}
                ?>
            </h2>
        </div>

        <div id="year_chart_<?php echo $inverter ?>" style=":width100%; height:100%;"></div>
    </div>
	
    <div style="float: unset; margin-top: 5px;">
        <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
		<a href="<?php echo "year_overview.php?jaar=".$chartyeardatestring ?>" target="_self"><button><?php echo $txt['back_to_today'] ?></button>
		</a>
    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <?php
                echo "<tr>						
								<td><b>" . $txt["totaal"] . "</b></td>
								<td></td>";
                if ($param['iTonendagnacht'] == 1) {
                    echo "<td><b>" . number_format(array_sum($ajaarverbruikdag), 0, ',', '.') . "</b>kWh</td>";
                    echo "<td><b>" . number_format(array_sum($ajaarverbruiknacht), 0, ',', '.') . "</b>kWh</td>";
                }
                echo "<td><b>" . number_format(array_sum($frefmaand), 0, ',', '.') . "</b>kWh</td>						
						<td><b>" . number_format(array_sum($agegevens), 0, ',', '.') . "</b>kWh</td>
						<td><b>" . number_format(array_sum($agegevens) * $current_euroval, 0, ',', '.') . "</b>&euro;</td>
						<td><b>" . number_format(100 * array_sum($agegevens) / array_sum($frefmaand), 0, ',', '.') . "</b>%</td>						
						<td><b>" . number_format(1000 * array_sum($agegevens) / $ieffectiefkwpiek, 0, ',', '.') . "</b>kWhp</td>						
					</tr>";
                ?>
                <tr>
                    <td width=70><?php echo $txt["datum"]; ?></td>
                    <td width=70><?php echo $txt["maxjaar"]; ?></td>
                    <?php
                    if ($param['iTonendagnacht'] == 1) {
                        echo "<td width=70>" . $txt["dagverbruik"] . " [kWh]</td>";
                        echo "<td width=70>" . $txt["nachtverbruik"] . " [kWh]</td>";
                    }
                    ?>
                    <td width=105><?php echo $txt["verwachteopbrengstkwh"]; ?></td>
                    <td width=105><?php echo $txt["absoluteopbrengstkwh"]; ?></td>
                    <td width=105><?php echo $txt["behaaldeuro"]; ?></td>
                    <td width=105><?php echo $txt["procentabsoluteopbrengst"]; ?></td>
                    <td width=105><?php echo $txt["genormeerdeopbrengstkwhp"]; ?></td>
                </tr>
                <?php
                if ($datum != $txt["nodata"] . date("Y", $chartdate)) {
                    for ($i = 1; $i <= 12; $i++) {
                        if (array_key_exists($i, $agegevens)) {
                            if ($agegevens[$i] == max($agegevens)) {
                                echo "<tr>						
											<td><a href='month_overview.php?maand=" . date("Y-", $chartdate) . $i . "-1'><b>" . $months[$i] . "</b></a></td>
											<td><a href='month_overview.php?jaar=" . $maxmaand_jaar[$i] . "-" . $i . "-1'><b>" . $maxmaand_jaar[$i] . "</b></a></td>";
                                if ($param['iTonendagnacht'] == 1) {
                                    if (!isset($ajaarverbruikdag[$i])) $ajaarverbruikdag[$i] = 0;
                                    if (!isset($ajaarverbruiknacht[$i])) $ajaarverbruiknacht[$i] = 0;
                                    echo "<td><b>" . $ajaarverbruikdag[$i] . "</b></td>";
                                    echo "<td><b>" . $ajaarverbruiknacht[$i] . "</b></td>";
                                }
                                echo "<td><b>" . $frefmaand[$i] . "</b></td>						
									<td><b>" . number_format($agegevens[$i], 2, ',', '.') . "</b></td>
									<td><b>" . number_format($agegevens[$i] * $current_euroval, 2, ',', '.') . " &euro;</b></td>
									<td><b>" . number_format(100 * $agegevens[$i] / $frefmaand[$i], 2, ',', '.') . "</b></td>						
									<td><b>" . number_format(1000 * $agegevens[$i] / $ieffectiefkwpiek, 2, ',', '.') . "</b></td>						
								</tr>";
                            } else {
                                echo "<tr>						
											<td><a href='month_overview.php?maand=" . date("Y-", $chartdate) . $i . "-1'>" . $months[$i] . "</a></td>
											<td><a href='month_overview.php?jaar=" . $maxmaand_jaar[$i] . "-" . $i . "-1'>" . $maxmaand_jaar[$i] . "</a></td>";
                                if ($param['iTonendagnacht'] == 1) {
                                    if (!isset($ajaarverbruikdag[$i])) $ajaarverbruikdag[$i] = 0;
                                    if (!isset($ajaarverbruiknacht[$i])) $ajaarverbruiknacht[$i] = 0;
                                    echo "<td>" . $ajaarverbruikdag[$i] . "</td>";
                                    echo "<td>" . $ajaarverbruiknacht[$i] . "</td>";
                                }
                                echo "<td>" . $frefmaand[$i] . "</td>						
									<td>" . number_format($agegevens[$i], 2, ',', '.') . "</td>
									<td>" . number_format($agegevens[$i] * $current_euroval, 2, ',', '.') . " &euro;</td>
									<td>" . number_format(100 * $agegevens[$i] / $frefmaand[$i], 2, ',', '.') . "</td>						
									<td>" . number_format(1000 * $agegevens[$i] / $ieffectiefkwpiek, 2, ',', '.') . "</td>						
								</tr>";
                            }
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