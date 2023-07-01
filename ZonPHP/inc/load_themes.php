<?php

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = "default";
}

$themesFiles = scandir(ROOT_DIR . "/inc/themes");
$themes = array();
foreach ($themesFiles as $file) {
    if (strpos($file, ".theme") > 0) {
        $tempName = strtolower(substr($file, 0, -6));
        $themes[$tempName] = parse_ini_file(ROOT_DIR . "/inc/themes/" . $file, true);
    }
}
$_SESSION['themes'] = $themes;
$defaultTheme = $themes['default'];
$colors = $defaultTheme['colors'];
// request to change theme
if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'];
    $customTheme = array('info' => array(), 'colors' => array());
    if (isset($_SESSION['theme']) && isset($themes[$_SESSION['theme']])) {
        $customTheme = $themes[$_SESSION['theme']];
        // override defaults with values from theme
        $colors = array_merge($defaultTheme['colors'], $customTheme['colors']);
    }
} else {
    // load user defined theme
    if (isset($themes[$params['userTheme']])) {
        $userTheme = $themes[$params['userTheme']];
        $colors = array_merge($defaultTheme['colors'], $userTheme['colors']);
    }
}

// build multi dimensional array for palettes
$color_palettes = array();
foreach ($colors['color_pal'] as $str) {
    $a = explode(',', $str);
    $color_palettes[] = $a;
}
$colors['color_palettes'] = $color_palettes;

// save colors in sesion
$_SESSION['colors'] = $colors;
