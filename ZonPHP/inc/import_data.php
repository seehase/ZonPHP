<?php
global $params;
$importer = clean($params['importer']); // remove all special chars allow only A-Za-z0-9_-

$now = time();
if (!isset($_SESSION['LastImportRun'])) {
    $_SESSION['LastImportRun'] = $now;
}
$lastImportRun = $_SESSION['LastImportRun'];
if ($lastImportRun < $now - 5) {
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
}
