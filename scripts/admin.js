
// part of query builder 
function ajaxLoadTableFields(fieldsInto,initial_select_element){
	selectedTable=document.forms['query_builder'].elements[initial_select_element].value
	ajaxResult=ajaxFunction("table",selectedTable,fieldsInto);
	//alert("Table fields loaded. Press ok to continue");
	return ajaxResult;
}

// assumed part of query builder
function options_from_csv(allCsvs,selectFieldName){
	document.query_builder.elements[selectFieldName].options.length=0;
	returnString="";
	var splitList=allCsvs.split(":");
	for (i=0;i<splitList.length;i++){
		document.query_builder.elements[selectFieldName].options[i]=new Option(splitList[i], splitList[i], true, false)
	}
}

// part of querybuilder
function query_builder_select_options(destination_element,source_element,initial_select_element){
	ajaxLoadTableFields(source_element,initial_select_element);
	var csvVars="";
	do {
	csvVars=document.forms['query_builder'].elements[source_element].value;
	//if (!csvVars){
		alert("Loading.. please click to continue");
	//}
	} while (csvVars=="");
	options_from_csv(csvVars,destination_element);	
	document.forms['query_builder'].elements[source_element].value="";
}

// for ajax export in admin
function items_as_excel() {
	document.forms['list_records_filter'].elements['dbf_direction'].value="Static";
	document.forms['list_records_filter'].elements['dbf_output_type'].value="excel";
	document.forms['list_records_filter'].target="_blank";
	document.forms['list_records_filter'].submit();
}

// show sql in admin
function show_sql(){
	if (sql != ""){
		alert("This recordset was generated with the following sql:\n\n" + sql);
	}
}

////////////////// INTERFACE CREATOR //////////////////

// file manager
function file_rename(renameUrl,filename,directory,showpath){
	var newname=prompt("Please enter the new file name (don't forget to include the file extention):");
	if (newname){
		locstr=renameUrl + "?action=directory_browser_file_rename&dir=" + directory + "&filename="+filename+"&newname="+newname;
		if (showpath=="x"){
			locstr = locstr + "&showpath=x";
		}
		location=locstr;
	} else {
		alert("No name given.");
	}
}

function file_delete(deleteUrl,filename,directory,showpath){
	if (confirm("Are you sure you want to delete " + filename + "?\n\n\This action is not undoable and will permanently delete this file.")){
		locStr=deleteUrl + "?action=directory_browser_delete_file&dir=" + directory + "&dt=" + filename;
		if (showpath=="x"){
			locStr = locStr + "&showpath=x";
		}
		location=locStr;
	}
}

// for admin templates which use tabs
function changeFormPart(toShow){
	for (i=1;i<14;i++){
		if (i==toShow){
			evalStr="document.getElementById('formpart_" + i + "').style.display='block'";
			eval(evalStr);
			evalStr="document.getElementById('formbutton_" + i + "').className='active'";
			eval(evalStr);
		} else {
			evalStr="document.getElementById('formpart_" + i + "').style.display='none'";
			eval(evalStr);
			evalStr="document.getElementById('formbutton_" + i + "').className='passive'";
			eval(evalStr);
		}
	}		
}

function processDisplayFieldsList(){
        var displayItems=new Array();
	fields_to_display_ul=document.getElementById("fields_to_display_ul");
        fields_to_display_ul.getChildren('li').each(function(el){
                displayItems.push(el.innerHTML);

        });
        displayItemsText=displayItems.join(",");
        document.forms['list_records_filter'].elements['dbf_dynamic_mootools_fields_to_display_list'].value=displayItemsText;
        search_data();
        //alert(document.forms['list_records_filter'].elements['dbf_dynamic_fields_to_display_list'].value);
}


var interfaceItem = new Array();
var interfaceDefs = new Array();
var interfaceTypeAssocs = new Array();
var interfaceValues = new Array();
var interfaceSelectedValue = new Array();
var interfaceElementType = new Array();

function clearField(filter_id, fieldname){
	newHTML="";
	document.getElementById(fieldname).innerHTML=newHTML;
	document.getElementById(fieldname).style.display="inline";
}


function showNextField(filter_id, fieldname, div_to_update, formFieldName){
	
	//alert("showNextField called with\n\n" + filter_id + "\n" + fieldname + "\n" + div_to_update + "\n" + formFieldName);
	
	var interfaceArray = new Array();
	interfaceArray = interfaceItem[filter_id];
	newHTML="<div class=\"interfaceInnerDiv\" style=\"background-color:#1b2c67\"><table class=\"form_table\" style=\"background-color:#e1e1e1\">";
	origFormFieldName=formFieldName;
	formFieldName=fieldname + "_-_" + formFieldName; 
	for (var i=0; i<interfaceArray.length; i++){

		if (interfaceTypeAssocs[filter_id][i] == document.forms['add_interface_form'].elements[formFieldName].value || document.forms['add_interface_form'].elements[formFieldName].value=="ALL"){

			// does it have a value
			getVarName="existing___" + fieldname + "___" + interfaceArray[i];
			if (eval("typeof("+getVarName+")") != "undefined"){
				actualValue=eval(getVarName);
			} else {
				actualValue="";
			}

			// get the print name..
			printname=interfaceArray[i];
			printname=printname.replace(/_/g, " ");
			firstletter=printname.substr(0,1);
			printname=firstletter.toUpperCase() + printname.substr(1);

			if (interfaceElementType[filter_id][i] == "checkbox"){
				if (actualValue==1){ selectedCb=" selected"; } else {selectedCb="";}
				newHTML += "<tr><td align=\"right\" valign=\"top\">" + printname + ": </td><td><input type=\"checkbox\" name=\"" + fieldname + "_-_" + interfaceArray[i] + "\"" + selectedCb + "></td><td><span class=\"helptip\">" + interfaceDefs[filter_id][i] + "</span><a href=\"\"><img src=\"system/graphics/icons/help.png\" border=\"0\"></a></td></tr>";

			} else if (!interfaceValues[filter_id][i]){
				newHTML += "<tr><td align=\"right\" valign=\"top\">" + printname + ": </td><td><input type=\"text\" name=\"" + fieldname + "_-_" + interfaceArray[i] + "\" size=\"40\" value = \"" + actualValue + "\"></td><td><span class=\"helptip\">" + interfaceDefs[filter_id][i] + "</span><a href=\"\"><img src=\"system/graphics/icons/help.png\" border=\"0\"></a></td></tr>";
			} else {
				newHTML += "<tr><td align=\"right\" valign=\"top\">" + printname + ": </td><td><select name name=\"" + fieldname + "_-_" + interfaceArray[i] + "\" >";

				var splitList=interfaceValues[filter_id][i].split(",");
				for (n=0;n<splitList.length;n++){
					listvalue=splitList[n];
					listtext=splitList[n];
					if (splitList[n].match(";;")){
						var splitOption=splitList[n].split(";;");
						listvalue=splitOption[0];
						listtext=splitOption[1];
					}
					newHTML += "<option value=\""+listvalue+"\">"+listtext+"</option>";	
				}
				
				newHTML += "</option></select></td><td><span class=\"helptip\">" + interfaceDefs[filter_id][i] + "</span><a href=\"\"><img src=\"system/graphics/icons/help.png\" border=\"0\"></a></td></tr>";

			}	
		} else {
		}
	}
	newHTML += "</table></div>";
	//newHTML = "<select name=\"\">";
	//newHTML += "<option value=\"\">";
	//newHTML += "</option>";
	//newHTML += "</select>";	
	document.getElementById(div_to_update).innerHTML=newHTML;
	document.getElementById(div_to_update).style.display="inline";
}

//function AJAX_update_many_to_many_field(table,key_field,key_id,value_field,master_id,value,type,item_value){
function AJAX_update_many_to_many_field(data){
   console.log("on function");
   UpdateUrlStr= "ajax/ajaxManyToManyFieldUpdate.php?t=" + data['table'] + "&kf=" + data['key_field'] + "&vf=" + data['value_field'] + "&v=" + data['value'] + "&w=id&id=" + data['key_id']+"&type="+data['type']+"&item_value="+data['item_value']+"&master_id="+data['master_id'];

   var myUpdateRequest= new Request({method: 'get', url: UpdateUrlStr,                                                                                                              onSuccess:function(responseStr){
      // do nothing
      //alert(responseStr);
   },
   onFailure:function(){
      alert("The inner ajax call failed. You should close and reload this form and check that the totals are all correct");
   }
   });
   myUpdateRequest.setHeader('Content-type','text/plain');
   myUpdateRequest.send();

}

