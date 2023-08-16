<?php
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
    global $params;
    if (isset($params['debugEnabled']) && $params['debugEnabled']) {

        addCheckMessage("DEBUG", date("Y-m-d H:i:s - ") . $msg, false);
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


function getFilesToImport(string $folderName, $lastImportDate, $importPrefix): array
{
    $directory = ROOT_DIR . "/" . $folderName . '/';
    $files_to_import = array();
    $num_today = date("Ymd", time());
    addDebugInfo("getFilesToImport: directory: $directory");
    if ($lastImportDate == NODATE) {
        // initial load, no data found in database
        $files = scandir($directory);
        $mindate = intval($num_today);
        foreach ($files as $filename) {
            // find oldest import file
            if (strlen($filename) == strlen($importPrefix) + 13) {
                $filedate = intval(substr($filename, strlen($importPrefix) + 1, 8));
                if ($filedate < $mindate) {
                    $mindate = $filedate;
                }
            }
        }
        $lastImportDate = $mindate . "";
        addDebugInfo("getFilesToImport: start on empty DB oldest file in dir: $lastImportDate");
    }

    for ($i = 0; $i <= 365; $i++) {
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
        }
        $dayValues = substr($dayValues, 0, -1);
        $sql_insert_day = "insert into " . TABLE_PREFIX . "_dag (IndexDag, Datum_Dag, Geg_Dag, kWh_Dag, Naam) values $dayValues";
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

function convertLocalDateTime(string $dateStr, bool $force = false): string
{
    global $params;
    if ($force || !$params['database']['UTC_is_used']) {
        $tz_from = $params['timeZone'];
        try {
            $newDateTime = new DateTime($dateStr, new DateTimeZone($tz_from));
            $newDateTime->setTimezone(new DateTimeZone("UTC"));
            return $newDateTime->format("Y-m-d H:i:s");
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
    return strtotime($datetime . "");
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
