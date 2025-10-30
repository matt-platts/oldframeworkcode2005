<style type="text/css">
.itemTextSpan {font-size:13px;}
</style>

<script type="text/javascript">

function checkForm(){
	document.forms[0].submit();
	return;
	formError=0;
	if (!document.forms[0].elements['new_value_of_sale'].value){formError=1; alert("You Must Enter the value of the sale. Use the 'Calculate' function to calculate this automaticaly from the products that you have entered.");}
	else if (!isNumeric(document.forms[0].elements['new_value_of_sale'].value)){formError=1; alert("You Must Enter the final total as a positive number. Use the 'Calculate' function to calculate this automaticaly from the products that you have entered.");}
	else {
		totalPrice=0;
		for (i=0;i<document.forms[0].elements.length;i++){
			thisname = document.forms[0].elements[i].name;

                        // check no blank product fields
                        if (thisname.match("select_") && !document.forms[0].elements[i].value){
                                formError=1;
                                alert("There is a blank product name field in the products form - please correct this or use the delete button by each product entry to delete the row.");
                        }

			// check no blank quantity fields
			if (thisname.match("quantity_") && !document.forms[0].elements[i].value){
				formError=1;
				alert("There is a blank quantity field in the products form - please correct this or use the delete button by each product entry to delete the row.");
			}

			// add prices together
			if (thisname.match("price_") && document.forms[0].elements[i].value){
			totalPrice += parseFloat(document.forms[0].elements[i].value);
			}

			// check no blank price fields
			if (thisname.match("price_") && !document.forms[0].elements[i].value){
				formError=1;
				alert("There is a blank price field in the products form - please correct this or use the delete button by each product entry to delete the row.");
			}
		}
		if (!totalPrice){
			alert("You must enter some products onto this invoice, together with quantities sold and the amount in UK Pounds");
			formError=1;
		}
	}

	if (!formError){
		document.forms[0].submit();
	}
}

function isNumeric(sText) {
   if (sText==0){return false;}
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

   for (i = 0; i < sText.length && IsNumber == true; i++) { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   }


function node_delete(whichPara){
	if (confirm("Are you sure you want to delete this product from the invoice?")){
		document.getElementById('allparas').removeChild(document.getElementById(whichPara));
		//calcTotal();
	} else {}
}

function loadInputField(keyVal,targetDiv){
	myname = "inputfield_" + keyVal;
	notesname = "helptext_" + keyVal;
	targetDiv="div_" + targetDiv;
	eval("document.getElementById('"+targetDiv+"').innerHTML='"+eval(myname)+"';")
} 

function calcTotal(){
	string="";
	totalPrice=0;
 	for (i=0;i<document.forms[0].elements.length;i++){
		thisname = document.forms[0].elements[i].name;
		
		if (thisname.match("price_") && document.forms[0].elements[i].value){
		totalPrice += parseFloat(document.forms[0].elements[i].value);
		string += thisname;
		string += "\n";	
		}

 	}
	document.forms[0].elements['new_value_of_sale'].value=totalPrice;
	if (totalPrice==0 || !totalPrice){alert("Price has been calculated as 0. Please check you have entered some products.");}
}

var products_matrix=new Array();
products_matrix[0]="";

<?php

// write out javascript arrays for product categories
$categories_array=array();
$categories_ids=array();
$productsarray=array();
$productsarray[0]=NULL;
$cat_sql="SELECT * from literature_sections order by section";
$res=mysql_query($cat_sql);

$sectionscount=1;
while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
	array_push($categories_array,$h['section']);
	array_push($categories_ids,$h['id']);
	$products_in_cat_sql="SELECT * from literature_products WHERE category = " . $h['id'] . "";
	$res2=mysql_query($products_in_cat_sql);
	$products_temp_array=array();
	$productsarray[$sectionscount]=array();
	while ($h1=mysql_fetch_array($res2,MYSQL_ASSOC)){
		array_push($productsarray[$sectionscount],$h1['id']."|".$h1['item_number'] . "|" . $h1['description']."|".$h1['max_order_quantity']."|".$h1['unit_of_issue']."|".$h1['price_per_issue']);
		array_push($products_temp_array,"".$h1['id']."|".$h1['item_number'] . "|" . $h1['description']."|".$h1['max_order_quantity']."|".$h1['unit_of_issue']."|".$h1['price_per_issue']);
	}
	$productslist=join("\",\"",$products_temp_array);
	print "products_matrix[$sectionscount]=new Array(\"" . $productslist . "\");\n";
	$sectionscount++;
}

$all_cats = join("\",\"",$categories_array);
$all_cat_ids = join("\",\"",$categories_ids);
?>

var categories_names_array = new Array("<?php echo $all_cats;?>" );
var categories_ids_array = new Array("<?php echo $all_cat_ids;?>" );

function updateproducts(selectedcategory,fieldtoupdate){
	//allHTML=document.getElementsByTagName("html")[0].innerHTML;
	//document.forms['update_table'].elements['dom'].value=allHTML;
	var categorieslist=document.update_table.category1
	var productslist=eval("document.update_table." + fieldtoupdate)

	productslist.options.length=0
	if (selectedcategory>0){
	for (i=0; i<products_matrix[selectedcategory].length; i++)
		productslist.options[productslist.options.length]=new Option(products_matrix[selectedcategory][i].split("|")[1]+ "  -  " + products_matrix[selectedcategory][i].split("|")[2], products_matrix[selectedcategory][i].split("|")[0]); 
	}
}

var products_names_array = new Array("<?php echo $all_products;?>" );
//-->
</script>
<form name="update_table" method="post" action="site.php?s=1&action=process_update_table&s=1&content=12&filter_id=34" enctype="multipart/form-data" style="padding:0px; margin:0px">
<input type="hidden" name="tablename" value="literature_sales">
<input type="hidden" name="edit_type" value="add_row">
<input type="hidden" name="rowid_for_edit" value="">
<input type="hidden" name="add_data" value="0">
<input type="hidden" name="filter_id" value="113">
<input type="hidden" name="dbf_ido" value="">
<input type="hidden" name="pass_keys_as_hidden_fields" value="after_update,export">
<input type="hidden" name="after_update" value="run_code">
<input type="hidden" name="after_update_run_code" value="enter_literature_products()">
<input type="hidden" name="after_update_display_content_id" value="105">

<input type="hidden" name="export" value="html">
<table class="form_table" style="padding-top:0px; margin-top:0px; font-size:13px" cellpadding=0 cellspacing=0>
<tr><td  align="left" valign="middle" align="right">

<p style="font-size:12px"><span style="width:100px; float:left;">Select Category:</span> 
<select name="select_category_1" onChange="updateproducts(this.selectedIndex,'select_1')" />
<script language="Javascript">

    for (p=0;p<categories_names_array.length;p++){
	document.write("<option value=\"" + categories_ids_array[p] + "\">" + categories_names_array[p] + "</option>\n");
    }
       
	function infoSpan(selectedProduct,spanToUpdate){
		//alert(selectedProduct + " " + spanToUpdate);
		// loop through products matrix
		for (n=0;n<products_matrix.length;n++){
			productsInSection=products_matrix[n];
			for (n2=0;n2<products_matrix[n].length;n2++){
				productsInner=products_matrix[n][n2].split("|")[0];
				if (productsInner==selectedProduct){
					document.getElementById(spanToUpdate).innerHTML="Unit Of Issue: " + products_matrix[n][n2].split("|")[4] + " | Max Order Quantity: " + products_matrix[n][n2].split("|")[3];
				}
			}
		}
	}
</script>

</select>
<br />
<span style="width:100px; float:left;">Product Name:</span>
<select name="select_1" style="font-size:13px" onChange="infoSpan(this.value,'item_1_info')">
<option value="" style="font-family:tahoma,arial,verdana,helvetica"> - </option>
<script language="Javascript">
/*
    for (p=0;p<products_options_array.length;p++){
	document.write("<option value=\"" + products_options_array[p] + "\">" + products_names_array[p] + "</option>\n");
    }
  */     
</script>
</select>
<br />
<span style="width:100px; float:left;">Quantity: </span><input type="text" size="2" name="quantity_1"> <span id="item_1_info" name="item_1_info"></span>
</p>
<div id="allparas" style="padding-top:0px; margin-top:0px; width:650px">
<span class="jc_button_140" style="margin-top:30px"><a style="font-weight:bold; clear:both; padding-top:10px; margin-top:10px;" href="#" onclick="
if( !document.createElement || !document.childNodes ) {
	window.alert('Your browser is not DOM compliant');
} else {
	// set up paragraph and select
	randomNumber = Math.floor(Math.random()*9999999999999999) 
	var theNewParagraph = document.createElement('p');
	var lineBreak = document.createElement('br');
	var divName = 'div_' + randomNumber;
	var theTopCategorySelect = document.createElement('select');
	var theSelect = document.createElement('select');
	newSelectName= 'select_' + document.getElementsByTagName('p').length;
	newCatSelectName= 'select_category_' + document.getElementsByTagName('p').length;
        newText1Name= 'quantity_' + document.getElementsByTagName('p').length;
        newText2Name= 'price_' + document.getElementsByTagName('p').length;
        newInfoSpanName= 'info_' + document.getElementsByTagName('p').length;
        myRandomNumber = Math.floor(Math.random()*9999999999999999);
	newSelectName = newSelectName + myRandomNumber;
        newText1Name += myRandomNumber;
        newText2Name += myRandomNumber;
        newInfoSpanName += myRandomNumber;
        theSelect.setAttribute('name',newSelectName);
        theSelect.setAttribute('id',newSelectName);
        theSelect.setAttribute('style','font-size:12px;');
	passValToFunction=newInfoSpanName;
        theSelect.setAttribute('onChange','infoSpan(this.value,passValToFunction)');
	theTopCategorySelect.setAttribute('name',newCatSelectName);
	theTopCategorySelect.setAttribute('id',newCatSelectName);
	theTopCategorySelect.setAttribute('style','font-size:13px');
    
	passToFunction='updateproducts(this.selectedIndex,\'' + newSelectName + '\')';
	theTopCategorySelect.setAttribute('onChange',passToFunction);

	// set up options for select
	firstOption = document.createElement('option');
	firstOption.setAttribute('value','');
	firstOption.setAttribute('style','font-weight:bold; font-size:12px');
	firstText = document.createTextNode(' - ');
	theSelect.appendChild(firstOption);
	firstOption.appendChild(firstText);
	arrayOptions=Array();
	arrayText=Array();
    /*
        for (p=0;p<products_options_array.length;p++){
		arrayOptions[p] = document.createElement('option');
		arrayOptions[p].setAttribute('value',products_options_array[p]);
		arrayOptions[p].setAttribute('style','font-size:13px; ');
		arrayText[p] = document.createTextNode(products_names_array[p]);
		theSelect.appendChild(arrayOptions[p]);
		arrayOptions[p].appendChild(arrayText[p]);
	}*/

	// set up options for category select
	catFirstOption = document.createElement('option');
	catFirstOption,setAttribute('value','');
	catFirstOption.setAttribute('style','font-weight:bold; font-size:12px');
	catFirstText = document.createTextNode('Select A Category:');
	theTopCategorySelect.appendChild(catFirstOption);
	catFirstOption.appendChild(catFirstText);
	arrayOptions=Array();
	arrayText=Array();
        for (p=0;p<categories_ids_array.length;p++){
		arrayOptions[p] = document.createElement('option');
		arrayOptions[p].setAttribute('value',categories_ids_array[p]);
		arrayOptions[p].setAttribute('style','font-size:13px; ');
		arrayText[p] = document.createTextNode(categories_names_array[p]);
		theTopCategorySelect.appendChild(arrayOptions[p]);
		arrayOptions[p].appendChild(arrayText[p]);
	}


        var theTextField = document.createElement('input'); // first input field
        theTextField.setAttribute('id','myid');
        var theTextField2 = document.createElement('input'); // second input field
	var theDeleteImage = document.createElement('img');
	
	//set up theNewParagraph
	newParaName= 'para_' + document.getElementsByTagName('p').length;
	newParaName += randomNumber; 
	theNewParagraph.setAttribute('id',newParaName);
	theNewParagraph.setAttribute('style','padding-top:10px; font-size:13px; width:630px; clear:both;'); // there was clear:both; here too

	//theSelect.setAttribute('name','');	// dont know why this is here!
	theTextField.setAttribute('type','text');
        theTextField.setAttribute('id',newText1Name);
        theTextField.setAttribute('name',newText1Name);
	theTextField.setAttribute('size','2');
        theTextField.setAttribute('style','width:35px;');
        theTextField2.setAttribute('type','text');
        theTextField2.setAttribute('id',newText2Name);
        theTextField2.setAttribute('name',newText2Name);
	theTextField2.setAttribute('size','4');
        theTextField2.setAttribute('style','width:55px;');
        theTextField2.setAttribute('onBlur','calcTotal()');
        theTextField2.setAttribute('onChange','calcTotal()');
        
	theDeleteImage.setAttribute('src','graphics/icons/cancel.png');
	deleteImageName = 'delete_' + document.getElementsByTagName('p').length;
	deleteImageName += randomNumber; 
	
	theDeleteImage.setAttribute('id',deleteImageName);
	theDeleteImage.setAttribute('hspace','20');
	theDeleteImage.setAttribute('vspace','3');

	theDiv = document.createElement('div');
	theDiv.setAttribute('id',divName); 
	theDiv.setAttribute('style','display:inline; float:left; padding:0px; margin:0px;'); 
    
        selectDiv = document.createElement('div');
        selectDiv.setAttribute('style','display:inline; float:left; clear:none;');

 	//prepare the text nodes and spans
	var productNameSpan=document.createElement('span');
	productNameSpan.setAttribute('style','display:inline; width:100px; float:left');
	var theText1 = document.createTextNode('Product Name:');
	//productFont=document.createElement('font');
	//productFont.setAttribute('size','2');
	//productFont.appendChild(theText1);
	productNameSpan.appendChild(theText1);

	var quantitySpan=document.createElement('span');
	quantitySpan.setAttribute('style','display:inline; width:100px; float:left; font-size:12px;');
	//quantityFont=document.createElement('font');
	//quantityFont.setAttribute('size','2');
        secondText = document.createTextNode('Quantity:');
	//quantityFont.appendChild(secondText);
	quantitySpan.appendChild(secondText);
	
	var categoryTextSpan=document.createElement('span');
	categoryTextSpan.setAttribute('style','display:inline; width:100px; float:left; font-size:12px;');
	categoryTextSpan.setAttribute('class','itemTextSpan');
	//categoryFont=document.createElement('font');
	//categoryFont.setAttribute('size','2');
	theCategoryText = document.createTextNode('Select Category:');
	//categoryFont.appendChild(theCategoryText);
	categoryTextSpan.appendChild(theCategoryText);

        thirdText = document.createTextNode('&nbsp; &nbsp; Total &pound;: ');
    
	theInfoSpan = document.createElement('span');
	theInfoSpan.setAttribute('style','display:inline; padding-left:15px');
	theInfoSpan.setAttribute('id',newInfoSpanName);

	//put together the whole paragraph
	theNewParagraph.appendChild(categoryTextSpan);
	theNewParagraph.appendChild(theTopCategorySelect);
	theNewParagraph.appendChild(lineBreak);
	theNewParagraph.appendChild(productNameSpan);
	theNewParagraph.appendChild(theSelect);
	theNewParagraph.appendChild(theDiv);
        theDiv.appendChild(quantitySpan);
	theDiv.appendChild(theTextField);
	theDiv.appendChild(theInfoSpan);
        //theDiv.appendChild(thirdText);
        //theDiv.appendChild(theTextField2);
	theDiv.appendChild(theDeleteImage);
	
	//insert it into the document somewhere
	this.parentNode.parentNode.insertBefore(theNewParagraph,this.parentNode);
	//make the paragraph delete itself when they click on it
	document.getElementById(deleteImageName).onclick = function () { node_delete(this.parentNode.parentNode.id); //this.parentNode.parentNode.removeChild(this.parentNode);
       	};

	document.getElementById(newCatSelectName).onchange = function () { updateproducts(this.selectedIndex,newSelectName); };
	document.getElementById(newSelectName).onchange = function () { infoSpan(this.value,newInfoSpanName); };
}
return false;
">Order More</a></span>
</td><td></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2">Cost Center Number: <input type="text" name="new_cost_centre_no"></td></tr>
<tr><td valign="middle" align="right"></td></tr><tr><td colspan=2 valign="middle">
<!--<input type="text" name="new_value_of_sale" value=0 size="5">//--> </td><td></td></tr>
<input type="hidden" name="new_ordered_by" value="<?php echo user_data_from_cookie("id"); ?>">
<input type="hidden" name="new_date_placed" value="NOW()">
<tr><td>&nbsp;</td></tr>
<tr><td align="left">
<span class="jc_button_140"><a href="Javascript:checkForm()">Place order</a></span>
</td></tr>
</table>
<!-- <textarea name="dom" rows=10 cols=70> </textarea>//-->
</form>
<?php
?>
