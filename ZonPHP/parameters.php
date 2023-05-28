<?php
/*****************************************************************************
 * Set constants to get absolute paths when needed.
 * This is based on the relative position of this parameter.php file
 * ROOT_DIR gives the full absolute path to a file, including the install directory  
 * HTML_PATH gives the extension for use in href
 * PHP_PATH is HTML_PATH without the forward slash
 * ROOT_DIR = $_SERVER['DOCUMENT_ROOT'] + SUFFIX
 *****************************************************************************/
define('ROOT_DIR', realpath(__DIR__.'/'));
define ('HTML_PATH',substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
define ('PHP_PATH', ltrim( HTML_PATH, '/')); 
// read version and debug configuration
include_once "inc/version_info.php";

/******************************************************************************
 * Hier kan u server , username , password , database naam ingeven van uw database
 * Here you can server, username, password, database name for enter your database
 * Ici, vous pouvez serveur, nom d'utilisateur, mot de passe, nom de la base pour l'entre le database
 *
 * Hier können Sie Server, Benutzername, Passwort, Datenbank-Namen geben für Ihre Datenbank
 * $spassword wordt gebruikt voor inloggen in de database
 *
 * $spassword is used for logging into the database
 *
 * $spassword est utilisé pour la connexion à la base de données *
 *
 * sample: https://solar.seehausen.org
 *
 * more infos under
 * https://github.com/seehase/ZonPHP
 ******************************************************************************/

/*****************************************************************************
 * Configuration
 *
 *     admin username and password must be set to be able to login,
 *     with empty password login is restricted
 *
 *****************************************************************************/

//fixme: set pw to "" as default in final version,
$admin_username = "admin";
$admin_password = " ";

/*****************************************************************************/
/* Database configuration                                                    */
/*****************************************************************************/
$sserver = "localhost";                     /*  Database server   --> default: "localhost"               */
$susername = "root";                        /*  Database user     --> default: "root"                    */
$spassword = "";                            /*  Database password --> default: ""                        */
$sdatabase_name = "solar";                  /*  Database name     --> default: "solar"                   */

$table_prefix = "tgeg";                     /*  table name prefix --> default: "tgeg"                    */

$default_language = "de";                   /*  preferred language --> values: en, de, fr, nl            */
$use_utf8 = true;                           /*  set this to true if your server is configured using UTF8 */

/*****************************************************************************/
/* weatherstation weewx configuration          OPTIONAL                      */
/*****************************************************************************/
$use_weewx = false;
$weewx_server = "localhost";              /*  weewx Database server   --> default: "localhost"         */
$weewx_username = "weewx";                /*  weewx Database user     --> default: "weewx"             */
$weewx_password = "weewx";                /*  weewx Database password --> default: "weewx"             */
$weewx_database_name = "weewx";           /*  weewx Database name     --> default: "weewx"             */
$weewx_table_name = "archive";            /*  weewx table name     --> default: "archive"              */
$weewx_temp_column = "outTemp";           /*  weewx temp column name     --> default: "outTemp"        */
$weewx_timestamp_column = "dateTime";     /*  weewx timestamp column name     --> default: "dateTime"  */
$weewx_temp_is_farenheit = true           /*  weewx temp is in fahrenheit     --> default: "true"       */
?>
