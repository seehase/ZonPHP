<?php

// read version and debug configuration
include_once "inc/version_info.php";

/******************************************************************************
 * Hier kan u server , username , password , database naam ingeven van uw database
 * Here you can server, username, password, database name for enter your database
 * Ici, vous pouvez serveur, nom d'utilisateur, mot de passe, nom de la base pour l'entre le database
 *
 * Hier können Sie Server, Benutzername, Passwort, Datenbank-Namen geben für Ihre Datenbank
 * $spassword wordt gebruikt voor inloggen in de database
 * alsook voor het inloggen voor het voor het instellen van je parameters voor zon PHP
 *
 * $spassword is used for logging into the database
 * and for logging on for setting the parameters for zonPHP
 *
 * $spassword est utilisé pour la connexion à la base de données
 * et pour l'ouverture de session pour définir les paramètres pour zonPHP
 *
 * $spassword ist für die Anmeldung in der Datenbank verwendet
 * und für die Anmeldung zur Einstellung der Parameter für zonPHP
 *
 *
 * more infos under
 * https://solar.seehausen.org
 * email: solar@seehausen.org
 ******************************************************************************/

/*****************************************************************************
 * Configuration
 *
 *     admin username and password must be set to be able to login,
 *     with empty password login is restricted
 *
 *****************************************************************************/

//fixme: set pw to "" as default in final version,
$admin_password = " ";
$admin_username = "admin";

/*****************************************************************************/
/* Database configuration                                                    */
/*****************************************************************************/
$sserver = "localhost";                     /*  Database server   --> default: "localhost"   */
$susername = "root";                        /*  Database user     --> default: "root"        */
$spassword = "";                            /*  Database password --> default: ""            */
$sdatabase_name = "solar";                  /*  Database name     --> default: "slaper_be"   */

$table_prefix = "tgeg";                     /*  table name prefix --> default: "tgeg"        */

$default_language = "de";                   /*  preferred language --> values: en, de, fr, nl, at */
$use_utf8 = true;                           /*  set this to true if your server is configured using UTF8 */



/*****************************************************************************/
/* datalogger configuration          OPTIONAL                                */
/*****************************************************************************/
$datalogger_password = "secret";            /*  password used for datalogger                 */
$datalogger_offset = "-1";                  /*  time offset in hours e.g.  +1  or -2         */

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
$weewx_temp_is_farenheit = true           /*  weewx temp is in farenheit     --> default: "true"       */


?>
