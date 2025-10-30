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
				var kwicks2 = $$('#kwick2 .kwick2'); 
				var kwicks3 = $$('#kwick3 .kwick3');
				var kwicks4 = $$('#kwick4 .kwick4');
				var kwicks5 = $$('#kwick5 .kwick5');

				var fx = new Fx.Elements(kwicks, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx2 = new Fx.Elements(kwicks2, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx3 = new Fx.Elements(kwicks3, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx4 = new Fx.Elements(kwicks4, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx5 = new Fx.Elements(kwicks5, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				
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
				
				// and now the same for kwicks 2
				kwicks2.each(function(kwick2, i2){
					start_widths[i2] = kwick2.getStyle('width').toInt();
					kwick2.addEvent('mouseenter', function(e){
						var obj2 = {};
						obj2[i2] = {
							'width': [kwick2.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks2.each(function(other2, j2){
							if (other2 != kwick2){
								var w2 = other2.getStyle('width').toInt();
								if (w2 != squeeze_to) obj2[j2] = {'width': [w2,squeeze_to] };
							}
						});
						fx2.start(obj2);
					}
					);
				});
				// end same for 2
				// and now the same for kwicks 3
				kwicks3.each(function(kwick3, i3){
					start_widths[i3] = kwick3.getStyle('width').toInt();
					kwick3.addEvent('mouseenter', function(e){
						var obj3 = {};
						obj3[i3] = {
							'width': [kwick3.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks3.each(function(other3, j3){
							if (other3 != kwick3){
								var w3 = other3.getStyle('width').toInt();
								if (w3 != squeeze_to) obj3[j3] = {'width': [w3,squeeze_to] };
							}
						});
						fx3.start(obj3);
					}
					);
				});
				// and now the same for kwicks 4
				kwicks4.each(function(kwick4, i4){
					start_widths[i4] = kwick4.getStyle('width').toInt();
					kwick4.addEvent('mouseenter', function(e){
						var obj4 = {};
						obj4[i4] = {
							'width': [kwick4.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks4.each(function(other4, j4){
							if (other4 != kwick4){
								var w4 = other4.getStyle('width').toInt();
								if (w4 != squeeze_to) obj4[j4] = {'width': [w4,squeeze_to] };
							}
						});
						fx4.start(obj4);
					}
					);
				});
				// and now the same for kwicks 5
				kwicks5.each(function(kwick5, i5){
					start_widths[i5] = kwick5.getStyle('width').toInt();
					kwick5.addEvent('mouseenter', function(e){
						var obj5 = {};
						obj5[i5] = {
							'width': [kwick5.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks5.each(function(other5, j5){
							if (other5 != kwick5){
								var w5 = other5.getStyle('width').toInt();
								if (w5 != squeeze_to) obj5[j5] = {'width': [w5,squeeze_to] };
							}
						});
						fx5.start(obj5);
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
				// another repeat
				$('kwick2').addEvent('mouseleave', function(e){
					var obj2 = {};
					kwicks2.each(function(other, j2){
						obj2[j2] = {'width': [other.getStyle('width').toInt(), start_widths[j2]]};
					});
					fx2.start(obj2);
				});
				// another repeat
				$('kwick3').addEvent('mouseleave', function(e){
					var obj3 = {};
					kwicks3.each(function(other, j3){
						obj3[j3] = {'width': [other.getStyle('width').toInt(), start_widths[j3]]};
					});
					fx3.start(obj3);
				});
				// another repeat
				$('kwick4').addEvent('mouseleave', function(e){
					var obj4 = {};
					kwicks4.each(function(other, j4){
						obj4[j4] = {'width': [other.getStyle('width').toInt(), start_widths[j4]]};
					});
					fx4.start(obj4);
				});
				// another repeat
				$('kwick5').addEvent('mouseleave', function(e){
					var obj5 = {};
					kwicks5.each(function(other, j5){
						obj5[j5] = {'width': [other.getStyle('width').toInt(), start_widths[j5]]};
					});
					fx5.start(obj5);
				});
			}
		};
	
	window.addEvent('domready',Kwix.start);
