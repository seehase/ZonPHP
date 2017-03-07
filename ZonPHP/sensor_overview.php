<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/sensor_chart.php";


$id = "";
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

$nextday = strftime("%Y-%m-%d", strtotime("+1 day", $chartdate));
$prevday = strftime("%Y-%m-%d", strtotime("-1 day", $chartdate));

?>


<div id="wrapper">
    <?php include "menu.php"; ?>
    <div id="container">
        <div id="bodytext">

            <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 52px; ">
                <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
                    <?php
                    if ($dminimum < $chartdate) {
                        echo '<a class="myButton" href="sensor_overview.php?dag=' . $prevday . $urlparams . '"><</a>';
                    }
                    echo " " . $title . " - " . $datum . " ";
                    if ($dmaximum > $chartdate) {
                        echo '<a class="myButton" href="sensor_overview.php?dag=' . $nextday . $urlparams . '">></a>';
                    }
                    ?>
                    </h2> <br/><br/>
                    <?php
                    echo '<a class="myButton" href="sensor_overview.php?' . $urlparams . '"> ' . $txt["terugnaarvandaag"] . ' </a>';
                    ?>
                </div>

                <div id="sensor_chart_<?php echo $id ?>" style="width:100%; height:100%;"></div>
            </div>


            <div id="kalender">
                <?php
                $iaantaldagen = cal_days_in_month(CAL_GREGORIAN, date("m", $chartdate), date("Y", $chartdate));
                echo '<table><tbody>';
                echo '<tr>';
                echo '<td width=60><a href="sensor_overview.php?dag=' . date("Y-m-d", strtotime("-1 months", $chartdate)) . $urlparams .
                    '"><font size="-1"><b>' . strftime("%B", strtotime("-1 months", $chartdate)) . '</b></font></a></td>';
                for ($i = 1; $i <= $iaantaldagen; $i++) {
                    if (strtotime(date("Y-m-", $chartdate) . $i) >= strtotime(date("Y-m-d", $dminimum)) && strtotime(date("Y-m-", $chartdate) . $i) < $dmaximum) {
                        echo '<td width=16><font size="-5"><b>' . strftime("%a", strtotime(date("Y-m-", $chartdate) . $i)) . '</b></font><br />';
                        echo '<a href="sensor_overview.php?dag=' . date("Y-m-", $chartdate) . $i . $urlparams . '"><font size="-5">' . $i . '</font></a></td>';
                    } else {
                        echo '<td width=16><font size="-5" color="#BBBBBB"><b>' . strftime("%a", strtotime(date("Y-m-", $chartdate) . $i)) . '</b></font><br />';
                        echo '<a href="sensor_overview.php?dag=' . date("Y-m-", $chartdate) . $i . $urlparams . '"><font size="-5" color="#FF000F">' . $i . '</font></a></td>';
                    }
                }
                echo '<td width=60><a href="sensor_overview.php?dag=' . date("Y-m-d", strtotime("+1 months", $chartdate)) . $urlparams .
                    '"><font size="-1"><b>' . strftime("%B", strtotime("+1 months", $chartdate)) . '</b></font></a></td>';
                echo '</tr>';
                echo '</tbody>
		</table>';
                ?>
            </div>

            <div style="float: unset; margin-top: 5px;">
                <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
            </div>

            <div id="tabelgeg">

                <div id="toggeldiv" class="collapse">
                    <table>
                        <tbody>
                        <tr>
                            <td width=20><font size="-5"><b><?php echo $txt["uur"]; ?></b></font></td>
                            <?php
                            for ($i = 0; $i < (60 / $param['isorteren']); $i++) {
                                $auurtabel[] = $param['isorteren'] * $i;
                                echo '<td width=60><font size="-5"><b>' . $param['isorteren'] * $i . '</b></font></td>';
                            }
                            ?>
                        </tr>
                        <?php
                        if ($geengevdag != 0) {
                            $bstart = true;
                            $tabelstr = "";
                            foreach ($sensor_values as $time => $val) {
                                $min = date("i", strtotime($time));
                                $safeval = "";
                                if (isset($val) && strlen($val > 0)) {
                                    $safeval = number_format($val, 1, ",", ".");
                                }
                                if ($min != 00) {
                                    if ($bstart) {
                                        $tabelstr .= "<td><font size='-5'><b>" . date("H", strtotime($time)) . "</b></font></td>";
                                        for ($i = 1; $i <= array_search($min, $auurtabel); $i++) {
                                            $tabelstr .= "<td><font size='-1'>--</font></td>";
                                        }
                                        $bstart = false;
                                    }
                                    if ($sensor_values[$time] == max($sensor_values))
                                        $tabelstr .= "<td><font size='-1'color='#00AA00'><b>" . $safeval . "</b></font></td>";
                                    else
                                        $tabelstr .= "<td><font size='-1'>" . $safeval . "</font></td>";
                                } else {
                                    $bstart = false;
                                    echo("<tr>" . $tabelstr . "</tr>");
                                    $tabelstr = "";
                                    $tabelstr .= "<td><font size='-5'><b>" . date("H", strtotime($time)) . "</b></font></td>";
                                    if ($sensor_values[$time] == max($sensor_values))
                                        $tabelstr .= "<td><font size='-1'color='#00AA00'><b>" . $safeval . "</b></font></td>";
                                    else
                                        $tabelstr .= "<td><font size='-1'>" . $safeval . "</font></td>";
                                }
                            }
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
                $("#resize").height(<?php echo $big_chart_height ?>);
            });
        </script>


    </div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>