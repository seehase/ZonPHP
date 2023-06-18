<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" >
    <meta name="keywords" content="ZonPHP,Sonne,Zon,sun PV, Photovoltaik, Datenlogger, SMA, Solar, Analyse">
    <meta name="description" content="PV Anlagen Monitoring">
    <meta name="author" content="seehase">
<?php
//header('Content-Type: text/html; charset=UTF-8');
// make css file static for caching, define variable styles as constants
define('HEADER_CLASS', 'jqx-window-header jqx-window-header-zonphp jqx-widget-header jqx-widget-header-zonphp jqx-disableselect jqx-disableselect-zonphp jqx-rc-t jqx-rc-t-zonphp');
define('HEADER_INDEX_CLASS', 'jqx-window-header jqx-window-header-index-zonphp jqx-widget-header jqx-widget-header-zonphp jqx-disableselect jqx-disableselect-zonphp jqx-rc-t jqx-rc-t-zonphp');
define('CONTENT_CLASS', 'jqx-window-content jqx-window-content-zonphp jqx-widget-content jqx-widget-content-zonphp jqx-rc-b jqx-rc-b-zonphp ');
define('WINDOW_STYLE_BIG', 'padding: 0px; background-color: inherit; border: 2px; border-color: #000; margin: 10px 3px 35px 10px; border-width: 1px; border-style: solid; border-radius: 10px;');
define('WINDOW_STYLE_CHART', 'padding: 0px; background-color: inherit; border: 2px; border-color: #000; margin: 0px 0px 0px 0px;border-width: 1px; border-style: solid; border-radius: 10px; width:100%; height:400px');
define('WINDOW_STYLE', 'padding: 0px; border: 2px; border-color: #000; margin: 3px; border-width: 1px; border-style: solid; border-radius: 10px; color:#000000;');
define('CHART_STYLE', 'background-color: #' . $colors['color_chartbackground'] . ';');
define('CONTENT_STYLE', 'float: left; top: 40px; margin-bottom: 85px; margin-left:  width: 100%; height: auto; overflow: hidden; background-color:#' . $colors['color_background'] . ';');
?>
    <?php
    if (isset($param['autorefresh'])) {
        $autorefresh = intval($param['autorefresh']);
    } else {
        $autorefresh = 300;
    }
    if ($autorefresh > 0) {
        echo '<meta http-equiv="refresh" content="' . $autorefresh . '" >';
    }
    ?>
    <title><?php echo $param['sNaamVoorOpWebsite']; ?></title>
    <!-- use googleapis CDN -->
    <!-- jquery -->
	<link type="text/css" rel="stylesheet"
	href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/blitzer/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

	<!-- bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
	
	<!-- Datepicker -->
	<link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css' rel='stylesheet' type='text/css'>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js'></script>
	
	<!-- Language files for Datepicker -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.de.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.en-GB.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.nl.min.js"></script>
	
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer" >

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>

    <!-- moment (used by Highcharts for UTC to local time) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.43/moment-timezone-with-data-1970-2030.js"></script>

    <!-- jqwidgets -->
    <link type="text/css" rel="stylesheet" href="https://jqwidgets.com/public/jqwidgets/styles/jqx.base.css">
    <link type="text/css" rel="stylesheet" href="<?php echo HTML_PATH ?>inc/styles/jqx.zonphp.css">
	
	<!-- read default styles (static) -->
    <link rel="stylesheet" type="text/css" href="<?php echo HTML_PATH ?>inc/styles/style.css">

    <!-- override dynamic with parameter -->
    <style>
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
            background: #<?php echo $colors['color_windowcolor'] ?> url(<?php echo HTML_PATH . $colors['color_image_windowtitle'] ?>) left center scroll repeat-x
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
            font-size: 18px;
        }

    </style>
    <script>
        $(document).ready(function () {
            $("#resize").resizable({autoHide: true});
        });
    </script>
    <?php
    // use google analytics if ID is set in paramaters
    if (isset($param['google_tracking']) && strlen($param['google_tracking']) > 1) {
        include_once(ROOT_DIR . "/inc/analyticstracking.php");
    }
    ?>
</head>
<body>
