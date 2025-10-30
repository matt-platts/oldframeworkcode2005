<?php

// http://www.maxmind.com/app/geolitecity

include("geoip.inc");

$gi = geoip_open("GeoIP.dat",GEOIP_STANDARD);

//echo geoip_country_code_by_addr($gi, "24.24.24.24") . "\t" .  geoip_country_name_by_addr($gi, "24.24.24.24") . "\n";
//echo geoip_country_code_by_addr($gi, "80.24.24.24") . "\t" .  geoip_country_name_by_addr($gi, "80.24.24.24") . "\n";
//echo geoip_country_code_by_addr($gi, "86.176.212.219") . "\t" .  geoip_country_name_by_addr($gi, "86.176.212.219") . "\n";

print "Your address is " . $_SERVER['REMOTE_ADDR'];
print "<br />";
print "You are in " . geoip_country_name_by_addr($gi, $_SERVER['REMOTE_ADDR']);

geoip_close($gi);

?>
