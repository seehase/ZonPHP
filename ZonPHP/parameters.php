<?php

/******************************************************************************
 * Information
 ******************************************************************************

installationDate = 2011-11-10
plants = SEEHASE, TILLY
plantskWp = 5040, 3000
defaultLanguage = de
userTheme = DarkGreyFire
displayInterval = 5  ;allowed:  1,2,3,4,5,6,10,12,15,20,30,60 sample  xls = 10, SolarLog = 5, SIC = 4
coefficient = 1
importer = sunny_explorer_seehase
autoReload = 0
useWeewx = false
emu = false
hideFooter = false
hideMenu = false
checkVersion = true
googleTrackingId = G-XCE1BK2ZX9

[layout]
cards = "day, month, year, allYears, cumulative, yearPerMonth,  farm, image, top, plants"

[database]
host =  localhost
username = root
password = root
database = solar
tablePrefix = tgeg

[SEEHASE]
name = Seehase
installationDate = 2011-11-10
capacity = 5040
importPrefix = seehase
image = "image1.jpg"
expectedYield = 180, 245,460, 640,645,645,675,635,510,375,215,185
website = https://solar.seehausen.org
panels = "5040Wq = 21*Trina TSM-240 PC05 Poly"
inverter = "SMA SB 5000TL20 ESS"
orientation = "180 Grad 30 Grad Neigung"
location = Ingolstadt

[TILLY]
name = Tillman
installationDate = 2012-10-01
capacity = 3000
importPrefix = tilly
image = "image2.jpg"
expectedYield = 175,272,395,491,573,542,603,546,443,332,193,131
website = https://solar.seehausen.org
panels = "3000Wq = 10* panels"
inverter = "SMA SB 3000TL10"
orientation = "180 Grad 30 Grad Neigung"
location = Ingolstadt

[farm]
name = ZonPHP Seehausen Solar
website = https://solar.seehausen.org
location = Ingolstadt
totalCapacity = 8040 kWp

[images]
image1[title] = "Wechselrichter"
image1[description] = "Wechselrichter + Stromzähler"
image1[uri] = "image1.jpg"

image2[title] = "Dach"
image2[description] =
image2[uri] = "image2.jpg"

image3[title] = "internet"
image3[description] = "source: wikipedia"
image3[uri] = "https://upload.wikimedia.org/wikipedia/commons/7/71/Sun_Earth_Comparison.png"

[weewx]
server = localhost
username = weewx
password = weewx
database = weewx
tableName = archive
tempColumn = outTemp
timestampColumn = dateTime
tempInFarenheit = true

[EMU]
path =
offset =
webRoot =
zonPhp =
PVO_API =
PVO_SYS_ID =

------------------------------------------------*/

