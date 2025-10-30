function swapTab(tabObj,toShow){
        for (i=1;i<14;i++){
                if (i==toShow){
                        evalStr="document.getElementById('formpart_" + i + "').style.display='block'";
                        eval(evalStr);
                } else {
                        evalStr="document.getElementById('formpart_" + i + "').style.display='none'";
                        eval(evalStr);
                }
        }
optionTabs=document.getElementById('dbfOptionTabs');
fullname="tabitem" + toShow;
for (i=0;i<optionTabs.childNodes.length;i++){
	thisId=optionTabs.childNodes[i].id
	if (thisId){
		if (thisId.match("tabitem") && thisId==fullname){
			optionTabs.childNodes[i].className="selected";
		} else if (thisId.match("tabitem")){
			optionTabs.childNodes[i].className="";
		}
	}
}
//selected: function(el, parent){
//$(parent).getChildren().each(function(listitem){
//listitem.removeClass('selected');
//});
//el.addClass('selected');
//}

}

