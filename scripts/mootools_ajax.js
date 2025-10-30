
// Ajax Request
//window.addEvent('domready', function(){
//$('preview_button').addEvent('click', function(e) {
//e = new Event(e).stop();
//var url = "http://www.dmlmarketing.net/software/software_master/mattsajax.php";
//new Ajax(url, { method: 'get', update: $('section_preview') }).request(); });
//}); 

//window.addEvent('domready', function(){
//$('preview_templates_button').addEvent('click', function(e) {
//e = new Event(e).stop();
//var url = "http://www.dmlmarketing.net/software/software_master/mattsajax.php?table=templates";
//new Ajax(url, { method: 'get', update: $('section_preview') }).request(); });
//}); 

function generate_url(){
	url="administrator.php?action=generate_url&jx=1&";
	url = url + "content_id=" + document.forms['urlform'].elements['content_id'].value;
	url = url + "&template_id=" + document.forms['urlform'].elements['template_id'].value;
	url = url + "&site_id=" + document.forms['urlform'].elements['site_id'].value;
	new Ajax(url, { method: 'post', update: $('urldiv') }).request();
}

function loadPage(getUrl){
	var myRequest = new Request({method: 'get', url: getUrl, 
	onSuccess: function(txt){
			$('result').set('col2', txt);
		},
		onFailure: function(){
			$('result').set('col2', 'The ajax request failed.');
		}
	});
	myRequest.send();
	//new Ajax(url, { method: 'get', update: $('col2') }).request(); 
}

function removeFile(table,formfield,recordId, filterId){
	if(confirm("Are you sure you want to remove the attached " + formfield + " file for this record? Press ok to remove this file")){
	url="administrator.php?action=ajax_remove_file_from_record&t="+table+"&f="+formfield+"&id=" + recordId + "&filter_id=" + filterId + "&jx=1";
	updateField="file_delete_response_" + formfield;
	new Ajax(url, { method: 'get', update: $(updateField) }).request(); 
	}
}

function sendResult(game_id,win_or_lose){
	var url = "ajax_update_game_result.php?result="+win_or_lose+"&game="+game_id;
}

function previewContent(whichTable){
	alert(whichTable);
	var url = "administrator.php?table="+whichTable+"";
	new Ajax(url, { method: 'get', update: $('col2') }).request(); 
}

function sendResult(game_id,win_or_lose){
	var url = "ajax_update_game_result.php?result="+win_or_lose+"&game="+game_id;
	new Ajax(url, { method: 'get', update: $('log') }).request(); 
}

