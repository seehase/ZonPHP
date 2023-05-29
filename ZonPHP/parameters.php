<?php
/*****************************************************************************
 * Set constants to get absolute paths when needed.
 * This is based on the relative position of this parameter.php file
 * ROOT_DIR gives the full absolute path to a file, including the install directory  
 * HTML_PATH gives the extension for use in href
 * PHP_PATH is HTML_PATH without the forward slash
 * ROOT_DIR = $_SERVER['DOCUMENT_ROOT'] + HTML_PATH
 *****************************************************************************/

define('ROOT_DIR', realpath(__DIR__.'/'));
define ('HTML_PATH',str_replace('\\', '/', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT']))));
define ('PHP_PATH', ltrim( HTML_PATH, '/')); 
// read version and debug configuration
include_once "inc/version_info.php";

/*****************************************************************************
 * #### REQUIRED ####
 * Username and password configuration
 *
 *     admin username and password must be set to be able to login,
 *     with empty password login is restricted
 *
 *****************************************************************************/

//fixme: set pw to "" as default in final version,
$admin_username = "admin";
$admin_password = " ";

/******************************************************************************
 * #### REQUIRED ####
 * Database credentials and connection details  
 ******************************************************************************
 *
 * Hier kunt u server, username, password en databasenaam ingeven van uw database
 * $spassword wordt gebruikt voor inloggen in de database
 * 
 * Here you can enter server, username, password, database name for your database
 * $spassword is used for logging into the database
 * 
 * Ici, vous pouvez donner serveur, nom d'utilisateur, mot de passe, nom de la base 
 * pour l'entrée la base de données
 * $spassword est utilisé pour la connexion à la base de données *
 *
 * Hier können Sie Server, Benutzername, Passwort, Datenbank-Namen geben für Ihre Datenbank
 *
 * sample: https://solar.seehausen.org
 *
 * more infos under
 * https://github.com/seehase/ZonPHP
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
$weewx_temp_is_farenheit = true           /*  weewx temp is in fahrenheit     --> default: "true"      */
?>
