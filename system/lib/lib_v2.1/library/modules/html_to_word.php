<?php

$document=$_GET['document'];
//build the content for the dynamic Word document
//in HTML alongwith some Office specific style properties. 

$strBody="";
$strBody .= "<html " . 
    "xmlns:o='urn:schemas-microsoft-com:office:office' " .
    "xmlns:w='urn:schemas-microsoft-com:office:word'" . 
    "xmlns='http://www.w3.org/TR/REC-html40'>" .
    "<head><title>Time</title>";

//The setting specifies document's view after it is downloaded as Print
//instead of the default Web Layout

//    $strBody.="<!--[if gte mso 9]>" .  "<xml>" .  "<w:WordDocument>" .  "<w:View>Print</w:View>" .  "<w:Zoom>90</w:Zoom>" .  "<w:DoNotOptimizeForBrowser/>" .  "</w:WordDocument>" .  "</xml>" .  "<![endif]-->";

$strBody.="<style>" .
     "<!-- /* Style Definitions */" .
     "@page Section1" .
     "   {size:8.5in 11.0in; " .
     "   margin:1.0in 1.25in 1.0in 1.25in ; " .
     "   mso-header-margin:.5in; " .
     "   mso-footer-margin:.5in; mso-paper-source:0;}" .
     " div.Section1" .
     "   {page:Section1;}" .
     "-->" .
     "</style></head>";

$strBody.="<body lang=EN-US style='tab-interval:.5in'>" .
     "<div class=Section1>";
	

$main_content=file_get_contents("../user/id_1/documents/$document") or die ("Cannot get main content!");
$strBody .= $main_content;
$strBody .="</div></body></html>";

//Force this content to be downloaded 
//as a Word document with the name of your choice
$doc_filename=str_replace(".html",".doc",$document);

header("Content-type: application/msword");
header("Content-disposition: attachment; filename=$doc_filename");
print $strBody;
exit;
?>
