<?php /** @noinspection PhpStrFunctionsInspection */
global $formatter;

//introducing new variable $shortmonthcategories to replace the fixed arrays in chart_lang_xx
//for use in year_chart, last_years_chart and cumulative_chart
//format follows ICU and Unicode
//skipping months, weekdays and shortMonths from chart_lang_xx
$formatter->setPattern('LLL');
$shortMonthLabels = "";
for ($i = 1; $i <= 12; $i++) {
    // get month names in current locale
    $shortMonthLabels .= '"' . str_replace('.', '', datefmt_format($formatter, mktime(0, 0, 0, $i, 15))) . '",';
}
$shortMonthLabels = strip($shortMonthLabels);


// convert value array into valid "data" string for chart datasets
function convertValueArrayToDataString($array): string
{
    $out = "";
    foreach ($array as $value) {
        $out .= '"' . $value . '",';
    }
    return strip($out);
}

function convertKeyValueArrayToDataString($array): string
{
    $out = "";
    foreach ($array as $key => $value) {
        $out .= "{x: $key, y: $value },";
    }
    return strip($out);
}

function buildConstantDataString($value, $count): string
{
    $out = "";
    for ($i = 1; $i <= $count; $i++) {
        $out .= '"' . $value . '",';
    }
    return $out;
}

// strips last "," from trimmed string
function strip($value): string
{
    $out = trim($value);
    if (strlen($out) > 0 && substr($out, -1) == ",") {
        $out = substr($out, 0, -1);
    }
    return $out;
}

function getIsSelectedString($key, $array): string
{
    if (in_array($key, $array)) {
        $isSelected = " selected";
    } else {
        $isSelected = "";
    }
    return $isSelected;
}

function getIsCheckedString($key, $array): string
{
    if (in_array($key, $array)) {
        $isChecked = " checked";
    } else {
        $isChecked = "";
    }
    return $isChecked;
}

function getIsHidden($key, $array): string
{
    if (in_array($key, $array)) {
        $isChecked = "false";
    } else {
        $isChecked = "true";
    }
    return $isChecked;
}

function updateDate(string $dateString): string
{
    $suppliedDate = new DateTime($dateString);
    $currentYear = (int)(new DateTime())->format('Y');
    $newDate = (new DateTime())->setDate($currentYear, (int)$suppliedDate->format('m'), (int)$suppliedDate->format('d'));
    return $newDate->format('Y-m-d');
}
