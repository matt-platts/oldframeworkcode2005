<?php
require_once ("../../admin/config.php");
?>

	function loadPage(url,windowTitle,previewWindow,windowWidth,windowHeight,messageText){
		if (messageText){
			MUI.notification(messageText);
		}

		var req = new Request({
					method: 'get',
					url: '<?php echo HTTP_PATH;?>/mui-administrator.php?action=dbf_mui_check_login&jx=1&pureAjax=1',
					onRequest: function() {  },
					onFailure: function(xhr){
						alert("Call failed " + xhr);
					},
					onSuccess: function(response) {
					if (response=="ok"){
						var pattern=/mui-administrator/gi;
						var script_pattern=/.php/gi;
						var img_pattern=/.jpg/gi
						var admin_pattern=/administrator.php/gi

						if (previewWindow){
							if (!windowTitle){
								windowTitle="Preview Window";
							}
							url = "../" + url;
							newWinWidth=444;
							newWinHeight=290;
						}

						var availHeight=document.body.scrollHeight;
						if (availHeight>700){ defaultWinHeight=575; } else { defaultWinHeight=480;}
						if (windowHeight){ newWinHeight = windowHeight; } else { newWinHeight=defaultWinHeight;} // large screen 575
						if (windowWidth){ newWinWidth = windowWidth; } else { newWinWidth=1014;}
						var sizePattern=/dbf_mui_ws=(\d+x\d+)/gi;
						if (url.match(sizePattern)){
							var availWidth=document.body.scrollWidth; // not used yet
							winSize=RegExp.$1.split("x");
							newWinWidth=winSize[0];
							newWinHeight=winSize[1];
						}
						if(url.match(admin_pattern) && !url.match(pattern) && !previewWindow && url.charAt(0) != "/"){
							url = "../admin/mui-" + url + "&dbf_mui=1&jx=1&iframe=1";
						} else if (url.charAt(0)=="/"){
							url = url + "?dbf_mui=1";
						}
						if (!windowTitle){ windowTitle="Database Window"; }
						var randomnumber=Math.floor(Math.random()*1000000)
						var windowID='database_subwindow' + randomnumber;
						new MUI.Window({
							id: windowID,
							title: windowTitle,
							loadMethod: 'iframe',
							contentURL: url,
							width: newWinWidth,
							height: newWinHeight 
						});

				} else {
					MUI.notification("You must log in to access this content");
					MUI.xhrloginWindow();
				}
				 }
				}).send();

	}

	function doajaxLogin(){
                var req = new Request({
                        method: 'post',
                        url: '/admin/mui-administrator.php?action=process_login&jx=1&pureAjax=1',
                        data: {email_address: document.forms['XHRLoginForm'].elements['email_address'].value, password: document.forms['XHRLoginForm'].elements['password'].value, xhr:1},
                        onComplete: function(response) {
				if (response=="Login Success"){
					MUI.notification("You have been logged in",600,60);
					MochaUI.closeWindow($('xhrlogin'));
				} else {
					MUI.notification("Incorrect Details " + response);
				}
                        }
		}).send();
	}

	function pickImage(imgName,windowName,ElName){
		alert("Picking Image" + imgName + " to put in window " + windowName + " and el " + ElName);
		//$(windowName).retrieve('instance').maximize();
		
		if (windowName){ alert ("Got windowname"); } else { alert("no windowname"); }
		if (windowName.document){ alert ("Got a document"); } else { alert("no doc"); }
		if (windowName.document.body.innerHTML){ alert("inner is " + windowName.document.body.innerHTML); } else { alert ("Cant get windowname.documebt.body.innerHTML"); }
		var currentInstance = MochaUI.Windows.instances.get(windowName);
		alert(currentInstance);
//		alert(currentInstance.id);
//		alert(currentInstance.document.forms['update_table'].elements);
		alert(windowName.document.body.innerHTML);
		//windowName.document.forms['update_table'].elements[ElName].value=imgName;
	}

	function sendToPixlr(imgSrc,imgTitle){

		MUI.pixlrDynWindow(imgSrc,imgTitle);
	}


	function loadExternalPage(url){
		titletext=this.text;
		new MUI.Window({
			id: 'externalPage',
			title: 'Database Sub-Window',
			loadMethod: 'iframe',
			contentURL: url,
			width: 999,
			height: 575
		});
	}
