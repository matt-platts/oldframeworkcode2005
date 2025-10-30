<!-- START SCRIPT

if (document.images) {

nexton = new Image();
nexton.src = "picts/nexton.gif"

nextoff = new Image();
nextoff.src = "picts/nextoff.gif"

}

function img_act(imgName) {
if (document.images) {
imgOn = eval(imgName + "on.src");
document [imgName].src = imgOn;
}
}

function img_inact(imgName) {
if (document.images) {
imgOff = eval(imgName + "off.src");
document [imgName].src = imgOff;
}
}


// -->