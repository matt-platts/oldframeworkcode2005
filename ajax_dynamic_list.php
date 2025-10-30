<?
require_once("library/database.php");

if(isset($_GET['getCountriesByLetters']) && isset($_GET['letters'])){
	$letters = $_GET['letters'];
	$letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
	$res = mysql_query("select ID,countryName from ajax_countries where countryName like '".$letters."%'") or die(mysql_error());
	while($inf = mysql_fetch_array($res)){
		echo $inf["ID"]."###".$inf["countryName"]."|";
	}	
}
?>
