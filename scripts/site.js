// site.js
// part of mysql_data_manager
// this script contains the main/general javascript functions

// very basic check on login form that both fields have been filled
function check_login() {
	if (document.forms['login_form'].elements['email_address'].value=="" || document.forms['login_form'].elements['password'].value==""){
		alert("Please enter both your email address and password to log in");
		return false;	
	}
return true;
}

// function for showing and hiding divs on the multi option pages
function show_hide_pagedivs(visible_id){
	for (i=1;i<8;i++){
	if (i != visible_id){
	eval('document.getElementById("page' + i + '").style.visibility="hidden"')
	} else {
	eval('document.getElementById("page' + i + '").style.visibility="visible"')
	}
	}
}

// extra precaution when deleting rows
function deleterow(rowid,child_records){
	continueDelete=0;
	if(confirm("Are you sure you want to delete this record?\n\nThis action is not undoable.")){
		if (child_records){
			if(confirm("Would you like to delete all child records of this master record?")){
				continueDelete=1;
			} else {
				continueDelete=0;
			}
		} else {
				continueDelete=1;	
		}
		if (continueDelete){
			document.forms['deleterow'].elements['deleteID'].value=rowid;
			document.forms['deleterow'].submit();	
		}
	}
	continueDelete=0;
}

// toggle the tinyMCE Rich Text Editor to act on and not act on a textarea. Call this function with the id of a textarea
function toggleEditor(id) {
if (!tinyMCE.get(id))
tinyMCE.execCommand('mceAddControl', false, id);
else
tinyMCE.execCommand('mceRemoveControl', false, id);
}

// the old editor toggle function for tiny mce version 2.x
var tinyMCEmode = true; // older code for tinyMCE2.0
function toggleEditorMode(sEditorID) {
    try {
        if(tinyMCEmode) {
            tinyMCE.removeMCEControl(tinyMCE.getEditorId(sEditorID));
            tinyMCEmode = false;
	    rewriteDiv = "toggle_" + sEditorID;
	    rewriteUrl = "<a style=\"font-size:9px;\" href=\"Javascript:toggleEditorMode('" + sEditorID + "')\">Style Editor</a>";
	    dynamiccontentNS6(rewriteDiv,rewriteUrl);
        } else {
            tinyMCE.addMCEControl(document.getElementById(sEditorID), sEditorID);
            tinyMCEmode = true;
	    rewriteDiv = "toggle_" + sEditorID;
	    rewriteUrl = "<a style=\"font-size:9px;\" href=\"Javascript:toggleEditorMode('" + sEditorID + "')\">Source Editor</a>";
	    dynamiccontentNS6(rewriteDiv,rewriteUrl);
        }
    } catch(e) {
       	alert("An error has occured: " + e); 
    }
}

function dynamiccontentNS6(elementid,content){
if (document.getElementById && !document.all){
rng = document.createRange();
el = document.getElementById(elementid);
rng.setStartBefore(el);
htmlFrag = rng.createContextualFragment(content);
while (el.hasChildNodes())
el.removeChild(el.lastChild);
el.appendChild(htmlFrag);
}
}

// Ajax methods
function ajaxFunction(ajaxVar,ajaxVarVal,fieldsInto){
var xmlHttp;
try { // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
catch (e) {
  // Internet Explorer
  try {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
    try {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
    catch (e) {
      alert("Your browser does not support AJAX!");
      return false;
      }
    }
  }

xmlHttp.onreadystatechange=function() {
    if(xmlHttp.readyState==4) {
      returnText = parseAjaxResponse(xmlHttp.responseText,fieldsInto);
      } else {
	returnText="";
	}
    }
  
  urlStr = "ajax/ajax.php?" + ajaxVar + "=" + ajaxVarVal
  xmlHttp.open("GET",urlStr,false);
  xmlHttp.send(null);
}

function parseAjaxResponse(returnedText,fieldsInto){
	//alert(returnedText + " into " + fieldsInto);
	document.forms[0].elements[fieldsInto].value=returnedText;
}

function ajaxGetDependents(which_field){
	//alert(which_field);
	var ajaxResult=ajaxFunction("getDependentFields",which_field,"carrier_field");
	//alert(ajaxResult);
}

function next_page(){
//	document.forms['list_records_filter'].elements['dbf_next'].value=eval((document.forms['list_records_filter'].elements['dbf_next'].value)+10);
	document.forms['list_records_filter'].elements['dbf_direction'].value="Up";
	document.forms['list_records_filter'].target="_self";
	document.forms['list_records_filter'].submit();
}

function previous_page(){
	document.forms['list_records_filter'].elements['dbf_direction'].value="Down";
	document.forms['list_records_filter'].target="_self";
	document.forms['list_records_filter'].submit();
}

function display_items() {
	document.forms['list_records_filter'].elements['dbf_direction'].value="Static";
	document.forms['list_records_filter'].elements['dbf_output_type'].value="";
	document.forms['list_records_filter'].target="_self";
	document.forms['list_records_filter'].submit();
}

function search_data(){
	document.forms['list_records_filter'].elements['dbf_direction'].value="";
	document.forms['list_records_filter'].elements['dbf_next'].value="";
	document.forms['list_records_filter'].elements['dbf_output_type'].value="";
	document.forms['list_records_filter'].target="_self";
	document.forms['list_records_filter'].submit();
}

function clear_search_filtering(){
//	if(confirm("Set all search and paging filtering to default values?")){
	document.forms['list_records_filter'].elements['dbf_direction'].value="";
	document.forms['list_records_filter'].elements['dbf_search_for'].value="";
	document.forms['list_records_filter'].elements['clear_filtering_post'].value=1;
	document.forms['list_records_filter'].submit();	
//	}
}

function clear_all_filtering(){
	if(confirm("Set all search and paging filtering to default values?")){
	document.forms['list_records_filter'].elements['dbf_direction'].value="Static";
	document.forms['list_records_filter'].elements['dbf_search_fields'].value="";

	if (document.forms['list_records_filter'].elements['dbf_data_filter_field']){
	document.forms['list_records_filter'].elements['dbf_data_filter_field'].value="";
	}
	if (document.forms['list_records_filter'].elements['dbf_data_filter_value']){
	document.forms['list_records_filter'].elements['dbf_data_filter_value'].value="";
	}
	if (document.forms['list_records_filter'].elements['dbf_data_filter_operator']){
	document.forms['list_records_filter'].elements['dbf_data_filter_operator'].value="";
	}
	document.forms['list_records_filter'].elements['dbf_search_for'].value="";
	document.forms['list_records_filter'].elements['clear_filtering_post'].value=1;
	document.forms['list_records_filter'].submit();	
	}
}

function edit_current_recordset(edit_recordset_url){
	document.forms['list_records_filter'].action=edit_recordset_url;
	document.forms['list_records_filter'].elements['dbf_direction'].value="Static";
	document.forms['list_records_filter'].submit();
}

function edit_current_recordset_mui(edit_recordset_url){
	parent.MUI.editRecordsetWindow();
	original_url=document.forms['list_records_filter'].elements['dbf_form_post_url'].value;
	document.forms['list_records_filter'].target="editRecordsetWindow_iframe";
	document.forms['list_records_filter'].action=edit_recordset_url;
	document.forms['list_records_filter'].elements['dbf_direction'].value="Static";
	document.forms['list_records_filter'].submit();
	document.forms['list_records_filter'].action=original_url;
	document.forms['list_records_filter'].target="_self";
}

// function which uses the javascript location to call a new page, written as i was already using too many quotes (" and ') and adding the location inline was getting too confusing
function goToUrl(toWhichURL){
	location=toWhichURL;
}

function showPreviewBlock(){
	document.getElementById('section_preview').style.display="block";
}

function deletePhoto(sFile,sDirectory,sDisplayOptions,sOptionsPosition){
	if(confirm("Are you sure you want to delete this photograph?")){
		locstring="administrator.php?action=file_browser&dt=" + sFile + "&d=" + sDirectory + " & display_options=" + sDisplayOptions + "&options_position=" + sOptionsPosition;
		location=locstring;
	}
}

function checkSurvey(){

  var radio_choice = false;

  for (counter = 0; counter < document.forms['surveyform'].response.length; counter++){
  if (document.forms['surveyform'].response[counter].checked)
  radio_choice = true; 
  }

  if (!radio_choice){
  alert("Please select a result before submitting your result - thanks!");
  } else {
  document.forms['surveyform'].submit();
  }
}

function fillOfficeFields(){
	selectedOffice=document.forms['add_product_to_cart'].elements['selectoffice'].options[document.forms.add_product_to_cart.elements.selectoffice.selectedIndex].value;
	officeData=offices_matrix[selectedOffice];
	officeDataArray=officeData.split("|");
	document.forms['add_product_to_cart'].elements['Business_Name'].value=officeDataArray[2];
	document.forms['add_product_to_cart'].elements['Division'].value=officeDataArray[3];
	document.forms['add_product_to_cart'].elements['Building_Name'].value=officeDataArray[4];
	document.forms['add_product_to_cart'].elements['Address'].value=officeDataArray[5];
	document.forms['add_product_to_cart'].elements['Town'].value=officeDataArray[6];
	document.forms['add_product_to_cart'].elements['Zip/Postcode'].value=officeDataArray[7];
	document.forms['add_product_to_cart'].elements['Country'].value=officeDataArray[8];
	document.forms['add_product_to_cart'].elements['Telephone'].value=officeDataArray[9];
	document.forms['add_product_to_cart'].elements['Direct_Telephone'].value=officeDataArray[12];
	document.forms['add_product_to_cart'].elements['Fax'].value=officeDataArray[10];
	document.forms['add_product_to_cart'].elements['Mobile'].value=officeDataArray[13];
	document.forms['add_product_to_cart'].elements['Email_Address'].value=officeDataArray[14];
	document.forms['add_product_to_cart'].elements['Web_Address'].value=officeDataArray[11];
	document.forms['add_product_to_cart'].elements['Job_Title'].value=officeDataArray[16];
	document.forms['add_product_to_cart'].elements['Name'].value=officeDataArray[15];
}

function deleteOfficeAssociation(o_id,u_id,lookup){
	if(confirm("Are you sure you want to remove this office from this users list?")){
		document.forms['remove_user_from_office'].elements['office_user'].value=lookup;	
		document.forms['remove_user_from_office'].elements['user_id'].value=u_id;	
		document.forms['remove_user_from_office'].elements['office_id'].value=o_id	
		document.forms['remove_user_from_office'].submit();
	}
}

function popUpDate(){
	document.forms['list_records_filter'].elements['active_date_filter'].value=1;
	document.getElementById("dateSearchPopup").style.display="block";
}

function popUpAZ(){
	document.forms['list_records_filter'].elements['active_az_filter'].value=1;
	document.getElementById("azSearchPopup").style.display="block";
}

function ajax_populate_field(fieldname,currentValue,currentName,querytype,rowid,filterid){
	//alert("Populating field " + fieldname + " from " + currentName);
	var populate_field_options=new Array();
	var populate_field_values=new Array();
	var url="mui-administrator.php?action=ajax_generate_options_list&top_value=" + currentValue + "&jx=1&ipopup=1&querytype=" + querytype + "&fieldname=" + fieldname + "&filter=" + filterid;
	ajax_list_options=new sack();
	ajax_list_options.requestFile=url;
	ajax_list_options.onCompletion=function(){
		// line below needs to differentiate between add and edit, so test for new_ or id_x to work out the correct field name (will need to use the value of x if it is edit)
		if (currentName.match("new_")){
			fieldname = "new_" + fieldname;	
		}
		if (currentName.match("id_")){
			old_fieldname=fieldname;
			fieldname = "id_" + rowid + "_";
			fieldname = fieldname + old_fieldname; 
		}
		//alert(ajax_list_options.response);
		var splitList=ajax_list_options.response.split(":::");
		// remove options
		  var elSel = document.getElementById(fieldname);
		  var i;
		  for (i = elSel.length - 1; i>=0; i--) {
		      elSel.remove(i);
		  }
		splitList[0]=splitList[0].replace("<p><b></b></p>","");
		for (i=0;i<splitList.length;i++){
			document.forms['update_table'].elements[fieldname].options[i]=new Option(splitList[i], splitList[i], true, false)
		}
	}	
	ajax_list_options.runAJAX();
	//ajax_showOptions(this,'t=$pass_table&idf=$pass_id_field&kf=$pass_key_field&jx=1&ajax_populate_dynamic_list',event)	
}

function ajax_populate_text_field(fieldname,currentValue,currentName,rowid,filterid){
	var url="mui-administrator.php?action=ajax_generate_text_field_value&top_value=" + currentValue + "&jx=1&pureAjax=1&fieldname=" + fieldname + "&filter=" + filterid;
	ajax_list_options=new sack();
	ajax_list_options.requestFile=url;
	ajax_list_options.onCompletion=function(){
		// line below needs to differentiate between add and edit, so test for new_ or id_x to work out the correct field name (will need to use the value of x if it is edit)
		if (currentName.match("new_")){
			fieldname = "new_" + fieldname;	
		}
		if (currentName.match("id_")){
			old_fieldname=fieldname;
			fieldname = "id_" + rowid + "_";
			fieldname = fieldname + old_fieldname; 
		}
		resulting_value=ajax_list_options.response;
		console.log(resulting_value + " FROM " + fieldname);
		document.forms['update_table'].elements[fieldname].value=resulting_value;
	}
	ajax_list_options.runAJAX();
}


function nosectionpreview(){
	document.getElementById("section_preview").style.display="none";
}

function getCheckedValue(radioObj) {
	if(!radioObj){ return "";}
	var radioLength = radioObj.length;
	if(radioLength == undefined) {
		if(radioObj.checked) {
			return radioObj.value;
		} else {
			return ""; }
	}
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function showYoutubeVideo(videoId){
	strEmbedCode='<object width="640" height="385"><param name="movie" value="http://www.youtube.com/v/' + videoId + '?fs=1&amp;hl=en_GB"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/' + videoId + '?fs=1&amp;hl=en_GB" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="385"></embed></object>';
	strEmbedCode = strEmbedCode + "<br /><a href=\"Javascript:hideYoutubeVideo('" + videoId + "')\">Hide Video</a>";
	divVideoId="youtube_" + videoId;
	document.getElementById(divVideoId).innerHTML=strEmbedCode;
}

function hideYoutubeVideo(videoId){
	divVideoId="youtube_" + videoId;
	document.getElementById(divVideoId).innerHTML="<a href=\"Javascript:showYoutubeVideo('" + videoId + "')\"><img src=\"http://i3.ytimg.com/vi/" + videoId + "/2.jpg\" border=\"0\"></a>";
}

function showYoutubeVideoSeparately(videoId){
	strEmbedCode='<object width="640" height="385"><param name="movie" value="http://www.youtube.com/v/' + videoId + '?fs=1&amp;hl=en_GB"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/' + videoId + '?fs=1&amp;hl=en_GB" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="385"></embed></object>';
	divVideoId="youtube_separate";
	document.getElementById(divVideoId).innerHTML=strEmbedCode;
}

function LuhnCheck(obj) {
        var cardnumber = obj.value;
        var re = /[^0-9]/g;
        cardnumber = cardnumber.replace(re, '');
        if (cardnumber.length == 0) return true;
        var store_card = cardnumber;
        var total = 0;
        var d = 0;
        while (cardnumber.length != 0) {
                total += parseInt(cardnumber.substring(cardnumber.length - 1, cardnumber.length));
                cardnumber = cardnumber.substr(0, cardnumber.length - 1);
                d = parseInt(cardnumber.substring(cardnumber.length - 1, cardnumber.length));
                cardnumber = cardnumber.substr(0, cardnumber.length - 1);
                d = isNaN(d) ? 0 : d;
                if (d < 9)
                        total += ((d * 2) % 9);
                else
                        total += 9;
        }
        if (total % 10 == 0) {
                return true;
        } else {
                return false;
        }
}

function showDiv(divname){
	document.getElementById(divname).style.display="block";
}

function hideDiv(divname){
	document.getElementById(divname).style.display="none";
}

