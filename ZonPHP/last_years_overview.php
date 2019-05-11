<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/last_years_chart.php";

$inverter = $sNaamSaveDatabase[0];
if (isset( $_GET['naam'])) {
    $inverter =  $_GET['naam'];
}

$nextyear = strftime("%Y-%m-%d", strtotime("+1 year", $chartdate));
$prevyear = strftime("%Y-%m-%d", strtotime("-1 year", $chartdate));

?>

<?php include_once "menu.php"; ?>

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
        
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" style="margin-top: 15px;margin-left: 20px;">' .
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

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="chart_header" class="<?= HEADER_CLASS ?>">

            <?php
                if ($multiple_inverters) echo $choose_inverter_dropdown;
            ?>

            <h2 align="center">
                <?php
                echo " " . $inverter . "  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  ";
                echo '<a class="btn btn-primary" href="all_years_overview.php' . $paramstr_day .'jaar=' . $prevyear . '"> < </a>';
                echo " " .  ($param['jaar'] - 4) . " - " . $param['jaar'] . " ";
                echo '<a class="btn btn-primary" href="all_years_overview.php' . $paramstr_day .'jaar=' . $nextyear . '"> > </a>';
                ?>
            </h2>
        </div>

        <div id="all_years_chart_<?php echo $inverter ?>" style="width:100%; height:100%; "></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button class="btn btn-primary" id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
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