window.addEvent('domready', function() {
	
	//create our Accordion instance
	var myAccordion = new Accordion($('accordion'), 'p.toggler', 'div.element', {
		opacity: false,
		onActive: function(toggler, element){
			toggler.setStyle('color', '#1b2c67');
		},
		onBackground: function(toggler, element){
			toggler.setStyle('color', '#1b2c67');
		}
	});

});
