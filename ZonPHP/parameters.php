<?php
###############################################################################
# ZonPHP CONFIGURATION FILE
#
# See the file LICENSE for your rights.
###############################################################################

###############################################################################
# This section is for general configuration information.
###############################################################################

# Name your farm, this name is shown in menu as title
name = ZonPHP Solar

# List all your plantnames comma separated. For all plants a separate configuration section is needed, the section name
# is the same as given for plantNames, but within brackets e.g. [SOLAR1]. This name is used in the database and the charts. 
# Keep it short and without spaces or special characters.
plantNames = SOLAR1

# Which should be your default language? possible values: en, de, fr, nl
defaultLanguage = de

# Default theme for your installation. Specify a name that corresponds to the *.theme files in folder /themes
# available themes at this moment: blue, julia, darkgreyfire, fire
# you can make your own theme by copy and rename an existing theme file in this folder. Theme files must be named 
# in lower case
userTheme = darkgreyfire

# additional website shown in farm-card (for display only)
website = "https://solar.seehausen.org"

# additional location shown in farm-card
location = Ingolstadt

# define display interval, default is 5 for solarlog
# allowed:  1,2,3,4,5,6,10,12,15,20,30,60 sample  xls = 10, SolarLog = 5, SIC = 4
displayInterval = 5

# Choose importer for your data files. Name correspond to a php file in folder /importer
# and is without ".php" extension and case sensitive
# default is "none" so no data will be imported
importer = none

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
cards = "day, month, year, allYears, cumulative, yearPerMonth, farm, images, top, plants"


###############################################################################
#   This section database connections parameters and is mandatory.
#   tablePrefix defaults to tgeg, only change with multiple ZonPHP instances
###############################################################################
[database]

host =  localhost

username = admin

password = "secret" ## Use quotes to guard against parsing errors ##

database = solar

tablePrefix = tgeg

###############################################################################
#  This section defines parameters for a single plant, specified in parameter "plantNames" of general section
# for each name specified in "plantNames" you need a separate section with the corresponding name
# The section contains configuration and information used by ZonPHP
###############################################################################

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

expectedYield = 180, 245,460, 640,645,645,675,635,510,375,215,185

# Prefix of your import files for this plant e.g. sunny-explorer exports file in this
# format "prefix-yyyymmdd.csv"  e.g. "seehase-20140426.csv"
# then define importPrefix = "seehase" without separator (used by importer)

importPrefix = SOLAR1

# additional information shown on the card (information only)
description = "seehase<br>Panels: 5040Wq = 21*Trina TSM-240 PC05 Poly<br>Inverter: SMA SB 5000TL20 ESS<br>Orientation: 180 Grad 30 Grad Neigung<br>My first solar plant build in 2011"


###############################################################################
#   This section specifies optional images to be shown as cards on index page
###############################################################################
# specify a list of images that are shown on the index page,
# you can refere to internal images located in the folder /images or use
# external images with a complete URL
# each image is shown as a single card
# all images need to have an indentifier and 3 parameters

# imageID[title] = "name"
# imageID[description] = "description"
# imageID[uri] = "image.ext"

# imageId must be different for each image you want to show

[images]

# Name that is show as card title
image1[title] = "Inverter"

# additional description shown below picture
image1[description] = "Inverter + Powermeter"

# the image itself, either the name of the file in folder /images (casesensitive incl. extension e.g. "image1.jpg"
# or external URL e.g. "https://upload.wikimedia.org/wikipedia/commons/7/71/Sun_Earth_Comparison.png"
image1[uri] = "image1.jpg"

image2[title] = "Roof"
image2[description] =
image2[uri] = "image2.jpg"

image3[title] = "internet"
image3[description] = "source: wikipedia"
image3[uri] = "https://upload.wikimedia.org/wikipedia/commons/7/71/Sun_Earth_Comparison.png"

###############################################################################
#  This section specifies parameters to configure use of WEEWX (https://weewx.com/)
#  if you have a weatherstation running with WEEWX  you can use temperature data to be displayed
#  in the day chart. To use weewx data, set option enabled to "true" and specify additional parameters in this section
#  default = false
###############################################################################
[weewx]
enabled = false

# Database host of your WEEWX database, can be different to the zonPHP database
host = localhost

# The user name for logging in to the WEEWX host database
username = weewx

# The password of the  WEEWX host database (use quotes to guard against parsing errors)
password = weewx

# The WEEWX database name
database = weewx

# Tablename which containd the weewx data
# default "archive"
tableName = archive

# Name of the column that contains temperature data
tempColumn = outTemp

# Name of the column that contains timestamp
timestampColumn = dateTime

# Are temperature values stored in Fahrenheit
# default = true
tempInFahrenheit = true

###############################################################################
#   This section specifies parameters if you use EMU
# only relevant if enabled is set to true
# If you use EMU, set "importer" to "none"
###############################################################################
[EMU]
enabled = false

path_CSV_data =

PVO_API =

PVO_SYS_ID =
