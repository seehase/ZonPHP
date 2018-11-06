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

$inverter_id = $inverter;
$add_params = "";
if ((isset($_POST['type']) && ($_POST['type'] == "all")) ||
    (isset($_GET['type']) && ($_GET['type'] == "all"))) {
    $inverter_id = "all";
    $add_params = "&type=all";
}

?>


<?php include "menu.php"; ?>

<?php
    $choose_inverter_dropdown = "";
    $multiple_inverters = false;
    $choose_inverter_items = "";
    $paramstr_choose = '';
    $paramstr_day = '';
    # remove naam parameter
    if (sizeof($_GET) > 0){
        foreach ($_GET as $key => $value) {
            if ( !(($key == "naam") || ($key == "type")) ) {
                $paramstr_choose .=  $key . "=" . $value . "&";
            }
            if ( $key != "dag") {
                $paramstr_day .= $key . "=" . $value . "&";
            }
        }
    }
    if (strpos($paramstr_day, "?") == 0) {
        $paramstr_day = '?' . $paramstr_day;
    }
    if (strpos($paramstr_choose, "?") == 0) {
        $paramstr_choose = '?' . $paramstr_choose;
    }
    foreach ($sNaamSaveDatabase as $key => $sdbnaam) {
        $choose_inverter_items .= "<li><a href='" . $_SERVER['SCRIPT_NAME'] . $paramstr_choose . "naam=" . $sdbnaam .
            "' onclick=\"target='_self'\">" . $sdbnaam . "</a></li>";
    }

    if (strlen($choose_inverter_items) > 0){
        $choose_inverter_dropdown = '
                        <div style="position: absolute; z-index: 50">
        
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" style="margin-top: 2px;margin-left: 20px;">' .
            $txt['choose_inverter'] . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu"> ' .
            $choose_inverter_items .
            "<li><a href='" . $_SERVER['SCRIPT_NAME'] . $paramstr_choose . "type=all" .
            "' onclick=\"target='_self'\">" . $txt['all_inverters'] . "</a></li>" . '
                            </ul>
                        </div>
                
                ';
        $multiple_inverters = true;
    }
?>

<div id="page-content">
        <?php $myKeys = array_keys($sum_per_year); ?>

        <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px;">
            <div id="week_chart_header" class="<?= HEADER_CLASS ?>">

                <?php
                    if ($multiple_inverters) echo $choose_inverter_dropdown;
                ?>

                <h2 align="center"><?php echo $txt["totaaloverzicht"] . "&nbsp;" . min($myKeys) . " - " . max($myKeys); ?></h2>
            </div>

            <div id="total_chart_<?php echo $inverter_id ?>" style="width:100%; height:100%;"></div>
        </div>

        <div style="float: unset; margin-top: 5px;">
            <button class="btn btn-primary" id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
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