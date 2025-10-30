<?php
/*
   Copyright 2009 Bernard Peh

   This file is part of PHP Quick Calendar.

   PHP Quick Calendar is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   PHP Quick Calendar is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with PHP Quick Calendar.  If not, see <http://www.gnu.org/licenses/>.
*/


// CONFIGURE WEB LOCATION FROM ROOT
define(QCALENDAR_WEB_PATH,'/software/plugins/qcalendar/app');

// CONFIGURE DB ACCESS
$dbhost = 'localhost';
$dbuser = 'jcmnewuser';
$dbpass = 'jcmnewpass';
$database = 'jcmnew';

// CONFIGURE MAIN TABLE
define(QCALENDAR_TABLE,'qcalendar');

// CONFIGURE CATEGORY TABLE
define(QCALENDAR_CAT_TABLE,'qcalendar_category');

// END OF CONFIGURATION. NOTHING NEEDS TO BE DONE BEYOND THIS POINT.

// start connecting to db
$dbConnect = mysql_connect($dbhost, $dbuser, $dbpass);
if (!$dbConnect) {
   die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db($database, $dbConnect);
if (!$db_selected) {
   die ('db selection error : ' . mysql_error());
}
?>
