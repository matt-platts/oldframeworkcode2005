<?php
// geoip


include("geoip/geoip.inc");

$gi = geoip_open("geoip/GeoIP.dat",GEOIP_STANDARD);

$countryname = geoip_country_name_by_addr($gi, $_SERVER['REMOTE_ADDR']);
$countrycode = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);

if ($countryname == "United States" || $countryname == "Canada"){
print "<div id=\"countryinfo\">";
print "<table><tr><td valign=\"top\"><img src=\"images/flag_usa.png\" style=\"margin-right:20px; float:left; vertical-align:top;\" /></td><td>";
print "<p><b>W</b>elcome to the Gonzo UK and European web site.</p>";
print "
<style type=\"text/css\">
.dbf_para_alert{
        background-image:url('../system/graphics/icons/exclamation.png');
        background-repeat:no-repeat;
        padding:3px;
        border: 0px #fff solid;
        background-color:transparent;
        padding-left:20px;
        color:red;
}
</style>
";
print "<p class=\"dbf_para_alert\" style=\"width:500px; float:right\">Your IP address indicates that you are browsing from: <b>$countryname</b>.</p>";
print "<p>Although you can purchase from here in UK Pounds (&pound;) you may wish to check out our US site with prices in $ at <a href=\"http://www.gonzomultimedia.com\">www.gonzomultimedia.com</a></p>";
print "</td></tr></table>";
print "</div>";
print "<br clear=\"all\">";
}
geoip_close($gi);
?>

