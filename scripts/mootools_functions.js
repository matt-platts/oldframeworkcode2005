// 
//------------------- -horizontal filter slide in
//
//var filterSlide= new Fx.Slide('page_filters', {mode: 'vertical'});

// the following 4 lines hide it at the start and stop the flickering effect that can come with doing this in some browsers 
//document.getElementById('page_filters').style.visibility = "hidden";
//document.getElementById('page_filters').style.display = "block";
//filterSlide.hide();
//document.getElementById('page_filters').style.visibility = "visible";

// here's tha actual function that attaches the event to a button
//$('toggle_filters_button').addEvent('click', function(e){
//	e = new Event(e);
//	filterSlide.toggle();
//	e.stop();
//});
