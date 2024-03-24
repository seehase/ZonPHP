# ZonPHP
## PHP Solar Logger

Based on the initial version from Slaper and Rally 
I have continued and enhanced the project. The current version supports PHP8 and can be used
on mobiles and tablets as well.

![index.png](resources%2Findex.png)

Major changes in release 4 is the replacement of highchart with chart.js ( https://www.chartjs.org/)

If you have any feedback or questions, please contact me at https://github.com/seehase/ZonPHP/issues

### Day-View
![img|320x271](resources%2Fday_view.png)
![img.png](img.png)


### All years per month view
![all_years.png](resources%2Fall_years.png)



## Installation
### Download
* Last stable version [download](https://github.com/seehase/ZonPHP/archive/master.zip)

### Setup
* copy all files from folder "ZonPHP" to your website

### Database
* Setup your database (mysql or mariadb)

### Configuration
* Edit the file "parameters.php"

All configuration is done in the file "parameters.php". 
Use your preferred editor to make the change in this file to configure your installation. 
All parameters are described in details, if values are not set, default values are used.

In section [database] specify your DB connection, all tables will be created automatically on first start

If you want to check your configuration, please navigate to page "validate.php"   
"your site/validate.php"

All problems and warnings will be shown on this page with a hint how to solve

## Feedback / issues
If you have any question or want to provide feedback please use gitHub issues
https://github.com/seehase/ZonPHP/issues
