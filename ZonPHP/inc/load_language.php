<?php
// php8.0 ready
$taal = "en";
if (isset($default_language)) {
    $default_language = strtolower($default_language);
    if ($default_language === "de" || $default_language === "en" || $default_language === "fr" || $default_language === "nl") {
        $taal = $default_language;
    }
}

if (isset($_SESSION['sestaal'])) {
    $taal = $_SESSION['sestaal'];
}

if (!isset($use_utf8)) {
    $use_utf8 = false;
}
// set default timezone
date_default_timezone_set("UTC");

// if new language is set via URL parameter
if (isset($_GET['taal']) || (!isset($_SESSION['months']))) {
    if ($debugmode) error_log("calling load_language --> reload needed");
    // load default language
    include ROOT_DIR . "/inc/language/en.php";
    // than override with new language
    if (isset($_GET['taal'])) {
        $taal = $_GET['taal'];
        unset($_GET['taal']);
    }
    $_SESSION['sestaal'] = $taal;
    include ROOT_DIR . "/inc/language/" . $taal . ".php";
    $_SESSION['txt'] = $txt;

} else {
    if ($debugmode) error_log("calling load_language --> cache hit");
    if (isset($_SESSION['txt'])) {
        // take txt from session if set (normal case)
        $txt = $_SESSION['txt'];
    } else {
        // nothing set reload from scratch
        include ROOT_DIR . "/inc/language/en.php";
        if (isset($_SESSION['sestaal'])) {
            include ROOT_DIR . "/inc/language/" . $_SESSION['sestaal'] . ".php";
        }
        $_SESSION['txt'] = $txt;
    }
    if (isset($_SESSION['sestaal'])) {
        $taal = $_SESSION['sestaal'];
    }
}
// date_default_timezone_set('Europe/Brussels');
if ($taal == "nl") {
    $locale = 'nl_NL'; // For IntlDateFormatter
}
if ($taal == "fr") {
    $locale = 'fr_FR'; // For IntlDateFormatter
}
if ($taal == "de") {
    $locale = 'de_DE'; // For IntlDateFormatter
}
if ($taal == "en") {
    $locale = 'en_US'; // For IntlDateFormatter
}
// preparing a localized month array
$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMMM");
?>
