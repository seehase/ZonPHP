<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/last_years_chart.php";


$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$nextyear = strftime("%Y-%m-%d", strtotime("+1 year", $chartdate));
$prevyear = strftime("%Y-%m-%d", strtotime("-1 year", $chartdate));

?>

<?php include_once "menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <?php
                echo '<a class="myButton" href="all_years_overview.php?jaar=' . $prevyear . '"> < </a>';
                echo ($param['jaar'] - 4) . " - " . $param['jaar'];
                echo '<a class="myButton" href="all_years_overview.php?jaar=' . $nextyear . '"> > </a>';
                ?>
            </h2>
        </div>

        <div id="all_years_chart_<?php echo $inverter ?>" style="width:100%; height:100%; "></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
    </div>

    <div id="tabelgeg">

        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <tr>
                    <td width=50><b><?php echo $txt["jaar"]; ?></b></td>
                    <td width=50><b><?php echo $txt["totaal"]; ?></b></td>
                    <?php
                    //echo '<pre>'.print_r($months, true).'</pre>';
                    for ($i = 1; $i <= 12; $i++) {
                        if (array_key_exists($i, $maxmaand))
                            echo '<td width=50><b>' . $months[$i] . '<br />' . number_format($maxmaand[$i], 0, ",", ".") . 'kWh</b></td>';
                        else
                            echo '<td width=50><b>' . $months[$i] . '<br />0kWh</b></td>';
                    }
                    ?>
                </tr>
                <?php
                foreach ($adatum as $ijaar => $amaandgeg) {
                    if ($aTotaaljaar[$ijaar] == max($aTotaaljaar)) {
                        echo "<tr>		
										<td><a href='year_overview.php?jaar=" . $ijaar . "-1-1'><b>" . $ijaar . "</b></a></td>
										<td><b>" . number_format($aTotaaljaar[$ijaar], 0, ",", ".") . "</b></td>";
                        for ($i = 1; $i < 13; $i++) {
                            if (empty($amaandgeg[$i]))
                                echo '<td><b>0</b></td>';
                            else
                                if ($amaandgeg[$i] == $maxmaand[$i])
                                    echo "<td><a href='month_overview.php?maand=" . $ijaar . "-" . $i . "-1'><b>" . number_format($amaandgeg[$i], 0, ",", ".") . "</b></a></td>";
                                else
                                    echo "<td><a href='month_overview.php?maand=" . $ijaar . "-" . $i . "-1'><b>" . number_format($amaandgeg[$i], 0, ",", ".") . "</b></a></td>";
                        }
                        echo "</tr>";
                    } else {
                        echo "<tr>						
										<td><a href='year_overview.php?jaar=" . $ijaar . "-1-1'>" . $ijaar . "</a></td>
										<td>" . number_format($aTotaaljaar[$ijaar], 0, ",", ".") . "</td>";
                        for ($i = 1; $i < 13; $i++) {
                            if (empty($amaandgeg[$i]))
                                echo '<td>0</td>';
                            else
                                if ($amaandgeg[$i] == $maxmaand[$i])
                                    echo "<td><a href='month_overview.php?maand=" . $ijaar . "-" . $i . "-1'><b>" . number_format($amaandgeg[$i], 0, ",", ".") . "</b></a></td>";
                                else
                                    echo '<td>' . number_format($amaandgeg[$i], 0, ",", ".") . '</td>';
                        }
                        echo "</tr>";
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