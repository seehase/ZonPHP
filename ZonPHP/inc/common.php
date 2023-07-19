<?php
/**
 * common functions
 */
function getTxt($key)
{
    return $_SESSION["txt"][$key] ?? "undefined key: " . $key;
}

function isActive($language): bool
{
    if (in_array(strtolower($language), LANGUAGES)) {
        return true;
    } else {
        return false;
    }
}

function addCheckMessage($level, $message, $isFatal = false): void
{
    global $params;
    $params['check'][$level][] = $message;
    if ($isFatal) {
        $params['check']['failed'] = true;
    }
    $_SESSION['params'] = $params;
}

function checkChangedConfigFiles(): bool
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

function controledatum($idag, $imaand, $ijaar) : bool
{
    if (!checkdate($imaand, $idag, $ijaar)) {
        return false;
    } else {
        return true;
    }
}

function checktime($hour, $minute, $second): bool
{
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $string
 * @return string
 * remove all whitespaces, slashes, ... from given string
 */
function clean($string): string
{
    $string = preg_replace('/[^A-Za-z0-9_\-]/', '', $string); // Removes special chars.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

function getLastImportDate(string $plantName, $con): string
{
    $firstImportDate = STARTDATE;
    $sql = "SELECT * FROM " . TABLE_PREFIX . "_dag WHERE Naam ='$plantName' ORDER BY Datum_Dag DESC LIMIT 1";
    // get oldest import date from db
    $result = mysqli_query($con, $sql) or die("ERROR: getting last date from DB " . mysqli_error($con));
    $row = mysqli_fetch_array($result);
    if ($row != null) {
        $firstImportDate = $row['Datum_Dag'];
    }
    return $firstImportDate;
}

function getFilesToImport(string $folderName, $lastImportDate, $importPrefix): array
{
    $directory = ROOT_DIR . "/" . $folderName . '/';
    $files_to_import = array();
    $num_today = date("Ymd", time());
    for ($i = 0; $i <= 160; $i++) {
        $num = (date("Ymd", strtotime("+" . $i . " day", strtotime($lastImportDate))));
        if ($num > $num_today) {
            // skip if date is in future
            break;
        }
        $filename = $directory . $importPrefix . "-" . $num . '.csv';
        if (file_exists($filename)) {
            $files_to_import[] = $filename;
        }
    }
    return $files_to_import;
}

function readImportFile(string $filename, int $linesToSkip) : array {
    $file = fopen($filename, "r") or die ("Cannot open " . $filename);
    $lineCounter = 1;
    $lines = array();
    while (!feof($file)) {
        $line = trim(fgets($file, 1024));
        if ($lineCounter > $linesToSkip && !empty($line)) {
            $lines[] = $line;
        }
        $lineCounter++;
    }
    fclose($file);
    return $lines;
}