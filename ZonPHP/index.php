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


    <?php

    if (isset($charts['chart_totaldayoverview'])) {
        foreach ($sNaamSaveDatabase as $key => $sdbnaam) {
            $out = '<div id="jqxwindow_chart_dayoverview_' . $sdbnaam . '" class="smallCharts" 
         style="' . WINDOW_STYLE;
            if (!isset($charts['chart_dayoverview'])) $out = $out . ' display: none;';
            $out = $out . '" ';
            $out = $out . '>' .
                ' 
        <a href="day_overview.php?naam=' . $sdbnaam . '">' .
                '    
             <div class="' . HEADER_CLASS . '">' . $daytext . " - " . $sdbnaam . '</div> 
        </a>
        <div id="day_chart_id_' . $sdbnaam . '" class="' . CONTENT_CLASS . '" style="' . CHART_STYLE . '"></div>
        </div>
                ';
            echo $out;
        }
    } else
    {
        $out = '<div id="jqxwindow_chart_dayoverview_' . $inverter . '" class="smallCharts" 
         style="' . WINDOW_STYLE;
        if (!isset($charts['chart_dayoverview'])) $out = $out . ' display: none;';
        $out = $out . '" ';
        $out = $out . '>' .
            ' 
        <a href="day_overview.php">' .
            '    
             <div class="' . HEADER_CLASS . '">' . $daytext . " - " . $inverter . '</div> 
        </a>
        <div id="day_chart_id_' . $inverter . '" class="' . CONTENT_CLASS . '" style="' . CHART_STYLE . '"></div>
        </div>
                ';
        echo $out;
    }
    ?>


    <div id='jqxwindow_chart_totaldayoverview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_totaldayoverview'])) echo ' display: none;'; ?> ">
        <a href="day_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $daytext . " - " . $txt['all_inverters']?></div>
        </a>

        <div id='totalday_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_month_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_monthoverview'])) echo ' display: none;'; ?> ">
        <a href="month_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_monthoverview'] ?></div>
        </a>
        <div id='month_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>


    <div id='jqxwindow_year_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_yearoverview'])) echo ' display: none;'; ?> ">
        <a href="year_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_yearoverview'] ?>
            </div>
        
        <div id='year_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>


    <div id='jqxwindow_all_years_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_allyearoverview'])) echo ' display: none;'; ?> ">
        <a href="all_years_overview.php">
            <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_allyearoverview'] ?>
            </div>
        
        <div id='all_years_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_last_years_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_lastyearoverview'])) echo ' display: none;'; ?> ">
       
            <div style="cursor:pointer" onclick="location.href='last_years_overview.php'" class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_lastyearoverview'] . " - " . $inverter?></div>
       
        <div id='last_years_chart_id' class="<?= CONTENT_CLASS ?> " style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_week_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_weekoverview'])) echo ' display: none;'; ?> ">
        
            <div style="cursor:pointer" onclick="location.href='week_overview.php'" class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_weekoverview'] . " - " . $inverter?>
            </div>
        
        <div id='week_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_flop31_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_31days'])) echo ' display: none;'; ?> ">
        
            <div style="cursor:pointer" onclick="location.href='top31_overview.php?Max_Min=flop'" class="<?= HEADER_CLASS ?>"><?php echo $txt['slechtste'] . " - " . $inverter?></div>
       
        <div id='top31_chart_id' class="<?= CONTENT_CLASS ?> " style="<?= CHART_STYLE ?>"></div>
    </div>
    <div id='jqxwindow_top31_overview' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_31days'])) echo ' display: none;'; ?> ">
        
            <div style="cursor:pointer" onclick="location.href='top31_overview.php?Max_Min=top'" class="<?= HEADER_CLASS ?>"><?php echo $txt['beste'] . " - " . $inverter?></div>
        
        <div id='flop31_chart_id' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
    </div>

    <div id='jqxwindow_chart_indoor' class="smallCharts"
         style="<?= WINDOW_STYLE ?> <?php if (!isset($charts['chart_indoor'])) echo ' display: none;'; ?> ">
        <div class="<?= HEADER_CLASS ?>"><?php echo $txt['chart_indoor'] ?></div>
        <div id='sensor_chart_id1' class="<?= CONTENT_CLASS ?>" style="<?= CHART_STYLE ?>"></div>
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
            $("#jqxwindow_year_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_all_years_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_last_years_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_week_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_top31_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_flop31_overview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_totaldayoverview").jqxPanel({height: 410, width: 440, theme: 'zonphp'});
            $("#jqxwindow_chart_weewx").jqxPanel({height: 410, width: 440, theme: 'zonphp'});

            <?php
            if (isset($charts['chart_totaldayoverview'])) {
                foreach ($sNaamSaveDatabase as $key => $sdbnaam) {
                    echo '$("#jqxwindow_chart_dayoverview_' . $sdbnaam . '").jqxPanel({height: 410, width: 440, theme: "zonphp"});';
                }
            } else {
                echo '$("#jqxwindow_chart_dayoverview_' . $inverter . '").jqxPanel({height: 410, width: 440, theme: "zonphp"});';
            }
            ?>


        });


        $(function () {

            <?php if (isset($charts['chart_gauge'])) echo "
                // show values
                var container_gauge1 = $('#sensor_gauge_id1');
                $.ajax({
                    url: 'charts/sensor_gauge.php',
                    type: 'post',
                    data: {
                        'action': 'indexpage', 'sensors': '197086:1:Sleep:FFF, ' +
                        '196692:1:Cellar:FFF, ' +
                        '18974:1:Loft:FFF, ' +
                        '197086:3:Sleep:3B77DB, ' +
                        '196692:3:Cellar:3B77DB, ' +
                        '18974:3:Loft:3B77DB', 'id': 'Current'
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

            <?php if (isset($charts['chart_dayoverview'])) {
            if (isset($charts['chart_totaldayoverview'])) {
                foreach ($sNaamSaveDatabase as $key => $sdbnaam) {
                    echo "
                        var container_day_" . $sdbnaam . " = $('#day_chart_id_" . $sdbnaam . "');
                        $.ajax({
                            url: 'charts/day_chart.php',
                            type: 'post',
                            data: {'action': 'indexpage', 'inverter': '" . $sdbnaam . "'},
                            cache: false,
                            success: function (chart) {
                                $(container_day_" . $sdbnaam . ").append(chart);
                            },
                            error: function (xhr, desc, err) {
                                console.log(xhr + '\\n' + err);
                            }
                        }); ";
                }
            } else {
                echo "
                        var container_day_" . $inverter . " = $('#day_chart_id_" . $inverter . "');
                        $.ajax({
                            url: 'charts/day_chart.php',
                            type: 'post',
                            data: {'action': 'indexpage', 'inverter': '" . $inverter . "'},
                            cache: false,
                            success: function (chart) {
                                $(container_day_" . $inverter . ").append(chart);
                            },
                            error: function (xhr, desc, err) {
                                console.log(xhr + '\\n' + err);
                            }
                        }); ";
                }
            }
            ?>

            <?php if (isset($charts['chart_totaldayoverview'])) echo "
            var container_totalday = $('#totalday_chart_id');
            $.ajax({
                url: 'charts/day_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_totalday).append(chart);
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
                    'sensors': '197086:1:Sleep °C:ffa200,197086:3:Sleep %RH:001570',
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
                    'sensors': '197190:1:Outdoor °C:ffa200,196692:1:Cellar °C:1919B7,197086:1:Sleep °C:33cc33, 18974:1:Loft °C:FA58F4',
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
                    'sensors': '197190:3:Outdoor %RH:ffa200,196692:3:Cellar %RH:1919B7,197086:3:Sleep %RH:33cc33, 18974:3:Loft %RH:FA58F4',
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

            <?php if (isset($charts['chart_allyearoverview'])) echo "            
            var container_all_years = $('#all_years_chart_id');
            $.ajax({
                url: 'charts/all_years_chart.php',
                type: 'post',
                data: {'action': 'indexpage'},
                cache: false,
                success: function (chart) {
                    $(container_all_years).append(chart);
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