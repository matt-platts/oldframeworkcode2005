// draglayer
// this goes into the very bottom of the admin screens and sets up the divs for draggable properties

// the following line should be changed depending on what we want draggable
// note that setting col2 to draggable means you cant select the text in it, which is a shame. Have to see if there's a way round this
SET_DHTML("titlebar"+CURSOR_MOVE, "menu"+SCALABLE, "frame"+NO_DRAG, "clientarea"+NO_DRAG, "resizehandle","resizebutton"+VERTICAL+HORIZONTAL);
//SET_DHTML("titlebar"+CURSOR_MOVE, "col2"+SCALABLE, "menu"+SCALABLE, "frame"+NO_DRAG, "clientarea"+NO_DRAG, "resizehandle","resizebutton"+VERTICAL+HORIZONTAL);

// Some vars to customize window:
var frame_padding = 0;
var titlebar_h = 19;
var toolbar_h = 20;
var statusbar_h = 20;
var clientarea_margin = 4;

// preload button images to ensure un-delayed image swapping    
var button_down_outset = new Image();
var button_down_inset = new Image();
var button_up_outset = new Image();
var button_up_inset = new Image();
button_down_outset.src = 'system/graphics/dragdrop/button_down_outset.gif';
button_down_inset.src = 'system/graphics/dragdrop/button_down_inset.gif';
button_up_outset.src = 'system/graphics/dragdrop/button_up_outset.gif';
button_up_inset.src = 'system/graphics/dragdrop/button_up_inset.gif';

// to save window height when window is minimized
var last_window_h;

// initWindow() moves elements to their adequate locations
// and builds coherences between these elements by converting outer frame, client area and images for resize functionalities
// to 'childern' of the draggable titlebar 
function initWindow()
{
    dd.elements.titlebar.moveTo(dd.elements.frame.x+2+frame_padding, dd.elements.frame.y+2+frame_padding);
    dd.elements.titlebar.addChild("frame");
    dd.elements.titlebar.setZ(dd.elements.frame.z+1); // ensure that titlebar is floating above frame
    dd.elements.titlebar.resizeTo(dd.elements.frame.w-4-(frame_padding<<1), titlebar_h);

	frame_padding=0;
	clientarea_margin=0;
	clientarea_margin_h=-20;

    dd.elements.clientarea.moveTo(dd.elements.frame.x+2+frame_padding+clientarea_margin, dd.elements.titlebar.y+titlebar_h+toolbar_h+clientarea_margin_h);
    dd.elements.titlebar.addChild("clientarea");
    dd.elements.clientarea.resizeTo(dd.elements.frame.w-4-(frame_padding<<1)-(clientarea_margin<<1), dd.elements.frame.h-titlebar_h-toolbar_h-statusbar_h-4-(frame_padding<<1)-clientarea_margin);

    dd.elements.resizehandle.moveTo(dd.elements.frame.x+dd.elements.frame.w-dd.elements.resizehandle.w-2, dd.elements.frame.y+dd.elements.frame.h-dd.elements.resizehandle.h-2);
    dd.elements.resizebutton.moveTo(dd.elements.titlebar.x+dd.elements.titlebar.w-dd.elements.resizebutton.w-frame_padding-(titlebar_h>>1)+Math.round(dd.elements.resizebutton.w/2), dd.elements.titlebar.y+Math.round(titlebar_h/2)-Math.round(dd.elements.resizebutton.h/2));
    dd.elements.titlebar.addChild("resizebutton");
    dd.elements.titlebar.addChild("resizehandle");
    
    dd.elements.titlebar.show();
}
initWindow();

// my_PickFunc, my_DragFunc and my_DropFunc override their namesakes in wz_dragdrop.js
function my_PickFunc(){
    if (dd.obj.name == "resizebutton")
    {
        dd.obj.swapImage(dd.elements.clientarea.visible? button_up_inset.src : button_down_inset.src);
    }
}

function my_DragFunc(){
    if (dd.obj.name == "resizehandle")
    {
        dd.elements.frame.resizeTo(dd.obj.x-dd.elements.frame.x+dd.obj.w+2, dd.obj.y-dd.elements.frame.y+dd.obj.h+2);
        dd.elements.titlebar.resizeTo(dd.obj.x-dd.elements.titlebar.x+dd.obj.w-frame_padding, titlebar_h);
        dd.elements.clientarea.resizeTo(dd.elements.frame.w-4-(frame_padding<<1)-(clientarea_margin<<1), dd.elements.frame.h-titlebar_h-toolbar_h-statusbar_h-4-(frame_padding<<1)-clientarea_margin);
        dd.elements.resizebutton.moveTo(dd.elements.titlebar.x+dd.elements.titlebar.w-dd.elements.resizebutton.w-frame_padding-(titlebar_h>>1)+Math.round(dd.elements.resizebutton.w/2), dd.elements.resizebutton.y);
    }
	alert("Dropped it");
}

function my_DropFunc(){
    if (dd.obj.name == "resizebutton")
    {
        if (dd.elements.clientarea.visible)
        {
            dd.obj.swapImage(button_down_outset.src);
            dd.elements.clientarea.hide();
            dd.elements.resizehandle.hide();
            last_window_h = dd.elements.frame.h;
            dd.elements.frame.resizeTo(dd.elements.frame.w, titlebar_h+(frame_padding<<1)+4);
        }
        else
        {
            dd.obj.swapImage(button_up_outset.src);
            dd.elements.clientarea.show();
            dd.elements.resizehandle.show();
            dd.elements.frame.resizeTo(dd.elements.frame.w, last_window_h);
        }
    }
}

