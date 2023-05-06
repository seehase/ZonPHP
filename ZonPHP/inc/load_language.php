<?php
// version 2.0
//php8.0 ready
$path = "";
if (!file_exists("inc/language/en.php")) {
    $path = "../";
}
$taal = "en";
if (isset($default_language)) {
    $default_language = strtolower($default_language);
    if ($default_language === "de" || $default_language === "en" || $default_language === "fr" || $default_language === "nl" || $default_language === "at") {
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
    include $path . "inc/language/en.php";
    // than override with new language
    if (isset($_GET['taal'])) {
        $taal = $_GET['taal'];
        unset($_GET['taal']);
    }
    $_SESSION['sestaal'] = $taal;
    include $path . "inc/language/" . $taal . ".php";
    $_SESSION['txt'] = $txt;

} else {
    if ($debugmode) error_log("calling load_language --> cache hit");
    if (isset($_SESSION['txt'])) {
        // take txt from session if set (normal case)
        $txt = $_SESSION['txt'];
    } else {
        // nothing set reload from scratch
        include $path . "inc/language/en.php";
        if (isset($_SESSION['sestaal'])) {
            include $path . "inc/language/" . $_SESSION['sestaal'] . ".php";
        }
        $_SESSION['txt'] = $txt;
    }
    if (isset($_SESSION['sestaal'])) {
        $taal = $_SESSION['sestaal'];
    }
}
// date_default_timezone_set('Europe/Brussels');
if ($taal == "nl") {
    //commented out setlocale. Do we need it?
    //setlocale(LC_TIME, 'Dutch_Netherlands', 'Dutch', 'nl_NL', 'nl', 'nl_NL.ISO8859-1', 'nl_NL.UTF-8', 'nld_nld', 'nld', 'nld_NLD', 'NL_nl');
    $locale='nl_NL'; // For IntlDateFormatter
}
if ($taal == "fr") {
    //setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1', 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8');
    $locale='fr_FR'; // For IntlDateFormatter
}
if ($taal == "de") {
    //setlocale(LC_TIME, 'de', 'de_DE', 'de_DE.UTF-8', 'de_AT', 'de_AT.UTF-8', 'deutsch', 'german', 'deu');
    $locale='de_DE'; // For IntlDateFormatter
}
if ($taal == "at") {
    //setlocale(LC_TIME, "de_AT", 'de_AT.UTF-8', 'de', 'de_DE', 'de_DE.UTF-8');
    $locale='de_DE'; // For IntlDateFormatter
}
if ($taal == "en") {
    //setlocale(LC_TIME, 'english-us', 'English', 'en_US', 'en_GB', 'en', 'en_US.ISO8859-1', 'en_US.UTF-8' );
    $locale='en_US'; // For IntlDateFormatter
}
// preparing a localized month array
$months[] = "";
for ($i = 1; $i <= 12; $i++) {
    if ($use_utf8 == true) {
        $format = new IntlDateFormatter($locale , IntlDateFormatter::NONE, 
              IntlDateFormatter::NONE, NULL, NULL, "MMMM");
		$months[] = substr(datefmt_format($format, mktime(0, 0, 0, $i)),0,3);
     } else {
        $months[] = utf8_encode(date("M", strtotime("2009-" . $i . "-01")));
    }
}
//print_r($months);
$_SESSION['months'] = $months;
?>