function blockError(){return true;}
window.onerror = blockError;

// SOMETHING IS USED HERE FOR THE GONZO TV BUSINESS






// NOTE: If you use a ' add a slash before it like this \'

// ONLY USE lowercase FOR ALL OPTIONS
var color		= "000000"		// HEADER BACKGROUND COLOR
var flashheight		= "100"			// HEIGHT OF THE FLASH (IN PIXELS) (and adjust the menu.js)
var flashwidth		= "600"			// WIDTH OF THE FLASH (IN PIXELS) (and adjust the menu.js)

var showimage 		= "no"	 		// SHOW THE SMALL IMAGE ON THE HEADER RIGHT SIDE
var imaglink		= "contact.htm"		// SMALL IMAGE PAGE LINK
var imagespace		= "150"			// IMAGE PLACEMENT

// YOU DO NOT NEED TO EDIT BELOW THIS LINE
		var Kwix = {
			
			start: function(){
				Kwix.parseKwicks();
			},
			
			parseKwicks: function(){
				
				var squeeze_to = 100;
				var max_width = 285;
				
				//get original widths
				var start_widths = new Array();
				var kwicks = $$('#kwick .kwick');
				//var fx = new Fx.Elements(kwicks, {wait: false, duration: 250, transition: Fx.Transitions.quadOut});
				var fx = new Fx.Elements(kwicks, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				kwicks.each(function(kwick, i){
					
					start_widths[i] = kwick.getStyle('width').toInt();
					
					kwick.addEvent('mouseenter', function(e){
						
						var obj = {};
						obj[i] = {
							'width': [kwick.getStyle('width').toInt(), max_width]
						};
						
						var counter = 0;
						
						kwicks.each(function(other, j){
							if (other != kwick){
								var w = other.getStyle('width').toInt();
								if (w != squeeze_to) obj[j] = {'width': [w,squeeze_to] };
							}
						});
						fx.start(obj);
					}
					);
				});
				
				$('kwick').addEvent('mouseleave', function(e){
					var obj = {};
					kwicks.each(function(other, j){
						obj[j] = {'width': [other.getStyle('width').toInt(), start_widths[j]]};
					});
					fx.start(obj);
				});
			}
		};
	window.addEvent('domready',Kwix.start);

//  End -->
