<?
global $user;
$name=$user->value('full_name');
if ($name){
print "<span class=\"menu_logintop\">Logged in as: $name. <a href=\"site.php?action=process_log_out\">Log Out</a></span>";
} else {
print "<span class=\"menu_logintop_notloggedin\"><a href=\"log_in.html\">Log In</a> | <a href=\"register.html\">Register</a></span>";
}
print "<br />";


?>

