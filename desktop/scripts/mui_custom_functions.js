
	// The menu at the top calls this function to dynamically create windows from the dbf menu	
	MUI.dynMenuWindow = function(menuID,menuHref,menuText,newWinWidth,newWinHeight){
		menuHref=menuHref + "&jx=1&iframe=1&dbf_mui=1";
		var winHeight=520;
		var winWidth=1010;
		var availWinHeight = document.body.scrollHeight;
		if (availWinHeight<700){
			winHeight=availWinHeight-160;
		}

		if (newWinHeight){ winHeight=newWinHeight;}
		if (newWinWidth){ winWidth=newWinWidth;}

		new MUI.Window({
			id: menuText,
			title: menuText,
			loadMethod: 'iframe',
			contentURL: menuHref,
			width:winWidth,
			height:winHeight	
		});
	}

	// Recordsets
	MUI.editRecordsetWindow= function(){
		var winTitle = "Edit Recordset";
		var winURL = '../mui-administrator.php';
		new MUI.Window({
			id: 'editRecordsetWindow',
			title: winTitle, 
			loadMethod: 'iframe',
			contentURL: winURL,
			width:990,
			height:480
		});
	}

	// Imagepicker script
	MUI.imagePickerWindow = function(s_windowName,s_elementName){
		var winTitle = "Image Picker for field " + s_elementName + " in window " + s_windowName;
		var winURL = '../mui-administrator.php?action=image_selector&dir=./images&target_window=' + s_windowName + '&target_element=' + s_elementName;
		new MUI.Window({
			id: 'imagePicker',
			title: winTitle, 
			loadMethod: 'iframe',
			contentURL: winURL,
			width:650,
			height:380
		});
	}

	// This function will open a url in an iframe - not sure where or if this is called from at present.
	MUI.urlWindow = function(dynURL,dynID,dynTitle,winW,winH){
		var winHeight=winH;
		var availWinHeight = document.body.scrollHeight;
		if (availWinHeight<winH-100){
			winHeight=availWinHeight-160;
		}
		new MUI.Window({
			id: dynID,
			title: dynTitle,
			loadMethod: 'iframe',
			contentURL: dynURL,
			width:winW,
			height:winH
		}) 
	}

	/* Window to open pixlr app */ 
	MUI.pixlrDynWindow = function(imgSrc,imgTitle){
		pixlrDynUrl="plugins/pixlr/index.php?imgsrc=" + imgSrc + "&imgtitle=" + imgTitle;
		new MUI.Window({
			id: 'pixlrdynwindow',
			title: 'Image Editor',
			loadMethod: 'iframe',
			contentURL: pixlrDynUrl, 
			width:990,
			height:695,
			x:150,
			y:100
		});
	}

	// opens the mui administrator exactly as the normal admin - not used now we use table manager instead.
	MUI.database_administratorWindow = function(){
		new MUI.Window({
			id: 'database_administrator_integrated',
			title: 'Database Administrator - Home',
			loadMethod: 'iframe',
			contentURL: '../mui-administrator.php?action=display_admin_content_by_key_name&dbf_key_name=admin_home_mui&iframe=1&jx=1&dbf_mui=1',
			width:610,
			height:265,
			x:150,
			y:100
		});
		MUI.notification('Loading Database Administrator...');
	}

	/* The products menu */
	MUI.database_productsWindow = function(){
		var winHeight=590;
		var availWinHeight = document.body.scrollHeight;
		if (availWinHeight<700){
			winHeight=availWinHeight-160;
		}
		new MUI.Window({
			id: 'database_products_menu',
			title: 'Product Catalogue Menu',
			loadMethod: 'iframe',
			contentURL: '../mui-administrator.php?action=display_admin_content_by_key_name&dbf_key_name=admin_product_menu_page&iframe=1&jx=1&dbf_mui=1',
			width:240,
			height:winHeight,
			x:950,
			y:100
		});
	}

	/* Initial login window */
	MUI.loginWindow = function(){
		new MUI.Window({
			id: 'login',
			title: 'Please Log In ',
			loadMethod: 'xhr',
			contentURL: 'plugins/login/index.html',
			width:305,
			height:110,
			x:230,
			y: 180,
			padding: { top:12, right:12, bottom:10, left: 12},
			resizable: false,
			maximizable: false,
		});	
	}

	/* Been logged ot */
	MUI.xhrloginWindow = function(){
		new MUI.Modal({
			id: 'xhrlogin',
			title: 'You have been logged out due to inactivity',
			loadMethod: 'xhr',
			contentURL: 'plugins/login/indexXHR.html',
			width:305,
			height:110,
			x:230,
			y: 180,
			padding: { top:12, right:12, bottom:10, left: 12},
			resizable: false,
			type: 'modal',
			cls: false,
			maximizable: false,
		});	
	}

	/* Welcome message window */
	MUI.welcomeWindow = function(){
		new MUI.Window({
			id: 'welcome',
			title: 'Welcome',
			loadMethod: 'xhr',
			contentURL: 'plugins/welcome/index.php',
			width:305,
			height:110,
			x:230,
			y: 180,
			padding: { top:12, right:12, bottom:10, left: 12},
			resizable: false,
			maximizable: false,
			onContentLoaded: function(windowEl){
				if ($('system_messages')){
					MUI.notification('You have new messages.');
					$('system_messages').addEvent('click', function(e){
						new Event(e).stop();
						MUI.dynMenuWindow(this.id,this.href,this.text);
					});
				}
			}
		});	
	}


