tinyMCEPopup.requireLangPack();

var DbfFormDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		//f.somearg.value = tinyMCEPopup.getWindowArg('some_custom_arg');

		var selText = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		
		selText=selText.replace("}","");
		selText=selText.replace("{=FORM:","");
		textBits=selText.split("&");
		
		var dbf_table_value="";
		var dbf_filter_value="";
		var dbf_formtype_value="";
		var dbf_rowid_value="";
		for (i=0;i<textBits.length;i++){
			textPairs=textBits[i].split("=");
			if (textPairs[0]=="table"){  var dbf_table_value=textPairs[1];} 
			if (textPairs[0]=="filter"){  var dbf_filter_value=textPairs[1]; } 
			if (textPairs[0]=="formtype"){ var dbf_formtype_value=textPairs[1]; } 
			if (textPairs[0]=="rowid"){ var dbf_rowid_value=textPairs[1]; } 
		}

		f.dbf_rowid.value = dbf_rowid_value; 
		//f.dbf_table.value = dbf_table_value; 
		//f.dbf_filter.value = dbf_filter_value; 
		//f.dbf_formtype.value = dbf_formtype_value; 

		if (dbf_filter_value){
			filter_option_id="filteroption_" + dbf_filter_value;
			var filterlist = document.getElementById(filter_option_id); 
			filterlist.selected=true;
		}
		if (dbf_table_value){
			table_option_id="tableoption_" + dbf_table_value;
			var tablelist = document.getElementById(table_option_id); 
			tablelist.selected=true;
		}
		if (dbf_formtype_value){
			type_option_id="typeoption_" + dbf_formtype_value;
			var typelist = document.getElementById(type_option_id); 
			typelist.selected=true;
		}

	},

	insert : function() {
		// Insert the contents from the input into the document
		//insertText = "<div class=\"mceNonEditable\">{=FORM:table=" + document.forms[0].dbf_table.value + "&amp;formtype=" + document.forms[0].dbf_table.value + "&amp;filter=" + document.forms[0].dbf_filter.value + "&amp;rowid=" + document.forms[0].dbf_rowid.value + "}</div>";
		insertText = "{=FORM:table=" + document.forms[0].dbf_table.value + "&amp;formtype=" + document.forms[0].dbf_formtype.value + "&amp;filter=" + document.forms[0].dbf_filter.value + "&amp;rowid=" + document.forms[0].dbf_rowid.value + "}";
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, insertText);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(DbfFormDialog.init, DbfFormDialog);
