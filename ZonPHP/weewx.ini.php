<?php
#######################################################################################################################
#   This section specifies parameters to configure use of WEEWX (https://weewx.com/)
#   if you have a weatherstation running with WEEWX  you can use temperature data to be displayed
#   in the day chart. To use weewx data, set option enabled to "true" and specify additional parameters in this section
#   default = false
#######################################################################################################################
/*
[weewx]
enabled = false

# Database connection host, username, password, database
host = localhost
username = weewx
password = weewx
database = weewx

# Tablename which contains the weewx data default "archive"
tableName = archive

# Name of the column that contains temperature data
tempColumn = outTemp

# Name of the column that contains timestamp
timestampColumn = dateTime

# Are temperature values stored in Fahrenheit default = true
tempInFahrenheit = true
