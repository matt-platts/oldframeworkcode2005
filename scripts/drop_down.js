// drop_down.js
// ensures that the dynamic menu code works in IE6 

window.onload = function() {
  if (document.all&&document.getElementById) {
    var LIs = document.getElementsByTagName("li");
    for (var i=0; i<LIs.length; i++) {
      LIs[i].onmouseover=function() {
        this.className+=" over";
      }
      LIs[i].onmouseout=function() {
        this.className=this.className.replace(" over", "");
      }
    }
  }
}
