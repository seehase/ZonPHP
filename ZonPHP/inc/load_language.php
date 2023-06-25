<?php

// php8.0 ready
$language = "en";
if (isset($default_language)) {
    $default_language = strtolower($default_language);
    if ($default_language === "de" || $default_language === "en" || $default_language === "fr" || $default_language === "nl") {
        $language = $default_language;
    }
}

if (isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
}

// set default timezone
date_default_timezone_set("UTC");

// if new language is set via URL parameter
if (isset($_GET['taal']) || (!isset($_SESSION['months']))) {
    if ($debugmode) error_log("calling load_language --> reload needed");
    // load default language
    $txt = parse_ini_file(ROOT_DIR . "/inc/language/en.ini", false);
    // than override with new language
    if (isset($_GET['taal'])) {
        $language = $_GET['taal'];
        unset($_GET['taal']);
    }
    $_SESSION['language'] = $language;
    $txt = parse_ini_file(ROOT_DIR . "/inc/language/" . $language . ".ini", false);
    $_SESSION['txt'] = $txt;

} else {
    if ($debugmode) error_log("calling load_language --> cache hit");
    if (isset($_SESSION['txt'])) {
        // take txt from session if set (normal case)
        $txt = $_SESSION['txt'];
    } else {
        // nothing set reload from scratch

        $txt = parse_ini_file(ROOT_DIR . "/inc/language/en.ini", false);
        if (isset($_SESSION['language'])) {
            $txt = parse_ini_file(ROOT_DIR . "/inc/language/" . $language . ".ini", false);
        }
        $_SESSION['txt'] = $txt;
    }
    if (isset($_SESSION['language'])) {
        $language = $_SESSION['language'];
    }
}
// date_default_timezone_set('Europe/Brussels');
if ($language == "nl") {
    $locale = 'nl-NL'; // For IntlDateFormatter
}
if ($language == "fr") {
    $locale = 'fr-FR'; // For IntlDateFormatter
}
if ($language == "de") {
    $locale = 'de-DE'; // For IntlDateFormatter
}
if ($language == "en") {
    $locale = 'en-US'; // For IntlDateFormatter
}
// preparing a localized month array
$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMMM");

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


