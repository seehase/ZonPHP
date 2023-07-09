<?php

// force to load missing or changed language
function loadLanguage($params): void
{
    // if new language is set via URL parameter and exists, or no text in session --> RELOAD
    if ((isset($_GET['language']) && isActive($_GET['language'])) || !isset($_SESSION['txt'])) {
        // user default language for this installation defined in parameters
        $default_user_language = $params['defaultLanguage'];

        // always load default language
        $defaultTXT = parse_ini_file(ROOT_DIR . "/inc/language/en.ini");

        // than load and override with new language
        if (isset($_GET['language'])) {
            $languageToLoad = strtolower($_GET['language']);
        } else {
            // in case on new session or no TXT in session load user default language
            $languageToLoad = $default_user_language;
        }
        $userTXT = array();
        if ($languageToLoad != "en") {
            // EN is already loaded, no need to load again
            $userTXT = parse_ini_file(ROOT_DIR . "/inc/language/" . $languageToLoad . ".ini");
        }
        $_SESSION['language'] = $languageToLoad;
        $_SESSION['txt'] = array_merge($defaultTXT, $userTXT);
        unset($_GET['language']);
    }
}
