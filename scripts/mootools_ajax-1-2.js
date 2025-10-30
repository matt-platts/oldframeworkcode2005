
function generate_url(){
	g_url="administrator.php?action=generate_url&jx=1&";
	g_url = g_url + "content_id=" + document.forms['urlform'].elements['content_id'].value;
	g_url = g_url + "&template_id=" + document.forms['urlform'].elements['template_id'].value;
	g_url = g_url + "&site_id=" + document.forms['urlform'].elements['site_id'].value;
	alert ("sending request " + document.forms['urlform'].elements['site_id'].value);
	var myRequest = new Request({method: 'get', url: g_url, 
	onSuccess: function(txt){
			$('urldiv').set('html', txt);
		},
		onFailure: function(){
			$('urldiv').set('text', 'The ajax request failed.');
		}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
}

function loadPage(getUrl){
	currentUrl=window.location.pathname;
	var adminPattern = "/admin/";

	if (currentUrl.match(adminPattern)){ 
	//alert("admin pattern");
		getUrl="/admin/"+getUrl+"&jx=1";
	} else { 
	//alert ("not admin pattern " + currentUrl);
		getUrl="/"+getUrl+"&jx=1";
	}
	var myRequest = new Request({method: 'get', url: getUrl, 
	onSuccess: function(txt){
			$('col2').set('html', txt);
			
			// to ensure that multibox still works, we need to include these files again!
			window.addEvent('domready', function(){
						var box = new multiBox('mb', {
							overlay: new overlay()
						});

						var advanced = new multiBox('advanced', {
							overlay: new overlay(),
							descClassName: 'advancedDesc'
						});
					});
			// end multibox re-include code

		},
		onFailure: function(){
			$('col2').set('text', 'The ajax request failed.');
		}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
	//new Ajax(url, { method: 'get', update: $('col2') }).request(); 
}

function dbf_search_popup_open(t,f,c){
	getUrl="administrator.php?action=dbf_search_popup_open&t=" + t + " &f=" + f + "&c=" + c + "&jx=1&ipopup=1";
	var myRequest = new Request({method: 'get', url: getUrl, 
	onSuccess: function(txt){
			document.getElementById('dbf_search_popup').style.display="block";
			document.getElementById('dbf_search_popup').style.position="absolute";
			document.getElementById('dbf_search_popup').style.top="150px";
			document.getElementById('dbf_search_popup').style.left="250px";
	
			$('dbf_search_popup').set('html', txt);
			
			// to ensure that multibox still works, we need to include this lot again!
			window.addEvent('domready', function(){
						var box = new multiBox('mb', {
							overlay: new overlay()
						});

						var advanced = new multiBox('advanced', {
							overlay: new overlay(),
							descClassName: 'advancedDesc'
						});
					});
			// end multibox re-include code

		},
		onFailure: function(){
			$('col2').set('text', 'The ajax request failed.');
		}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
	//new Ajax(url, { method: 'get', update: $('col2') }).request(); 
}

function dbf_displayfields_popup_open(t,f,c){
	getUrl="administrator.php?action=dbf_displayfields_popup_open&t=" + t + " &f=" + f + "&c=" + c + "&jx=1&ipopup=1";
	var myRequest = new Request({method: 'get', url: getUrl, 
	onSuccess: function(txt){
			document.getElementById('dbf_search_popup').style.display="block";
			document.getElementById('dbf_search_popup').style.position="absolute";
			document.getElementById('dbf_search_popup').style.top="150px";
			document.getElementById('dbf_search_popup').style.left="250px";
	
			$('dbf_search_popup').set('html', txt);
			
			// to ensure that multibox still works, we need to include this lot again!
			window.addEvent('domready', function(){
					//	var box = new multiBox('mb', {
					//			overlay: new overlay()
					//	});

					//	var advanced = new multiBox('advanced', {
					//		overlay: new overlay(),
					//		descClassName: 'advancedDesc'
					//	});

    new Sortables('#table_fields_sortable UL', {
        clone: true,
        revert: true,
        opacity: 0.7
    });


					});
			// end multibox re-include code

	},
	onFailure: function(){
		$('dbf_search_popup').set('text', 'The ajax request failed.');
	}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
}

function dbf_imagePicker_popup_open(elementName,dir){
	
	if (!dir){ dir="./images"; }
	getUrl="mui-administrator.php?action=image_selector&dir="+dir+"&target_window=&target_element=" + elementName;
	var myRequest = new Request({method: 'get', url: getUrl, 
	onSuccess: function(txt){
			document.getElementById('dbf_imagePicker').style.display="block";
			document.getElementById('dbf_imagePicker').style.width="650px";
			document.getElementById('dbf_imagePicker').style.height="290px";
	
			$('dbf_imagePicker').set('html', txt);
			
		},
	onFailure: function(){
		$('dbf_imagePicker').set('text', 'The ajax request failed.');
		}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
}

function sendQuickEditMenuPosition(menutop,menuleft){
	menuUrl="ajax/savemenu.php?menu=quickedit&menutop=" + menutop + "&menuleft=" + menuleft;
	var myRequest = new Request({method: 'get', url: menuUrl, 
		onSuccess: function(txt){
			//$('col2').set('html', txt);
		},
		onFailure: function(){
			//$('col2').set('text', 'The ajax request failed.');
		}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
}

function sendDevMenuPosition(menutop,menuleft){
	menuUrl="ajax/avemenu.php?menu=dev&menutop=" + menutop + "&menuleft=" + menuleft;
	var myRequest = new Request({method: 'get', url: menuUrl, 
		onSuccess: function(txt){
			//$('col2').set('html', txt);
		},
		onFailure: function(){
			//$('col2').set('text', 'The ajax request failed.');
		}
	});
	myRequest.setHeader('Content-type','text/html');
	myRequest.send();
}

function removeFile(table,formfield,recordId, filterId){
	if(confirm("Are you sure you want to remove the attached " + formfield + " file for this record? Press ok to remove this file")){
	getUrl="administrator.php?action=ajax_remove_file_from_record&t="+table+"&f="+formfield+"&id=" + recordId + "&filter_id=" + filterId + "&jx=1";
	updateField="file_delete_response_" + formfield;
	var myRequest = new Request({method: 'get', url: getUrl,
		onSuccess: function(txt){
			//alert("updating div: " + updateField + " with : " + txt);
			$(updateField).set('html', txt);
		},
		onFailure: function(){
			$(updateField).set('html', 'The Ajax request failed');
		}
	});
	myRequest.send();
	//new Ajax(url, { method: 'get', update: $(updateField) }).request(); 
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

