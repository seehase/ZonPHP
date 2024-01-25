<?php
global $params, $colors;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="ZonPHP,Sonne,Zon,sun PV, Photovoltaik, Datenlogger, SMA, Solar, Analyse">
    <meta name="description" content="PV Anlagen Monitoring">
    <meta name="author" content="seehase">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <?php
    if ($params['autoReload'] > 0) {
        echo '<meta http-equiv="refresh" content="' . $params['autoReload'] . '" >';
    }
    ?>
    <title><?= $params['farm']['name']; ?></title>
    <!-- use googleapis CDN -->
    <!-- jquery -->
    <link type="text/css" rel="stylesheet"
          href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/blitzer/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"
            integrity="sha512-VK2zcvntEufaimc+efOYi622VN5ZacdnufnmX7zIhCPmjhKnOi9ZDMtg1/ug5l183f19gG1/cBstPO4D8N/Img=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- bootstrap-select-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/js/bootstrap-select.min.js"></script>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">

    <!-- Datepicker -->
    <link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css'
          rel='stylesheet' type='text/css'>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js'></script>

    <!-- Language files for Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.de.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.en-GB.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.nl.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css"
          integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

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
    <link type="text/css" rel="stylesheet" href="<?= HTML_PATH ?>inc/styles/jqx.zonphp.css">

    <!-- read default styles (static) -->
    <link rel="stylesheet" type="text/css" href="<?= HTML_PATH ?>inc/styles/style.css">

    <!-- override dynamic with parameter -->
    <style>
        .ui-widget-content {
            background: <?= $colors['color_menubackground'] ?>;
        }

        .ui-menu, .ui-menu-item, .ui-widget-content, .ui-widget-content a {
            color: <?= $colors['color_menufont'] ?>;
        }

        .jqx-widget-content-zonphp {
            background: <?= $colors['color_chartbackground'] ?>;
        }

        .jqx-widget-header-zonphp {
            color: <?= $colors['color_windowfont'] ?>;
            border-color: #ffffff;
            background: <?= $colors['color_windowcolor'] ?> url(<?= HTML_PATH . $colors['color_image_windowtitle'] ?>) left center scroll repeat-x
        }

        #footer a, #id_links a {
            color: <?= $colors['color_text_link1'] ?>;
        }

        #footer a:hover, #id_links a:hover {
            color: <?= $colors['color_text_link2'] ?>;
        }

        .sensorgauge {
            color: <?= $colors['color_chart_text_subtitle'] ?>;
        }

        .jqx-gauge-label {
            fill: <?= $colors['color_chart_labels_xaxis1'] ?>;
            color: <?= $colors['color_chart_labels_xaxis1'] ?>;
        }

        .container {
            background-color: <?= $colors['color_background'] ?>;
        }

        html, body {
            background-color: <?= $colors['color_background_body'] ?>;
        }

        #headerinverter, .dropdown-toggle {
            color: <?= $colors['color_menufont'] ?>;
            font-size: 18px;

        }

        #chart_header .dropdown-toggle {
            color: <?= $colors['color_menufont'] ?>;
            color: white !important;
            font-size: 14px;
            position: static;
        }

        #chart_header {
            color: <?= $colors['color_windowfont'] ?>;
            background: <?= $colors['color_windowcolor'] ?> url(<?= HTML_PATH . $colors['color_image_windowtitle'] ?>) left center scroll repeat-x
        }
    </style>
    <script>
        $(document).ready(function () {
            $("#resize").resizable({autoHide: true});
        });
    </script>

    <?php
    // use google analytics if ID is set in paramaters
    if (strlen($params['googleTrackingId']) > 1) {
        include_once(ROOT_DIR . "/inc/analyticstracking.php");
    }
    ?>
</head>
<body>