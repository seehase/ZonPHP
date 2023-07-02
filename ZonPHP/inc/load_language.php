<?php

// php8.0 ready
$language = "en";
$default_language = $params['defaultLanguage']; --> auch in init setzten bzw garnicht und immer aus in params lesen

$default_language = strtolower($default_language);
--> in param validate machen nicht mehr hier
if ($default_language === "de" || $default_language === "en" || $default_language === "fr" || $default_language === "nl") {
    $language = $default_language;
}


if (isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
} else {
    $_SESSION['language'] = $default_language;
    xxx force load wenn in session nicht gesetzt???
}

// if new language is set via URL parameter
if (isset($_GET['language'])) {
    if ($debugmode) error_log("calling load_language --> reload needed");
    // load default language
    $txt = parse_ini_file(ROOT_DIR . "/inc/language/en.ini", false);
    // than override with new language
    xxx
    // fixme: check if language is in suppoerted langiuage  you can set ?language=dsfhskdfjhdjshf

    $language = $_GET['language'];
    unset($_GET['language']);

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
        $defaultTXT = parse_ini_file(ROOT_DIR . "/inc/language/en.ini", false);
        $userTXT = parse_ini_file(ROOT_DIR . "/inc/language/" . $language . ".ini", false);
        $txt = array_merge($defaultTXT, $userTXT);
        $_SESSION['txt'] = $txt;
    }
    if (isset($_SESSION['language'])) {
        $language = $_SESSION['language'];
    }
}




