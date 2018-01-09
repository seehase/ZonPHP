<?php
//header('Content-Type: text/html; charset=UTF-8');
// make css file static for caching, define variable styles as constants
define('HEADER_CLASS', 'jqx-window-header jqx-window-header-zonphp jqx-widget-header jqx-widget-header-zonphp jqx-disableselect jqx-disableselect-zonphp jqx-rc-t jqx-rc-t-zonphp');
define('CONTENT_CLASS', 'jqx-window-content jqx-window-content-zonphp jqx-widget-content jqx-widget-content-zonphp jqx-rc-b jqx-rc-b-zonphp ');
define('WINDOW_STYLE_BIG', 'padding: 0px; background-color: inherit; border: 2px; border-color: #000; margin: 10px 3px 35px 10px; border-width: 1px; border-style: solid; border-radius: 10px;');
define('WINDOW_STYLE_CHART', 'padding: 0px; background-color: inherit; border: 2px; border-color: #000; margin: 0px 0px 0px 0px;border-width: 1px; border-style: solid; border-radius: 10px; width:100%; height:400');
define('WINDOW_STYLE', 'padding: 0px; border: 2px; border-color: #000; margin: 3px; border-width: 1px; border-style: solid; border-radius: 10px; color:#000000;');
define('CHART_STYLE', 'background-color: #' . $colors['color_chartbackground'] . ';');
define('CONTENT_STYLE', 'float: left; top: 40px; margin-bottom: 85px; margin-left:  width: 100%; height: auto; overflow: hidden; background-color:#' . $colors['color_background'] . ';');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="keywords" content="ZonPHP,Sonne,Zon,sun PV, Photovoltaik, Datenlogger, SMA, Solar, Analyse">
    <meta name="description" content="PV Anlagen Montoring">
    <meta name="author" content="slaper">
    <meta http-equiv="Cache-Control" content="no-cache">
    <?php
    echo '<meta http-equiv="refresh" content="300" >';
    ?>
    <title><?php echo $param['sNaamVoorOpWebsite']; ?></title>


    <!-- use googleapis CDN -->
    <link type="text/css" rel="stylesheet"
          href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script type="text/javascript"
            src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


    <script type="text/javascript" src="https://code.highcharts.com/highcharts.js"></script>
    <script type="text/javascript" src="https://code.highcharts.com/highcharts-more.js"></script>
    <script type="text/javascript" src="https://code.highcharts.com/modules/exporting.js"></script>
    <script type="text/javascript" src="https://code.highcharts.com/modules/solid-gauge.js"></script>


    <script language="javascript" type="text/javascript" src="inc/js/jquery.flot.resize.min.js"></script>
    <link type="text/css" rel="stylesheet" href="inc/js/jqwidgets/jqwidgets/styles/jqx.base.css">
    <link type="text/css" rel="stylesheet" href="inc/js/jqwidgets/jqwidgets/styles/jqx.zonphp.css">

    <!-- read default styles (static) -->
    <link rel="stylesheet" type="text/css" href="style.css">

    <!-- override dynamic with parameter -->
    <style type="text/css">
        .ui-widget-content {
            background: #<?php echo $colors['color_menubackground'] ?>;
        }

        .ui-menu, .ui-menu-item, .ui-widget-content, .ui-widget-content a {
            color: #<?php echo $colors['color_menufont'] ?>;
        }

        .jqx-widget-content-zonphp {
            background: #<?php echo $colors['color_chartbackground'] ?>;
        }

        .jqx-widget-header-zonphp {
            color: #<?php echo $colors['color_windowfont'] ?>;
            border-color: #ffffff;
            background: # <?php echo $colors['color_windowcolor'] ?> url(<?php echo $colors['color_image_windowtitle'] ?>) left center scroll repeat-x
        }

        #footer a, #id_about a, #id_links a, #id_install a {
            color: #<?php echo $colors['color_text_link1'] ?>;
        }

        #footer a:hover, #id_about a:hover, #id_links a:hover, #id_install a:hover {
            color: #<?php echo $colors['color_text_link2'] ?>;
        }

        .sensorgauge {
            color: #<?php echo $colors['color_chart_text_subtitle'] ?>;
        }

        .jqx-gauge-label {
            fill: #<?php echo $colors['color_chart_labels_xaxis1'] ?>;
            color: #<?php echo $colors['color_chart_labels_xaxis1'] ?>;
        }

        .container {
            background-color: #<?php echo $colors['color_background'] ?>;
        }

        html, body {
            background-color: #<?php echo $colors['color_background'] ?>;
        }

        #headerinverter {
            color: #<?php echo $colors['color_menubackground'] ?>;
        }

    </style>


    <script type="text/javascript">
        $(document).ready(function () {
            $("#resize").resizable({autoHide: true});
            $("#resize").resizable("option", "autoHide", true);
        });

        $(document).ready(function () {
            $("#toggelbutton").click(function () {
                var mydiv = $("#toggeldiv");
                var ishidden = true;

                if (mydiv.is(':hidden')) {
                    $("#toggeldiv").show();
                } else {
                    $("#toggeldiv").hide();
                }


            });
        });


    </script>

    <?php
    // use google analytics if ID is set in paramaters
    if ( isset($param['google_tracking']) && strlen ($param['google_tracking']) > 1 ) {
        include_once("analyticstracking.php");
    }
    ?>

    <?php $showflop = "#top31_chart"; ?>


</head>
<body>