<?php
/**
 * common functions
 */
function getTxt($key)
{
    if (isset($_SESSION["txt"]) && isset($_SESSION["txt"][$key])) {
        return $_SESSION["txt"][$key];
    } else {
        return "undefined key: " . $key;
    }
}

function isActive($language)
{
    if (in_array(strtolower($language), LANGUAGES)) {
        return true;
    } else {
        return false;
    };
}


function checkChangedConfigFiles()
{
    // check parameter.php
    $paramsFileDate = filemtime(ROOT_DIR . "/parameters.php");
    if (!isset($_SESSION['paramsFileDate'])) {
        $_SESSION['paramsFileDate'] = $paramsFileDate;
        return true;
    } else {
        if ($_SESSION['paramsFileDate'] < $paramsFileDate) {
            $_SESSION['paramsFileDate'] = $paramsFileDate;
            unset($_SESSION['params']);
            unset($_SESSION['txt']);
            unset($_SESSION['colors']);
            return true;
        }
    }

    // check language files
    $languageFiles = scandir(ROOT_DIR . "/inc/language");
    $hash = "";
    foreach ($languageFiles as $file) {
        $hash .= filemtime(ROOT_DIR . "/inc/language/" . $file);
    }
    $hash = md5($hash);
    if (!isset($_SESSION['languageFilesHash'])) {
        $_SESSION['languageFilesHash'] = $hash;
    } else {
        if ($_SESSION['languageFilesHash'] != $hash) {
            $_SESSION['languageFilesHash'] = $hash;
            unset($_SESSION['txt']);
            return true;
        }
    }

    // check themes files
    $themeFiles = scandir(ROOT_DIR . "/themes");
    $hash = "";
    foreach ($themeFiles as $file) {
        $hash .= filemtime(ROOT_DIR . "/themes/" . $file);
    }
    $hash = md5($hash);
    if (!isset($_SESSION['themeFilesHash'])) {
        $_SESSION['themeFilesHash'] = $hash;
    } else {
        if ($_SESSION['themeFilesHash'] != $hash) {
            $_SESSION['themeFilesHash'] = $hash;
            unset($_SESSION['colors']);
            return true;
        }
    }
    return false;
}