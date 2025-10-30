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
				var kwicks2 = $$('#kwick2 .kwick'); 
				var kwicks3 = $$('#kwick3 .kwick');
				var kwicks4 = $$('#kwick4 .kwick');
				var kwicks5 = $$('#kwick5 .kwick');
				var kwicks6 = $$('#kwick6 .kwick');
				var kwicks7 = $$('#kwick7 .kwick');
				var kwicks8 = $$('#kwick8 .kwick');

				var fx = new Fx.Elements(kwicks, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx2 = new Fx.Elements(kwicks2, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx3 = new Fx.Elements(kwicks3, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx4 = new Fx.Elements(kwicks4, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx5 = new Fx.Elements(kwicks5, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx6 = new Fx.Elements(kwicks6, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx7 = new Fx.Elements(kwicks7, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				var fx8 = new Fx.Elements(kwicks8, {wait: false, duration: 250, transition:Fx.Transitions.Cubic.easeOut});
				
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

				// and now the same for kwicks 6
				kwicks6.each(function(kwick6, i6){
					start_widths[i6] = kwick6.getStyle('width').toInt();
					kwick6.addEvent('mouseenter', function(e){
						var obj6 = {};
						obj6[i6] = {
							'width': [kwick6.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks6.each(function(other6, j6){
							if (other6 != kwick6){
								var w6 = other6.getStyle('width').toInt();
								if (w6 != squeeze_to) obj6[j6] = {'width': [w6,squeeze_to] };
							}
						});
						fx6.start(obj6);
					}
					);
				});


				kwicks7.each(function(kwick7, i7){
					start_widths[i7] = kwick7.getStyle('width').toInt();
					kwick7.addEvent('mouseenter', function(e){
						var obj7 = {};
						obj7[i7] = {
							'width': [kwick7.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks7.each(function(other7, j7){
							if (other7 != kwick7){
								var w7 = other7.getStyle('width').toInt();
								if (w7 != squeeze_to) obj7[j7] = {'width': [w7,squeeze_to] };
							}
						});
						fx7.start(obj7);
					}
					);
				});


				kwicks8.each(function(kwick8, i8){
					start_widths[i8] = kwick8.getStyle('width').toInt();
					kwick8.addEvent('mouseenter', function(e){
						var obj8 = {};
						obj8[i8] = {
							'width': [kwick8.getStyle('width').toInt(), max_width]
						};
						var counter = 0;
						kwicks8.each(function(other8, j8){
							if (other8 != kwick8){
								var w8 = other8.getStyle('width').toInt();
								if (w8 != squeeze_to) obj8[j8] = {'width': [w8,squeeze_to] };
							}
						});
						fx8.start(obj8);
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
				// another repeat
				$('kwick6').addEvent('mouseleave', function(e){
					var obj6 = {};
					kwicks6.each(function(other, j6){
						obj6[j6] = {'width': [other.getStyle('width').toInt(), start_widths[j6]]};
					});
					fx6.start(obj6);
				});
				// another repeat
				$('kwick7').addEvent('mouseleave', function(e){
					var obj7 = {};
					kwicks7.each(function(other, j7){
						obj7[j7] = {'width': [other.getStyle('width').toInt(), start_widths[j7]]};
					});
					fx7.start(obj7);
				});
				// another repeat
				$('kwick8').addEvent('mouseleave', function(e){
					var obj8 = {};
					kwicks8.each(function(other, j8){
						obj8[j8] = {'width': [other.getStyle('width').toInt(), start_widths[j8]]};
					});
					fx8.start(obj8);
				});
				// end repeats
			}
		};
	
	window.addEvent('domready',Kwix.start);
