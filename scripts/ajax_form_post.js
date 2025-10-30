// JavaScript Document
dbf_loaderimg= new Image(16, 16);
dbf_loaderimg.src="ajax-loader.gif";

window.addEvent('domready', function() {

	$('update_table').addEvent('submit', function(e) {
		// Prevents the default submit event from loading a new page.
		e.stop();

		// Show the spinning indicator when pressing the submit button...
		$('ajax_loading').setStyle('display','block');

		// Hide the submit button while processing...
		//$('submit').setStyle('display','none');

		// Set the options of the form's Request handler.
		// ("this" refers to the $('login') element).
		this.set('send', { onComplete: function(response) {
			$('ajax_loading').setStyle('display','none');

	if(response == 'OK') {
		$('col2').set('html', '<div id="logged_in">Response Made<br />' + '<img align="absmiddle" src="loader-bar.gif" height="25">' + '<br /> Please wait while we redirect you to your page...</div>');

        //setTimeout('go_to_private_page()', 3000);
	} else {
		$('col2').set('html', response);
        }
		}});

		// Send the form.
		this.send();
	});
});

function go_to_private_page() {
	window.location = 'private.php'; // Members Area
}
