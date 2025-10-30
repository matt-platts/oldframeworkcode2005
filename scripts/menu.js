<!-- Begin HORIZONTAL CSS DROP MENU VER 1.50 2009

// NOTE: If you use a ' add a slash before it like this \'
// USE lowercase FOR ALL OPTIONS ONLY

var showtop		= "no"		//  SHOW TOP MENU IMAGE
var showimage		= "no"		//  SHOW BOTTOM MENU IMAGE

document.write('<div id="menulocation" class="printhide">');
   if (showtop == "yes") {
document.write('<img src="picts/menu-top.jpg" width="150" height="125" class="menutop"></a><br>');
}
document.write('<table cellpadding="0" cellspacing="0" border="0" width="150"><tr><td class="topmargin">');
document.write('<ul id="top-nav">');


// START MENU LINKS - EDIT BELOW THIS AREA
document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/index.html">Home</a></li>');

// MENU SEPARATOR 1
document.write('<li class="menuseparator"></li>');

document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/artists.html" class="parentM">Artists</a>'); 
document.write('    <ul id="sub-nav">');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/top_artists.html">Featured Artists</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/artists_a-z.html">Artists A-Z</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/new_artists.html">New Artists</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/top_artists.html">Top Selling Artists</a></li>');
document.write('    </ul>');
document.write('  </li>');

document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/labels.html" class="parentM">Labels</a>');
document.write('    <ul id="sub-nav">');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/labels.html">Labels A-Z</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/new_labels.html">New Labels</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/new_labels.html">Featured Labels</a></li>');
document.write('    </ul>');
document.write('  </li>');

document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/genres.html" class="parentM">Genres</a>');

document.write('  </li>');

// MENU SEPARATOR 2
document.write('<li class="menuseparator"></li>');

document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/latest_releases.html">Latest Releases</a></li>');
document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/forthcoming_releases.html">Forthcoming Releases</a></li>');
document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/specials.html">Specials</a></li>');

document.write('<li class="menuseparator"></li>');

document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/radio.html" class="parentM">Voiceprint Radio</a>');
document.write('    <ul id="sub-nav">');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/all_radio_shows.html">Browse Shows</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/latest_radio_shows.html">Latest Shows</a></li>');
document.write('    </ul>');
document.write('  </li>');



document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/voiceprint_tv.html" class="parentM">Voiceprint TV</a>');
document.write('    <ul id="sub-nav">');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/latest_videos.html">Latest Youtubes</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/search_videos.html">Browse Youtubes</a></li>');
document.write('    </ul>');
document.write('  </li>');


document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/newsprint_home.html" class="parentM">Newsprint</a>');
document.write('    <ul id="sub-nav">');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/newsprint_latest.html">Latest Issue</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/newsprint_search.html">Back Issues</a></li>');
document.write('    </ul>');
document.write('  </li>');






document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/gallery.html" class="parentM">Photo Gallery</a>');
document.write('    <ul id="sub-nav">');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/gallery.html">Gallery Index</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/gallery1.html">Gallery 1</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/gallery2.html">Gallery 2</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/gallery3.html">Gallery 3</a></li>');
document.write('      <li><a href="http://www.mattplatts.com/voiceprint/site/slideshow.html">Gallery Slideshow</a></li>');
document.write('    </ul>');
document.write('  </li>');










// MENU SEPARATOR 3
document.write('<li class="menuseparator"></li>');





document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/contact.html">Contact</a></li>');


document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/links.html" class="parentM">Links</a>');
document.write('  </li>');


// MENU SEPARATOR 3
document.write('<li class="menuseparator"></li>');





document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/help_and_support.html">Help &amp; Support</a></li>');
document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/about.html">About Voiceprint</a></li>');
document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/terms_and_conditions.html">Terms &amp; Conditions</a></li>');
document.write('  <li class="menuT"><a href="http://www.mattplatts.com/voiceprint/site/privacy_policy.html" class="parentM">Privacy Policy</a>');
document.write('  </li>');



// END LINKS //



document.write('</ul>');
document.write('</td></tr><tr><td align="center">');




// START MENU IMAGE


   if (showimage == "yes") {
document.write('<br><br><a href="http://www.mattplatts.com/voiceprint/site/index.html"><img src="picts/menu-image.jpg" width="150" height="75" border="0" class="borders-menuimage"></a><br><br><br>');
}

document.write('</td></tr></table>');
document.write('</div>');

//  End -->




// COPYRIGHT 2009 © Allwebco Design Corporation
// Unauthorized use or sale of this script is strictly prohibited by law

// YOU DO NOT NEED TO EDIT BELOW THIS LINE



function IEHoverPseudo() {

	var navItems = document.getElementById("top-nav").getElementsByTagName("li");
	
	for (var i=0; i<navItems.length; i++) {
		if(navItems[i].className == "menuT") {
			navItems[i].onmouseover=function() { this.className += " over"; }
			navItems[i].onmouseout=function() { this.className = "menuT"; }
		}
	}

}
window.onload = IEHoverPseudo;
