<?
/* 
 * File: autodoc.php
 * Meta: Incomplete, but provides some general system documentation automatically by reading the tables and scripts and pulling parts out
*/

function list_all_functions($directory){
	open_col2();
	$count=0;
	$dir = opendir($directory); #open directory
	print "<div class=\"table_title\">Listing all functions in directory: $directory</div><div id=\"cleardiv\"></div><br clear=\"all\" />";
	while ($f = readdir($dir)) { #read one file name
		if (preg_match("/^\./",$f)){continue;}
		print "<p><b>$f</b><br>\n";
		if (strlen(strpos($f,".php"))){
			$openfile=fopen("$directory/$f","r");
			$file_contents=fread($openfile, filesize("$directory/$f"));
			fclose($openfile);
			$file_contents=explode("\n",$file_contents);
			foreach ($file_contents as $line){
				if (preg_match("/^function /",$line)){
					$line = preg_replace("/{/","",$line);
					$line = preg_replace("/^function /","",$line);
					print "\t      " . $line . "<br>\n";
				}
			$count++;
			}
		}
	}
	print "<p><b>Total lines searched:</b> $count";
	close_col();
}

?>
