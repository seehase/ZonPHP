<?php

/******************************************************************************
 * Information
 ******************************************************************************


installationDate = 2011-11-10
plants = SEEHASE, TILLY
plantskWp = 5040, 3000
defaultLanguage = de
defaultTheme = DarkGreyFire
displayInterval = 5  ;allowed:  1,2,3,4,5,6,10,12,15,20,30,60 sample  xls = 10, SolarLog = 5, SIC = 4
coefficient = 1
importer = sunny_explorer_seehase
supportedLanguages = de, EN, fr ; values DE, FR, NL, EN default all
autoReload = 300
useWeewx = false
emu = false
hideFooter = false
hideMenu = false
checkVersion = true
googleTrackingId = G-XCE1BK2ZX9

[database]
host =  localhost
username = root
password = root
database = solar
tablePrefix = tgeg

[SEEHASE]
name = Seehase
capacity = 5040
importPrefix = seehase
image = "inc/image/image1.jpg"
expectedYield = 180, 245,460, 640,645,645,675,635,510,375,215,185

[TILLY]
name = Tillman
capacity = 3000
importPrefix = tilly
image = "inc/image/image2.jpg"
expectedYield = 175,272,395,491,573,542,603,546,443,332,193,131

[plant]
name = ZonPHP Seehausen Solar
website = https://solar.seehausen.org
panels = "5040Wq = 21*Trina TSM-240 PC05 Poly"
converter = "SMA SB 5000TL20 ESS"
orientation = "180 Grad 30 Grad Neigung"
location = Ingolstadt
totalCapacity = 8040 kWp

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

