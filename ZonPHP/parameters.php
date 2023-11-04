<?php
#######################################################################################################################
#   ZonPHP CONFIGURATION FILE
#
#   See the file LICENSE for your rights.
#######################################################################################################################

#######################################################################################################################
#   This section is for general configuration information.
#######################################################################################################################

# Name your farm, this name is shown in menu as title
name = ZonPHP Solar

# List all your plantnames comma separated. For all plants a separate configuration section is needed, the section name
# is the same as given for plantNames, but within brackets e.g. [SOLAR1].
# This name is used in the database and the charts. Keep it short and without spaces or special characters.
plantNames = SOLAR1

# Which should be your default language? possible values: en, de, fr, nl
defaultLanguage = de

# Define the timezone to use, e.g. "Europe/Berlin" or "UTC" default is "UTC",
# list of valid timezones can be seen here https://www.php.net/manual/en/timezones.php
timeZone = Europe/Berlin

# Default theme for your installation. Specify a name that corresponds to the *.theme files in folder /themes
# available themes at this moment: blue, julia, darkgreyfire, fire, zonphp
# you can make your own theme by copy and rename an existing theme file in this folder. Theme files must be named 
# in lower case
userTheme = zonphp

# additional website shown in farm-card (for display only)
website = https://solar.seehausen.org

# additional location shown in farm-card
location = Ingolstadt

# Choose importer for your data files. Name correspond to a php file in folder /importer
# and is without ".php" extension and case sensitive
# default is "none" so no data will be imported, valid and tested importer: sunny_explorer
importer = none

# If the dates in import data (csv) is in local time you can convert the data into UTC during import
# To convert dates into UTC set importLocalDateAsUTC = true
# default = false
importLocalDateAsUTC = false

# Number in seconds to auto reload the website in your browser, if value is "0" auto reload is deactivated
# default 300 = 5min
autoReload = 300

# ZonPHP can check for newer version available on GitHub, if you do not want to check for updates
# or your provider do not allow php file_get_contents set this option to "false"
# default = true
checkVersion = true

# If you use google track manager and want to track your traffic, you can configure you personal trackingID here
# if no googleTrackingId is provided this feature is disabled
# default = "" (disabled)
googleTrackingId =

# Show "?" in top menu, it is used to show validation page and reset session if needed
# possible values: always, onError, never
showDebugMenu = always

# Enable debug information default = false, use this only if you need detailed debug information
# Debug messages will be stored until session is cleared or debug is disabled again
debugEnabled = false

# Define a comma separated list of images you want to show on the index page, for each name you specify here
# you need to have a dedicated section defined below, name must be unique and not conflicting with other
# sections.
plantImages = image1, image2

# In some special cases zonPHP cannot determine the right HTML path, in this case you can overwrite the calculated
# path and use the provided path. html-path has tail and leading slashes e.g. "/zonXYZ/"
# uncomment if you want to overwrite
# overwrite_HTML_PATH = ""

# Define the individual cards to be shown on the index page
#  available charts:
#   day        -> the day chart for today, optional with temperature values
#   month      -> overview of the current month
#   year       -> overview of the current year
#  allYears    -> overview of all collected data grouped by year
# yearPerMonth -> overview of all years grouped by month
#  top         -> show the top most days in regards of revenue
# cumulative   -> Show graph of cumulated values for all years

#  additional information:
#  farm   --> Show information for your farm and all included plants
#  plants --> Show aditional information for all plants
#  images --> Show individual imaged specified in section [images]

# provide a list of cards and additional infos as a list (names are case insensitive)
# default = "day, month, year, allYears, cumulative, yearPerMonth, top, farm, plants, images";
cards = day, month, year, allYears, cumulative, yearPerMonth, farm, images, top, plants

#######################################################################################################################
#   This section database connections parameters and is mandatory.
#   tablePrefix defaults to tgeg, only change with multiple ZonPHP instances
#   if you store your timestamp fields in UTC then set UTC_is_used = true
#
#   Put your password in quotes if it contains special characters e.g. "my_s3cret!"
#
#######################################################################################################################
[database]
host =  localhost
username = root
password = "root"
database = solar
tablePrefix = tgeg
UTC_is_used = false

#######################################################################################################################
#   This section defines parameters for a single plant, specified in parameter "plantNames" of general section
#   For each name specified in "plantNames" you need a separate section with the corresponding name
#   The section contains configuration and information used by ZonPHP
#######################################################################################################################

[SOLAR1]

# Capacity of this plant in Wp (Wattpeak)
# This value is used for calculations in several charts! Wrong entries can give strange charts.

capacity = 5040

# Specify list of expected values in kWh per month
# you can calculate expected values for your location at
# https://re.jrc.ec.europa.eu/pvg_tools/en/#api_5.1
# always provide exactly 12 values one for each month (fist value is for January, ...)
# if there are less the 12 values or invalid values,
# default = "170,200,300,500,550,600,600,550,500,300,200,170" is used
# This value is used for calculations in several charts! Wrong entries can give strange charts.

expectedYield = 180, 245, 460, 640, 645, 645, 675, 635, 510, 375, 215, 185

# Prefix of your import files for this plant e.g. sunny-explorer exports file in this
# format "prefix-yyyymmdd.csv"  e.g. "solar1-20140426.csv"
# then define importPrefix = "solar1" without separator (used by importer)

importPrefix = solar1

# optional
#
# by default import date format is read from input file in special case
# it is needed to override the value tha specify "importDateFormat"
# e.g. importDateFormat = "d-m-Y H:i:s"
# another example (1st October 2023)   01/10/2023 00:00 -> use "d/m/Y H:i"
# importDateFormat = "d-m-Y H:i:s"

# additional information shown on the card (information only), for linebreak use <br>
description = "Sample description<br>Panels: 5040Wq = 21*Trina TSM-240 PC05 Poly<br>Inverter: SMA SB 5000TL20 ESS<br>Orientation: 180 Grad 30 Grad Neigung<br>My first solar plant build in 2011"


#######################################################################################################################
#   This section specifies optional images to be shown as cards on index page
#   For each name specified in "imageNames" you need a separate section with the corresponding name  
#   you can refer to internal images located in the folder /images or use
#   external images with a complete URL
#   each image is shown as a single card
# all images need to have an identifier and 3 parameters
# imageId must be different for each image you want to show
# [imageID]
# title = "name" (Card title)
# description = "description" (additional description shown below picture)
# uri = "image.ext" (the image itself, either the name of the file in folder /images)
#                   (or external URL e.g. "https://upload.wikimedia.org/wikipedia/commons/7/71/Sun_Earth_Comparison.png") 
#######################################################################################################################

[image1]
title = Inverter
description = "Inverter + Powermeter"
uri = image1.jpg

[image2]
title = internet
description = "source: wikipedia"
uri = https://upload.wikimedia.org/wikipedia/commons/7/71/Sun_Earth_Comparison.png
