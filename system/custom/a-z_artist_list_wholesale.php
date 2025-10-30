<?php
global $current_site;
if ($current_site['http_path']){
	$base_path=trim($current_site['http_path']);
	$base_path .= "/";
}
$az_output_text = "<ul>";
$let=65;
$let=97;
$count=1;
do {
	$az_output_text .= "<li class=\"artist_az_link\"><a href=\"/site.php?s=5&mt=2029&action=list_table&t=artists&filter_id=220&dbf_az_filter_value=".chr($let)."&active_az_filter=1\" id=\"a".$count."\">".chr($let)."</a></li>\n";
	$let++;
	$count++;
} while ($let<123);
$az_output_text .= "</ul>";

print $az_output_text;

?>
