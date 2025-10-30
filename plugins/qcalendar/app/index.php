<?php
require_once('controller.php');
$cssCalendar= 'float:left;margin-right:30px;';
$cssLongDesc='float:left;margin-left:50px;width:400px';
// configure calendar theme
print "Calendar is below<br>";
initQCalendar('complex','qCalendarSmall', $cssCalendar, 'myContentSmall', $cssLongDesc, 0,0,0,0,0);
?>
