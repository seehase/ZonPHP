<?php
/**
 common functions
 */
function getTxt($key)
{
    if (isset($_SESSION["txt"]) && isset($_SESSION["txt"][$key])) {
        return $_SESSION["txt"][$key];
    } else {
        return "undefined key: " . $key;
    }
}

function isActive($language)
{
    if (in_array(strtolower($language), LANGUAGES)) {
        return true;
    } else {
        return false;
    };
}
