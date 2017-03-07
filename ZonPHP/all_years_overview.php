<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";

include_once "charts/all_years_chart.php";


$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}
?>


    <?php include "menu.php"; ?>
<div id="page-content">
        <?php $myKeys = array_keys($sum_per_year); ?>

        <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px;">
            <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
                <h2 align="center"><?php echo $txt["totaaloverzicht"] . "&nbsp;" . min($myKeys) . " - " . max($myKeys); ?></h2>
            </div>

            <div id="total_chart_<?php echo $inverter ?>" style="width:100%; height:100%;"></div>
        </div>

        <div style="float: unset; margin-top: 5px;">
            <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
        </div>


        <div id="tabelgeg">
            <div id="toggeldiv" class="collapse1">
                <table>
                    <tbody>
                    <?php
                    echo "<tr>						
						<td><b>" . $txt["totaal"] . "</b></td>";
                    if ($param['iTonendagnacht'] == 1) {
                        echo "<td><b>" . number_format(array_sum($ajaarverbruikdag), 0, ',', '.') . "</b>kWh</td>";
                        echo "<td><b>" . number_format(array_sum($ajaarverbruiknacht), 0, ',', '.') . "</b>kWh</td>";
                    }
                    echo "
						<td><b>" . number_format(array_sum($sum_per_year), 0, ",", ".") . "</b>kWh</td>						
						<td><b>" . number_format($fsomeuro, 0, ",", ".") . "</b>&euro;</td>
						<td></td>
						<td><b>" . number_format(1000 * array_sum($sum_per_year) / $ieffectiefkwpiek, 0, ",", ".") . "</b>kWhp</td>						
					</tr>";
                    ?>
                    <tr>
                        <td width=70><?php echo $txt["datum"]; ?></td>
                        <?php
                        if ($param['iTonendagnacht'] == 1) {
                            echo "<td width=70>" . $txt["dagverbruik"] . " [kWh]</td>";
                            echo "<td width=70>" . $txt["nachtverbruik"] . " [kWh]</td>";
                        }
                        ?>
                        <td width=150><?php echo $txt["absoluteopbrengstkwh"]; ?></td>
                        <td width=150><?php echo $txt["behaaldeuro"]; ?></td>
                        <td width=150><?php echo $txt["procentabsoluteopbrengst"]; ?></td>
                        <td width=150><?php echo $txt["genormeerdeopbrengstkwhp"]; ?></td>
                    </tr>
                    <?php
                    if ($datum != "Geen data") {
                        foreach ($sum_per_year as $ijaar => $fkw) {
                            if ($sum_per_year[$ijaar] == max($sum_per_year)) {
                                echo "<tr>						
							<td><a href='year_overview.php?jaar=" . $ijaar . "-1-1'><b>" . $ijaar . "</b></a></td>";
                                if ($param['iTonendagnacht'] == 1) {
                                    if (!isset($ajaarverbruikdag[$ijaar])) $ajaarverbruikdag[$ijaar] = 0;
                                    if (!isset($ajaarverbruiknacht[$ijaar])) $ajaarverbruiknacht[$ijaar] = 0;
                                    echo "<td><b>" . $ajaarverbruikdag[$ijaar] . "</b></td>";
                                    echo "<td><b>" . $ajaarverbruiknacht[$ijaar] . "</b></td>";
                                }
                                echo "	<td><b>" . number_format($fkw, 0, ",", ".") . "</b></td>						
							<td><b>" . number_format($fkw * $year_euro[$ijaar], 0, ",", ".") . " &euro;</b></td>
							<td><b>" . number_format(100 * $fkw / $frefjaar, 2, ",", ".") . "</b></td>						
							<td><b>" . number_format(1000 * $fkw / $ieffectiefkwpiek, 0, ",", ".") . "</b></td>						
						</tr>";
                            } else {
                                echo "<tr>						
							<td><a href='year_overview.php?jaar=" . $ijaar . "-1-1'>" . $ijaar . "</a></td>";
                                if ($param['iTonendagnacht'] == 1) {
                                    if (!isset($ajaarverbruikdag[$ijaar])) $ajaarverbruikdag[$ijaar] = 0;
                                    if (!isset($ajaarverbruiknacht[$ijaar])) $ajaarverbruiknacht[$ijaar] = 0;
                                    echo "<td>" . $ajaarverbruikdag[$ijaar] . "</td>";
                                    echo "<td>" . $ajaarverbruiknacht[$ijaar] . "</td>";
                                }
                                echo "	<td>" . number_format($fkw, 0, ",", ".") . "</td>						
							<td>" . number_format($fkw * $year_euro[$ijaar], 0, ",", ".") . " &euro;</td>
							<td>" . number_format(100 * $fkw / $frefjaar, 2, ",", ".") . "</td>						
							<td>" . number_format(1000 * $fkw / $ieffectiefkwpiek, 0, ",", ".") . "</td>						
						</tr>";
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
            $("#resize ").height(500);
        });
    </script>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->
<?php include_once "inc/footer.php"; ?>

</body>
</html>