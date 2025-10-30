// Ajax Code
window.addEvent('domready', function(){
$('preview_selector').addEvent('change', function(e) {
e = new Event(e).stop();
var selValue=document.forms['previewForm'].preview_selector[document.forms['previewForm'].preview_selector.selectedIndex].value;
var url = "ajax/ajax_previewdiv.php?table="+selValue;
showPreviewBlock();
var myRequest = new Request({method: 'get', url: url,
                 onSuccess: function(txt){
                         $('section_preview').set('html', txt);
                 },
                 onFailure: function(){
                         $('section_preview').set('text', 'The ajax request failed.');
                 }
         });
         myRequest.setHeader('Content-type','text/html');
         myRequest.send();
});
}); 


function ajaxOnDivLoad(){
	textareas=document.getElementsByTagName("textarea");
	for (i=0;i<textareas.length;i++){
		toggleEditor(textareas[i].id);
	}
	return; 
}

function divIsLoaded(id, load){
	if (!load&&document.getElementById(id)){
		document.getElementById(id).id='';
		return;
	}
	else if (load&&document.getElementById(id)){
		if (id=='unique_1') //optional
		ajaxOnDivLoad(); //required
		return;
	}
	else if (load&&!document.getElementById(id))
	setTimeout("divIsLoaded('"+id+"', 'load')", 60);
}
