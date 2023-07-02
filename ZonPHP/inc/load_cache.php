<?php
/**
 * load  only if changed or invalid cache in session
 * import data always
 */

include_once "connect.php";

if (isset($_SESSION['lastupdate']) && ($_SESSION['lastupdate'] + $cache_timeout) > (time())) {
    // cache still valid --> do not reload cache
    if ($debugmode) error_log("cache hit --> ");

    // error_log("cache is valid  " . ($_SESSION['lastupdate'] + $cache_timeout) . " - " . time());
} else {
    // reload cache
    if ($debugmode) error_log("cache failed --> need to reload data");


    // get latest Version from github can cause error on some provider e.g.bplaced do not allow file_get_content
    $github_version = "unknown";
    $homepage = "";
    if (!isset($version) || strlen($version) < 6) {
        $version = "unknown";
    }
    if ($params['checkVersion'] == true) {
        try {
            if (strpos($version, "(dev)") > 0) {
                $homepage = file_get_contents('https://raw.githubusercontent.com/seehase/ZonPHP/development/ZonPHP/inc/version_info.php');
            } else {
                $homepage = file_get_contents('https://raw.githubusercontent.com/seehase/ZonPHP/master/ZonPHP/inc/version_info.php');
            }
            $pos_start = strpos($homepage, '"v');
            $pos_end = strpos($homepage, '";', $pos_start + 2);

            if ($pos_start > 0) {
                $github_version = substr($homepage, $pos_start + 1, $pos_end - $pos_start - 1);
            }
        } catch (Throwable $e) {

        }
    }
    $_SESSION['github_version'] = $github_version;

    $new_version_label = "";
    if ($github_version > $version) {
        $new_version_label = " - new version " . $github_version;
    }
    $_SESSION['new_version_label'] = $new_version_label;

    // -----------------------------------------------------------------------------------------------------------------
    $_SESSION['lastupdate'] = time();
}
// import new data
include_once ROOT_DIR . "/inc/import_data.php";
