<?php /** @noinspection PhpUnused */
/**
 * common functions and constants
 */

function getTxt($key)
{
    return $_SESSION["txt"][$key] ?? "undefined key: " . $key;
}

function isValidLanguage($language): bool
{
    $LANGUAGES = array("en", "de", "fr", "nl");
    if (in_array(strtolower($language), $LANGUAGES)) {
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


function addDebugInfo(string $msg): void
{
    global $params, $debugmode;
    if (!isset($_SESSION['debugMessages'])) {
        $_SESSION['debugMessages'] = array();
    }
    if (!isset($params) || (isset($params['debugEnabled']) && $params['debugEnabled'])) {
        $_SESSION['debugMessages'][] = (date("Y-m-d H:i:s - ") . $msg);
    }
    if ($debugmode) error_log(date("Y-m-d H:i:s - ") . $msg);
}

function addDBInfo(string $msg): void
{
    global $params;
    if (!isset($_SESSION['dbMessages'])) {
        $_SESSION['dbMessages'] = array();
    }
    if (!isset($params) || (isset($params['debugEnabled']) && $params['debugEnabled'])) {
        $_SESSION['dbMessages'][] = (date("Y-m-d H:i:s - ") . $msg);
    }
}

function checkChangedConfigFiles(): bool
{
    // check parameter.php
    $paramsFileDate = filemtime(ROOT_DIR . "/parameters.php");
    if (!isset($_SESSION['paramsFileDate'])) {
        $_SESSION['paramsFileDate'] = $paramsFileDate;
        addDebugInfo("checkChangedConfigFiles: paramsFileDate not found in session -> changed = true");
        return true;
    } else {
        if ($_SESSION['paramsFileDate'] < $paramsFileDate) {
            $_SESSION['paramsFileDate'] = $paramsFileDate;
            unset($_SESSION['params']);
            unset($_SESSION['txt']);
            unset($_SESSION['colors']);
            addDebugInfo("checkChangedConfigFiles: paramsFileDate has been changed -> changed = true");
            return true;
        }
    }
    // check parameter_dev.php if exists
    if (file_exists(ROOT_DIR . "/parameters_dev.php")) {
        $paramsDevFileDate = filemtime(ROOT_DIR . "/parameters_dev.php");
        if (!isset($_SESSION['paramsDevFileDate'])) {
            $_SESSION['paramsDevFileDate'] = $paramsDevFileDate;
            return true;
        } else {
            if ($_SESSION['paramsDevFileDate'] < $paramsDevFileDate) {
                $_SESSION['paramsDevFileDate'] = $paramsDevFileDate;
                unset($_SESSION['params']);
                unset($_SESSION['txt']);
                unset($_SESSION['colors']);
                addDebugInfo("checkChangedConfigFiles: parameter_dev changed -> changed = true");
                return true;
            }
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
            addDebugInfo("checkChangedConfigFiles: language files changed -> changed = true");
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
            addDebugInfo("checkChangedConfigFiles: themes files changed -> changed = true");
            return true;
        }
    }
    return false;
}

function controledatum($idag, $imaand, $ijaar): bool
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

function getLastImportDateForPlant(string $plantName, $con): string
{
    $sql = "SELECT * FROM " . TABLE_PREFIX . "_dag WHERE Naam ='$plantName' ORDER BY Datum_Dag DESC LIMIT 1";
    return getLastImportDate($sql, $con);
}

function getFirstImportDateForPlant(string $plantName, $con): string
{
    $sql = "SELECT * FROM " . TABLE_PREFIX . "_dag WHERE Naam ='$plantName' ORDER BY Datum_Dag ASC LIMIT 1";
    return getLastImportDate($sql, $con);
}

function getStartDate($con): string
{
    $sql = "SELECT Datum_Dag FROM " . TABLE_PREFIX . "_dag ORDER BY Datum_Dag ASC LIMIT 1";
    return getLastImportDate($sql, $con);
}

function getLastImportDate(string $sql, $con): string
{
    $firstImportDate = NODATE;
    // get oldest import date from db
    $result = mysqli_query($con, $sql) or die("ERROR: getting last date from DB " . mysqli_error($con));
    $row = mysqli_fetch_array($result);
    if ($row != null) {
        $firstImportDate = $row['Datum_Dag'];
    }
    addDebugInfo("getLastImportDate: $firstImportDate");
    return $firstImportDate;
}

function isValidTimezoneId($timezoneId): bool
{
    $zoneList = timezone_identifiers_list(); # list of (all) valid timezones
    return in_array($timezoneId, $zoneList); # set result
}

function prepareFarm(&$params, $con): void
{
    $params['farm']['installationDate'] = getStartDate($con);
    foreach ($params['PLANTS'] as $name => $plant) {
        $params['farm']['plants'][$name]['installationDate'] = getFirstImportDateForPlant($name, $con);
    }
    $_SESSION['params'] = $params;
}


function getFilesToImport(string $folderName, $lastImportDate, $importPrefix, $datePattern = "Ymd", $fileExtentsion = ".csv"): array
{
    $directory = ROOT_DIR . "/" . $folderName . '/';
    $files_to_import = array();
    $dateStringLength = strlen(date($datePattern, time()));
    $filenameStringLength = strlen($importPrefix) + $dateStringLength + strlen($fileExtentsion);

    addDebugInfo("getFilesToImport: directory: $directory");
    if (!is_dir($directory)) {
        addDebugInfo("getFilesToImport: directory: $directory does not exist");
        // return empty array
        return array();
    }
    if ($lastImportDate == NODATE) {
        // initial load, no data found in database
        $files = scandir($directory);
        $oldestImportDate = time();
        foreach ($files as $filename) {
            // find oldest import file
            if (strlen($filename) == $filenameStringLength) {
                $filenameDateString = substr($filename, strlen($importPrefix), $dateStringLength);
                $filenameTimeStamp = DateTime::createFromFormat($datePattern, $filenameDateString)->getTimestamp();

                if ($filenameTimeStamp < $oldestImportDate) {
                    $oldestImportDate = $filenameTimeStamp;
                }
            }
        }
        $lastImportDate = date("Y-m-d H:i:s", $oldestImportDate);
        addDebugInfo("getFilesToImport: start on empty DB oldest file in dir: $lastImportDate");
    }

    // last import DateTime at midnight
    $lastDateTimeAtMidnight = new DateTime($lastImportDate);
    $lastDateTimeAtMidnight->setTime(0, 0, 0);
    $tomorrow = new DateTime('tomorrow');

    for ($i = 0; $i <= 365; $i++) {
        $nextDayToImport = $lastDateTimeAtMidnight->modify("+" . $i . " day");
        if ($nextDayToImport > $tomorrow) {
            // skip if date is in future
            break;
        }

        $nextDayToImportString = date($datePattern, $nextDayToImport->getTimestamp());
        $filename = $directory . $importPrefix . $nextDayToImportString . $fileExtentsion;
        if (file_exists($filename)) {
            $files_to_import[] = $filename;
        }
    }
    addDebugInfo("getFilesToImport: Files to import: " . count($files_to_import));
    return $files_to_import;
}

/**
 * add file-separator and dateformat
 */
function readImportFile(string $filename, int $linesToSkip): array
{
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
    addDebugInfo("readImportFile: filename: $filename - $linesToSkip lines skipped - " . count($lines) . " read");
    return $lines;
}

function readIniFile(string $filename): string
{
    $file = fopen($filename, "r") or die ("Cannot open " . $filename);
    $lines = array();
    while (!feof($file)) {
        $line = trim(fgets($file, 1024));
        if (!isComment($line)) {
            $lines[] = $line;
        }
    }
    fclose($file);
    addDebugInfo("readIniFile: filename: $filename - " . count($lines) . " lines read");
    return implode(PHP_EOL, $lines);
}

function readParameterFile(): string
{
    if (file_exists(ROOT_DIR . "/parameters_dev.php")) {
        $filename = ROOT_DIR . "/parameters_dev.php";
    } else {
        $filename = ROOT_DIR . "/parameters.php";
    }
    return readIniFile($filename);
}

function readWeewxFile(): string
{
    $filename = ROOT_DIR . "/weewx.ini.php";
    if (file_exists($filename)) {
        return readIniFile($filename);
    } else return "";
}

function isComment(string $input): bool
{
    if (strlen($input) > 0) {
        $char = substr($input, 0, 1);
        if ($char === ";" || $char === "#" || $char === "/" || $char === "*" || $char === "<" || $char === "-" || $char === "⌃" || $char === "⌄") {
            return true;
        }
    }
    return false;
}

function prepareAndInsertData(array $dbValues, $con): void
{
    if (count($dbValues) > 0) {
        // Sort array desc based on timestamp
        usort($dbValues, function ($first, $second) {
            return strnatcmp($first['timestamp'], $second['timestamp']);
        });

        $dayValues = "";
        $cummulatedkWh = 0;
        $name = "";
        $currentDate = date("Y-m-d", strtotime($dbValues[0]['timestamp']));
        foreach ($dbValues as $row) {
            $watt = $row['watt'];
            $cummulatedkWh = $row['cummulatedkWh'];
            $name = $row['name'];
            $timeStamp = $row['timestamp'];
            $id = $timeStamp . $name;
            $dayValues .= "('$id', '$timeStamp', $watt, $cummulatedkWh, '$name'),";
            // $dayValues .= "('$timeStamp', $watt, '$name'),";
        }
        $dayValues = substr($dayValues, 0, -1);
        $sql_insert_day = "insert into " . TABLE_PREFIX . "_dag (IndexDag, Datum_Dag, Geg_Dag, kWh_Dag, Naam) values $dayValues";
        // $sql_insert_day = "insert into " . TABLE_PREFIX . "_dag ( Datum_Dag, Geg_Dag, Naam) values $dayValues";
        $del_month = "DELETE FROM " . TABLE_PREFIX . "_maand WHERE Naam ='$name' AND Datum_Maand='$currentDate'";
        $sqL_insert_month = "insert into " . TABLE_PREFIX . "_maand (IndexMaand, Datum_Maand, Geg_Maand, Naam) values ('$currentDate$name', '$currentDate', $cummulatedkWh, '$name')";

        mysqli_query($con, $del_month) or die("Query failed. ERROR1: " . $del_month . mysqli_error($con));
        mysqli_query($con, $sql_insert_day) or die("Query failed. ERROR2: " . $sql_insert_day . mysqli_error($con));
        mysqli_query($con, $sqL_insert_month) or die("Query failed. ERROR3: " . $sqL_insert_month . mysqli_error($con));
        addDebugInfo("prepareAndInsertData: data to insert: " . count($dbValues));
    }
}

/**
 * Find HTML path if they are not a substring, but expecting at least they have a part in common
 *
 * Example:
 * ROOT_DIR:      /mnt/web405/b0/65/52610665/htdocs/zonphp
 * DOCUMENT_ROOT: /home/strato/http/premium/rid/06/65/52610665/htdocs
 * ==>
 * ROOT_DIR:      65/52610665/htdocs/zonphp
 * DOCUMENT_ROOT: 65/52610665/htdocs
 * ==> Substing -> /zonphp
 * @noinspection PhpStrFunctionsInspection
 */
function getHTMLPATH(string $path1, $path2): string
{
    if (strpos($path1, $path2) !== false) {
        // default case, ROOT_DIR is part of DOCUMENT_ROOT
        return str_replace('\\', '/', substr($path1, strlen($path2)));
    } else {
        if (strlen($path1) > strlen($path2)) {
            $longerPaths = explode("/", $path1);
            $shorterPaths = explode("/", $path2);
        } else {
            $longerPaths = explode("/", $path2);
            $shorterPaths = explode("/", $path1);
        }
        $cnt = 0;
        foreach ($longerPaths as $item) {
            $idx = array_search($item, $shorterPaths);
            if (!$idx) {
                unset($longerPaths[$cnt]);
                $cnt++;
            } else {
                break;
            }
        }
        $cnt = 0;
        foreach ($shorterPaths as $item) {
            $idx = array_search($item, $longerPaths);
            if (!$idx) {
                unset($shorterPaths[$cnt]);
                $cnt++;
            } else {
                break;
            }
        }
        if (count($shorterPaths) > count($longerPaths)) {
            $longerPath = implode("/", $shorterPaths);
            $shorterPath = implode("/", $longerPaths);
        } else {
            $longerPath = implode("/", $longerPaths);
            $shorterPath = implode("/", $shorterPaths);
        }
        if (stripos($longerPath, $shorterPath) !== false) {
            return substr($longerPath, strlen($shorterPath));
        }
    }
    return "/";
}

function convertDateTime(string $dateStr): string
{
    try {
        $newDateTime = new DateTime($dateStr);
        $newDateTime->setTimezone(new DateTimeZone("UTC"));
        return $newDateTime->format("Y-m-d H:i:s");
    } catch (Exception $e) {
        error_log($e);
        return "";
    }
}

function convertToLocalDateTime(string $dateStr, string $dateFormat = "Y-m-d H:i:s"): string
{
    global $params;
    try {
        $tz_to = $params['timeZone'];
        $importDateFormat = "Y-m-d H:i:s";
        $newDateTime = DateTime::createFromFormat($importDateFormat, $dateStr, new DateTimeZone("UTC"));
        $newDateTime->setTimezone(new DateTimeZone($tz_to));
        return $newDateTime->format($dateFormat);
    } catch (Exception $e) {
        error_log($e);
        return "";
    }
}

function convertDateTimeToLocalDateTime(int $dateTime, string $dateFormat = "Y-m-d H:i:s"): string
{
    global $params;
    try {
        $tz_to = $params['timeZone'];
        $newDateTime = new DateTime();
        $newDateTime->setTimestamp($dateTime);
        $newDateTime->setTimezone(new DateTimeZone($tz_to));
        return $newDateTime->format($dateFormat);
    } catch (Exception $e) {
        error_log($e);
        return "";
    }
}

function convertLocalDateTime(string $dateStr, string $importDateFormat = "Y-m-d H:i:s", bool $force = false): string
{
    global $params;
    if ($force || !$params['database']['UTC_is_used']) {
        $tz_from = $params['timeZone'];
        try {
            $newDateTime = DateTime::createFromFormat($importDateFormat, $dateStr, new DateTimeZone($tz_from));
            if ($newDateTime) {
                $newDateTime->setTimezone(new DateTimeZone("UTC"));
                return $newDateTime->format("Y-m-d H:i:s");
            } else {
                return $dateStr;
            }
        } catch (Exception $e) {
            error_log($e);
            return $dateStr;
        }
    } else {
        // Date is already in UTC
        return $dateStr;
    }
}

function convertToUnixTimestamp($datetime): string
{
    $cleanDate = str_replace('/', '-', $datetime);
    return strtotime($cleanDate . "");
}

// get min UnixTimeStamp from given date
function getMinUnixTimestamp(string $dateStr): int
{
    $newDateTime = DateTime::createFromFormat("Y-m-d", $dateStr, new DateTimeZone("UTC"));
    $newDateTime->setTime(0, 0, 0);
    return $newDateTime->getTimestamp();
}

// get max UnixTimeStamp from given date
function getMaxUnixTimestamp(string $dateStr): int
{
    $newDateTime = DateTime::createFromFormat("Y-m-d", $dateStr, new DateTimeZone("UTC"));
    $newDateTime->setTime(23, 59, 59);
    return $newDateTime->getTimestamp();
}

function hasErrorOrWarnings(): bool
{
    if (count($_SESSION['params']['check']['ERROR']) > 0 || count($_SESSION['params']['check']['WARN']) > 0) {
        return true;
    } else {
        return false;
    }
}

// build colors per inverter array
function colorsPerInverter(): array
{
    global $colors;
    $myColors = array();
    for ($k = 0; $k < count(PLANT_NAMES); $k++) {
        $col1 = "color_inverter" . $k . "_chartbar_min";
        $col1 = "'" . $colors[$col1] . "'";
        $myColors[PLANT_NAMES[$k]]['min'] = $col1;
        $col1 = "color_inverter" . $k . "_chartbar_max";
        $col1 = "'" . $colors[$col1] . "'";
        $myColors[PLANT_NAMES[$k]]['max'] = $col1;
    }
    return $myColors;
}

// build colors per inverter array
function colorsPerInverterJS(): array
{
    global $colors;
    $myColors = array();
    for ($k = 0; $k < count(PLANT_NAMES); $k++) {
        $col1 = "color_inverter" . $k . "_chartbar_min";
        $col1 = $colors[$col1];
        $myColors[PLANT_NAMES[$k]]['min'] = $col1;
        $col1 = "color_inverter" . $k . "_chartbar_max";
        $col1 = $colors[$col1];
        $myColors[PLANT_NAMES[$k]]['max'] = $col1;
    }
    return $myColors;
}

// Check for empty fields during importing CSV files
function hasValidValues(array $values, int $index): bool
{
    if (count($values) >= (($index + 1) * 2 + 1)) {
        $date = $values[0];
        $fieldkWh = $values[($index * 2) + 1];
        $fieldWatt = $values[($index * 2) + 2];
        return strlen($date) > 0 && strlen($fieldkWh) > 0 && strlen($fieldWatt) > 0;
    }
    return false;
}

function hasAllValidValues(array $values, int $inverterCount): bool
{
    $result = true;
    for ($i = 0; $i < $inverterCount; $i++) {
        $result = ($result && hasValidValues($values, $i));
    }
    return $result;
}

function getPhpInfo(): string
{
    ob_start();
    phpinfo();
    $html = ob_get_contents();
    ob_end_clean();

    /// Delete styles from output
    $html = preg_replace('#(\n?<style[^>]*?>.*?</style[^>]*?>)|(\n?<style[^>]*?/>)#is', '', $html);
    $html = preg_replace('#(\n?<head[^>]*?>.*?</head[^>]*?>)|(\n?<head[^>]*?/>)#is', '', $html);
    // Delete DOCTYPE from output
    $html = preg_replace('/<!DOCTYPE html PUBLIC.*?>/is', '', $html);
    // Delete body and html tags
    $html = preg_replace('/<html.*?>.*?<body.*?>/is', '', $html);
    return preg_replace('/<\/body><\/html>/i', '', $html);
}