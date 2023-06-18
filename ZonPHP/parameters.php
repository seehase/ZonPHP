<?php
/******************************************************************************
 * Information
 ******************************************************************************
 * sample: https://solar.seehausen.org
 *
 * more infos under
 * https://github.com/seehase/ZonPHP
 *
 *
 * #### REQUIRED ####
 * Username and password configuration
 *
 *     admin username and password must be set to be able to login,
 *     with empty password login is restricted
 *
 *****************************************************************************/

$admin_username = "admin";
$admin_password = " ";

/******************************************************************************
 * #### REQUIRED ####
 * Database credentials and connection details  
 ******************************************************************************
 * Here you must enter server, username, password, database name for your database
 ******************************************************************************/

$sserver = "localhost";                     /*  Database server   --> default: "localhost"               */
$susername = "root";                        /*  Database user     --> default: "root"                    */
$spassword = "";                            /*  Database password --> default: ""                        */
$sdatabase_name = "solar";                  /*  Database name     --> default: "solar"                   */
$default_language = "de";                   /*  preferred language --> values: en, de, fr, nl            */
/*-------------------------------------------------------------------------------------------------------*/
/***               suggested defaults, change only when needed                                         ***/
$table_prefix = "tgeg";                     /*  table name prefix --> default: "tgeg"                    */
$use_utf8 = true;                           /*  set this to true if your server is configured using UTF8 */

/*****************************************************************************
* #### OPTIONAL ####                                                         *
* weatherstation weewx configuration                                         *
******************************************************************************/
$use_weewx = false;
$weewx_server = "localhost";              /*  weewx Database server   --> default: "localhost"         */
$weewx_username = "weewx";                /*  weewx Database user     --> default: "weewx"             */
$weewx_password = "weewx";                /*  weewx Database password --> default: "weewx"             */
$weewx_database_name = "weewx";           /*  weewx Database name     --> default: "weewx"             */
$weewx_table_name = "archive";            /*  weewx table name     --> default: "archive"              */
$weewx_temp_column = "outTemp";           /*  weewx temp column name     --> default: "outTemp"        */
$weewx_timestamp_column = "dateTime";     /*  weewx timestamp column name     --> default: "dateTime"  */
$weewx_temp_is_farenheit = true;          /*  weewx temp is in fahrenheit     --> default: "true"      */

/*****************************************************************************
 * Set constants to get absolute paths when needed.
 * This is based on the relative position of this parameter.php file
 * ROOT_DIR gives the full absolute path to a file, including the install directory
 * HTML_PATH gives the extension for use in href
 * PHP_PATH is HTML_PATH without the forward slash
 * ROOT_DIR = $_SERVER['DOCUMENT_ROOT'] + HTML_PATH
 *****************************************************************************/

define('ROOT_DIR', realpath(__DIR__.'/'));
$tmpHTMLPath = str_replace('\\', '/', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
if (strlen($tmpHTMLPath) == 0) $tmpHTMLPath = "/";
if (substr($tmpHTMLPath, 0, 1) != "/") {
    $tmpHTMLPath = "/" . $tmpHTMLPath;
}
define ('HTML_PATH', $tmpHTMLPath);
define ('PHP_PATH', ltrim( HTML_PATH, '/'));
// read version and debug configuration
include_once "inc/version_info.php";
//echo "HTML_PATH: " .  HTML_PATH. "<br>";
//echo "ROOT_DIR: " .  ROOT_DIR. "<br>";
//echo "PHP_PATH: " .  PHP_PATH. "<br>";
?>
