<?php

// TODO: change this!
if (!isset($_SESSION['theme'])) {
    if (isset($colors) && isset($colors['colortheme'])) {
        $_SESSION['theme'] = $colors['colortheme'];
    } else {
        $_SESSION['theme'] = "user";
    }
}

if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'];
}

$defaultColors = parse_ini_file(ROOT_DIR . "/inc/themes/default.theme", false);
$themeColors = array();

// FIXME: find better solution when par_edit is removed
// define theme name directly in parameters.php and add ".theme" and rework session handling
if ($_SESSION['theme'] == "theme1") {
    $themeColors = parse_ini_file(ROOT_DIR . "/inc/themes/darkgreyfire.theme", false);
} else if ($_SESSION['theme'] == "theme2") {
    $themeColors = parse_ini_file(ROOT_DIR . "/inc/themes/julia.theme", false);
} else if ($_SESSION['theme'] == "theme3") {
    $themeColors = parse_ini_file(ROOT_DIR . "/inc/themes/fire.theme", false);
} else if ($_SESSION['theme'] == "theme4") {
    $themeColors = parse_ini_file(ROOT_DIR . "/inc/themes/blue.theme", false);
}
// override defaults with values from theme
$colors = array_merge($defaultColors, $themeColors);

// build multi dimensional array for palettes
$color_palettes = array();
foreach ($colors['color_pal'] as $str) {
    $a = explode(',', $str);
    $color_palettes[] = $a;
}
$colors['color_palettes'] = $color_palettes;

// save colors in sesion
$_SESSION['colors'] = $colors;
