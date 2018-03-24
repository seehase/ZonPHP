<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";

include_once "inc/import_data.php";


$aoplopendkwdag[] = 0;

include_once "inc/header.php";

$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$daytext =  $txt['chart_dayoverview'];
if (isset($use_weewx) && $use_weewx==true){
    $daytext =  $txt['chart_solar_temp'];
}
?>

<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxscrollbar.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxpanel.js"></script>


<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxchart.js"></script>
<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxgauge.js"></script>
<!--<script type="text/javascript" src="inc/js/jqwidgets/jqwidgets/jqxknob.js"></script>-->

<?php include "menu.php"; ?>

<div id="page-content">

    <div id='jqxwindow_chart_gauge' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_gauge'])) echo ' display: none;'; ?> ">
        <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_gauge'] ?></div>
        <div id='sensor_gauge_id1' class="<?= CONTENT_CLASS ?>"
             style="<?= CHART_STYLE ?>; height: 373px; "></div>
    </div>

    <div id='jqxwindow_chart_weewx' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_weewx'])) echo ' display: none;'; ?> ">
        <a href="/weewx/index.html">
        <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_weewx'] . "-Experimental" ?></div>
        </a>
        <div id='sensor_weewx_id1' class="<?= CONTENT_CLASS ?>"
             style="<?= CHART_STYLE ?>; height: 373px; "></div>
    </div>

    <div id='jqxwindow_chart_solar_temp' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_solar_temp'])) echo ' display: none;'; ?> ">
        <a href="day_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $daytext . " - " . $inverter?></div>
        </a>

        <div id='day_chart_id1' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_month_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_monthoverview'])) echo ' display: none;'; ?> ">
        <a href="month_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_monthoverview'] . " - " . $inverter ?></div>
        </a>
        <div id='month_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_total_month_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_totalmonthoverview'])) echo ' display: none;'; ?> ">
        <a href="month_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_totalmonthoverview'] ?></div>
        </a>
        <div id='total_month_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_chart_indoor' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_indoor'])) echo ' display: none;'; ?> ">
        <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_indoor'] ?></div>
        <div id='sensor_chart_id1' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_year_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_yearoverview'])) echo ' display: none;'; ?> ">
        <a href="year_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_yearoverview'] . " - " . $inverter?>
            </div>
        </a>
        <div id='year_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_total_year_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_yearoverview'])) echo ' display: none;'; ?> ">
        <a href="year_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_totalyearoverview']?>
            </div>
        </a>
        <div id='total_year_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_all_years_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_allyearoverview'])) echo ' display: none;'; ?> ">
        <a href="all_years_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_allyearoverview'] . " - " . $inverter?>
            </div>
        </a>
        <div id='all_years_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_last_years_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_lastyearoverview'])) echo ' display: none;'; ?> ">
        <a href="last_years_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_lastyearoverview'] . " - " . $inverter?></div>
        </a>
        <div id='last_years_chart_id' class="<?= CONTENT_CLASS ?> " style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_week_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_weekoverview'])) echo ' display: none;'; ?> ">
        <a href="week_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_weekoverview'] . " - " . $inverter?>
            </div>
        </a>
        <div id='week_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_flop31_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_31days'])) echo ' display: none;'; ?> ">
        <a href="top31_overview.php?Max_Min=flop">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['slechtste'] . " - " . $inverter?></div>
        </a>
        <div id='top31_chart_id' class="<?= CONTENT_CLASS ?> " style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_top31_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_31days'])) echo ' display: none;'; ?> ">
        <a href="top31_overview.php?Max_Min=top">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['beste'] . " - " . $inverter?></div>
        </a>
        <div id='flop31_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_chart_all_temp' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_all_temp'])) echo ' display: none;'; ?> ">
        <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_all_temp'] ?></div>
        <div id='sensor_chart_id2' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_chart_all_humidity' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_all_humidity'])) echo ' display: none;'; ?> margin-bottom: 45px; ">
        <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_all_humidity'] ?></div>
        <div id='sensor_chart_id3' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>


    <script type="text/javascript">

        $(document).ready(function () {
            $("#jqxwindow_chart_gauge").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_indoor").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_all_temp").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_all_humidity").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_month_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_total_month_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_year_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_total_year_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_all_years_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_last_years_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_week_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_top31_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_flop31_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_solar_temp").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_weewx").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
        });


        $(function () {

            <?php if (isset($charts['chart_gauge'])) echo "
                // show values
                var container_gauge1 = $('#sensor_gauge_id1');
                $.ajax({
                    url: 'charts/sensor_gauge.php',
                    type: 'post',
                    data: {
                        'action': 'indexpage', 'sensors': '197086:1:Indoor:FFF, ' +
                        '196692:1:Cellar:FFF, ' +
                        '197190:1:Outdoor:FFF, ' +
                        '197086:3:Indoor:3B77DB, ' +
                        '196692:3:Cellar:3B77DB, ' +
                        '197190:3:Outdoor:3B77DB', 'id': 'Current'
                    },
    
                    cache: false,
                    success: function (chart) {
                        $(container_gauge1).append(chart);
    
                    },
                    error: function (xhr, desc, err) {
                        console.log(xhr + '\\n' + err);
    
                    }
                });
            " ?>

            <?php if (isset($charts['chart_weewx']) && isset($use_weewx)) echo "
                // show values
                var container_weewx1 = $('#sensor_weewx_id1');
                $.ajax({
                    url: 'charts/weewx_all_values.php',
                    type: 'post',
                    data: { 'id': 'Current'
                    },
    
                    cache: false,
                    success: function (chart) {
                        $(container_weewx1).append(chart);
    
                    },
                    error: function (xhr, desc, err) {
                        console.log(xhr + '\\n' + err);
    
                    }
                });
            " ?>

            <?php if (isset($charts['chart_solar_temp'])) echo "
            var container_day1 = $('#day_chart_id1');
            $.ajax({
                url: 'charts/day_chart.php',
                type: 'post',
                data: {'action': 'indexpage', 'inverter': '" . $inverter . "'},
                cache: false,
                success: function (chart) {
                    $(container_day1).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_indoor'])) echo "            
            var container_sensor1 = $('#sensor_chart_id1');
            $.ajax({
                url: 'charts/sensor_chart.php',
                type: 'post',
                data: {
                    'action': 'indexpage',
                    'sensors': '197086:1:Indxoor °C:ffa200,197086:3:Indoor %RH:001570',
                    'id': 'indoor',
                    'title': '" . $txt['onlinevandaag'] . "'
                },
                cache: false,
                success: function (chart) {
                    $(container_sensor1).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_all_temp'])) echo "              
            var container_sensor2 = $('#sensor_chart_id2');
            $.ajax({
                url: 'charts/sensor_chart.php',
                type: 'post',
                data: {
                    'action': 'indexpage',
                    'sensors': '197190:1:Outdoor °C:ffa200,196692:1:Cellar °C:1919B7,197086:1:Indoor °C:33cc33',
                    'id': 'all_temp'
                },
                cache: false,
                success: function (chart) {
                    $(container_sensor2).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_all_humidity'])) echo "             
            var container_sensor3 = $('#sensor_chart_id3');
            $.ajax({
                url: 'charts/sensor_chart.php',
                type: 'post',
                data: {
                    'action': 'indexpage',
                    'sensors': '197190:3:Outdoor %RH:ffa200,196692:3:Cellar %RH:1919B7,197086:3:Indoor %RH:33cc33',
                    'id': 'all_humidity'
                },
                cache: false,
                success: function (chart) {
                    $(container_sensor3).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_monthoverview'])) echo "                    
            var container_month = $('#month_chart_id');
            $.ajax({
                url: 'charts/month_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_month).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>
            <?php if (isset($charts['chart_totalmonthoverview'])) echo "                    
            var container_total_month = $('#total_month_chart_id');
            $.ajax({
                url: 'charts/month_chart.php',
                type: 'post',
                data: {'action': 'indexpage', 'type': 'all'},
                cache: false,
                success: function (chart) {
                    $(container_total_month).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_yearoverview'])) echo "            
            var container_year = $('#year_chart_id');
            $.ajax({
                url: 'charts/year_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_year).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>
            <?php if (isset($charts['chart_totalyearoverview'])) echo "            
            var container_total_year = $('#total_year_chart_id');
            $.ajax({
                url: 'charts/year_chart.php',
                type: 'post',
                data: {'action': 'indexpage', 'type': 'all'},
                cache: false,
                success: function (chart) {
                    $(container_total_year).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_allyearoverview'])) echo "            
            var container_total = $('#all_years_chart_id');
            $.ajax({
                url: 'charts/all_years_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_total).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_lastyearoverview'])) echo "                
            var container_last_years = $('#last_years_chart_id');
            $.ajax({
                url: 'charts/last_years_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_last_years).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>
            <?php if (isset($charts['chart_weekoverview'])) echo "            
            var container_week = $('#week_chart_id');
            $.ajax({
                url: 'charts/week_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_week).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>

            <?php if (isset($charts['chart_31days'])) echo "                
            var container_top = $('#top31_chart_id');
            $.ajax({
                url: 'charts/top31_chart.php',
                type: 'post',
                data: {'action': 'indexpage', 'topflop': 'flop'},
                cache: false,
                success: function (chart) {
                    $(container_top).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });

            var container_flop = $('#flop31_chart_id');
            $.ajax({
                url: 'charts/top31_chart.php',
                type: 'post',
                data: {'action': 'indexpage', 'topflop': 'top'},
                cache: false,
                success: function (chart) {
                    $(container_flop).append(chart);
                },
                error: function (xhr, desc, err) {
                    console.log(xhr + '\\n' + err);
                }
            });
" ?>
            // alert("index_ajax");

        });


    </script>


</div><!-- closing ".page-content" -->
<div id="spacer" style="float: left; height: 40px; width: 400px; display: block">
    <br />&nbsp;
</div>

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>



