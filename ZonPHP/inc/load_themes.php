<?php

// forced to load or change theme
function loadTheame($params): void
{
    // load available themes
    $themesFiles = scandir(ROOT_DIR . "/themes");
    $themes = array();
    foreach ($themesFiles as $file) {
        if (strpos($file, ".theme") > 0) {
            $tempName = strtolower(substr($file, 0, -6));
            $tempTheme = parse_ini_file(ROOT_DIR . "/themes/" . $file, true);
            $themes[$tempName] = validatedTheme($tempTheme);
        }
    }
    $defaultTheme = $themes['zonphp'];
    $themeToLoad = $defaultTheme;
    $_SESSION['themes'] = $themes;
    $_SESSION['theme'] = $defaultTheme;
    $_SESSION['colors'] = $defaultTheme['colors'];

    // request to change theme but only load if not default that is already loaded
    if (isset($_GET['theme']) && themeExists($themes, $_GET['theme'])) {
        $themeToLoad = strtolower($_GET['theme']);
    } elseif (themeExists($themes, $params['userTheme'])) {
        // get default theme defined in parameters
        $themeToLoad = $params['userTheme'];
    } else {
        addCheckMessage("INFO", "Unknown userTheme: " . $params['userTheme']);
    }
    // change only if it is not default, which is already loaded
    if ($themeToLoad != "zonphp") {
        $customTheme = $themes[$themeToLoad];
        // override defaults with values from customTheme
        $_SESSION['theme'] = $themeToLoad;
        $_SESSION['colors'] = array_merge($defaultTheme['colors'], $customTheme['colors']);
    }

    // build multi dimensional array for palettes
    $color_palettes = array();
    foreach ($_SESSION['colors']['color_pal'] as $str) {
        $color_palettes[] = explode(',', $str);
    }
    $_SESSION['colors']['color_palettes'] = $color_palettes;
    unset($_GET['theme']);
}

/*********************************************************************
 * helper functions for themes
 *********************************************************************/
// Make a valid theme, at least name and empty colors array is needed
function validatedTheme(&$theme)
{
    if (!isset($theme['info'])) {
        $theme['info'] = array('name' => "undefined");
    }
    if (!isset($theme['info']['name'])) {
        $theme['info']['name'] = "undefined";
    }
    if (!isset($theme['colors'])) {
        $theme['colors'] = array();
    }
    return $theme;
}

function themeExists($themes, $themeName): bool
{
    $themeName = strtolower($themeName);
    if (isset($themes[$themeName])) {
        return true;
    } else {
        return false;
    }
}
