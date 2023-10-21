<?php
global $params, $debugmode;
$importer = clean($params['importer']); // remove all special chars allow only A-Za-z0-9_-
if ($debugmode) error_log("Call Importer --> ");
$now = time();
if (!isset($_SESSION['LastImportRun'])) {
    $_SESSION['LastImportRun'] = $now-10;
}
$lastImportRun = $_SESSION['LastImportRun'];
if ($lastImportRun < $now - 5) {
    if ($debugmode) error_log("DO IMPORTING --> ");
    // check file is not "" and run only all 5s (prevent multi load on index page)
    if ((strlen($importer) > 0)) {
        $_SESSION['LastImportRun'] = time();
        $importerFile = ROOT_DIR . "/importer/" . $importer . ".php";
        // check that file exists under /importer folder
        if (file_exists($importerFile)) {
            include_once $importerFile;
        }
        $_SESSION['LastImportRun'] = time();
    }
} else {
    if ($debugmode) error_log("importer skipped --> ". ($lastImportRun < $now-5) ." -> ". $lastImportRun  ." < ". $now ." - 5") ;
}

