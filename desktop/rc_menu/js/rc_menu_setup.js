window.addEvent('domready', function() {
	$(document.body).getElements('div.mainPageContent').each(function(smartbox) {
		new CM.menu(smartbox, {
			links: [{
					libelle: 'Desktop',
					icon: '../system/graphics/icons/application.png',
					onclick: function() { alert('New !!'); },
					links: [{
							libelle: 'Change Theme',
							icon: '../system/graphics/icons/color_swatch.png',
							onclick: function() { alert('New Smartbox !!'); },
							links: [{
								       libelle: 'Default',
		    							onclick: function(){ MochaUI.Themes.init('default'); },
		    					},{
								       libelle: 'Charcoal',
		    							onclick: function(){ MochaUI.Themes.init('charcoal'); }
								
							}]
						},{
							libelle: 'Change Background',
							icon: '../system/graphics/icons/images.png',
							onclick: function() { MUI.urlWindow('../mui-administrator.php?action=set_mui_background&jx=1&iframe=1&dbf_mui=1','Background','Background',300,140); },
							shortcut: {control: true, key: 'g'}
						},{
							libelle: 'Display',
							icon: '../system/graphics/icons/images.png',
							onclick: function() { MUI.urlWindow('../mui-administrator.php?action=set_mui_display_options&jx=1&iframe=1&dbf_mui=1','Display Settings','Display Settings',300,140); },
						},{
							libelle: 'Reload Desktop',
							icon: '../system/graphics/icons/computer_go.png',
							onclick: function() { history.go(0); },
						}
					]
				},{
					libelle: 'Window',
					icon: '../system/graphics/icons/application.png',
					links: [{
							libelle: 'Cascade',
							icon: '../system/graphics/icons/application_cascade.png',
							onclick: function() { MUI.arrangeCascade(); }
						},{
							libelle: 'Tile',
							icon: '../system/graphics/icons/application_tile_horizontal.png',
							onclick: function() { MUI.arrangeTile(); }
						},{
							libelle: 'Minimize All',
							icon: '../system/graphics/icons/application_put.png',
							onclick: function() { MUI.minimizeAll(); }
						},{
							libelle: 'Close All',
							icon: '../system/graphics/icons/application_delete.png',
							onclick: function() { MUI.closeAll(); }
						},{
							libelle: 'Parametrics',
							onclick: function() { MUI.parametricsWindow(); }
						}

					]
				},{
					libelle: 'Close',
					icon: '../system/graphics/icons/cancel.png',
					onclick: function() { alert('Close Menu - No Item Selected'); },
					links: [{
							libelle: 'All Windows',
							icon: '../system/graphics/icons/application_delete.png',
							onclick: function() { MUI.closeAll(); }
						},{
							libelle: 'Log Out of Database',
							icon: '../system/graphics/icons/database_delete.png',
							onclick: function() { alert('Log Out of Database'); }
						},{
							libelle: 'Log Out of Desktop',
							icon: '../system/graphics/icons/key_delete.png',
							onclick: function() { location="index.php?action=process_log_out"; }
						}
					]
				}
			]
		});
	});
	
});
