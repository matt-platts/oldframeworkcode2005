// Search background

function searchBackgroundReplace(searchVal){
	document.getElementById("dbf_search_for").style.backgroundImage="url(images/search_background_clear.png)";
}

function searchBackgroundRevert(searchVal){
	if (!searchVal){
		document.getElementById("dbf_search_for").style.backgroundImage="url(images/search_background_init.png)";
	}
}

// Tab mouseover/outs
var override="500";
var display=new Array();
var display_orig=new Array();
var img=new Array("tab 1a cosmedix","tab 2a results rx","tab 3a skin nutrition","tab 4a societe","tab 5a mineral makeup","tab 7a whats new","tab 8a cosmetic surgery","tab 8a professional");
var imgorig=new Array("tab 1b cosmedix","tab 2b results rx","tab 3b skin nutrition","tab 4b societe","tab 5b mineral makeup","tab 7b whats new","tab 8b cosmetic surgery","tab 8b professional");

if(document.images){
	for(n=0;n<img.length;n++){
		display[n]=new Image;
		display[n].src="http://www.paragon-digital.net/medico_dev2/images/tabs/"+img[n]+".png"
		display_orig[n]=new Image;
		display_orig[n].src="http://www.paragon-digital.net/medico_dev2/images/tabs/"+imgorig[n]+".png"
	}
} else {for(n=0;n<img.length;n++){display[n]=""}}

function swap(position,source){

	if (override == position) {} else {
		if(document.images){
			imgName="menu_image_" + position;
			eval("document.images['" + imgName + "'].src=display_orig[source].src");
		}
	}
}

function swapout(position,source){
	if (override == position) {} else {
		if(document.images){
			imgName="menu_image_" + position;
			eval("document.images['" + imgName + "'].src=display[source].src");
		}
	}

}

function stick(objref,replace) {
	return;
//	if (document.images) {
//		for (n=4;n<10;n++) {
//			if (n!=objref) document.images[n].src=display_orig[n-4].src; else if (document.images) {document.images[objref].src="images/"+img[(n+1)]+".gif"
//		}
//	}override=objref} else return false
}
