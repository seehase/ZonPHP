<?php
// remove all special chars allow only A-Za-z0-9_-
$importer = clean($params['importer']);

// check file is not "", and not index.php
if ((strlen($importer) > 0) && (strtoupper($importer) != "INDEX.PHP")) {
    $importerFile = ROOT_DIR . "/importer/" . $importer . ".php";
    // check that file exists under /importer folder
    if (file_exists($importerFile)) {
        include_once $importerFile;
    }
}

// FIXME: create common library for helper functions
function clean($string)
{
    $string = preg_replace('/[^A-Za-z0-9_\-]/', '', $string); // Removes special chars.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
