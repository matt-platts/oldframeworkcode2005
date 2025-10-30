<script type="text/javascript">

function checkForm(){
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

var products_options_array= new Array("","",96,97,98,99,100,101,102,103,104,105,106,107,"","",108,109,110,"","",3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95);

<?php
$product_array=array();
$productid_array=array();
$ids_array=array();

$sql1="SELECT * from products where product_type = 1 order by ID";
$res1=mysql_query($sql1) or die(mysql_error());
$sql2="SELECT * from products WHERE product_type = 2 order by ID";
$res2=mysql_query($sql2) or die(mysql_error());
$sql3="SELECT * from products where product_type NOT IN (1,2) order by ID";
$res3=mysql_query($sql3) or die(mysql_error());

array_push ($product_array,"");
array_push ($product_array,"               Section 1:");
while ($data=mysql_fetch_array($res1,MYSQL_ASSOC)){
	array_push($product_array,$data['ID'] . " : " . $data['title']);	
	array_push($ids_array,$data['id']);	
}

array_push ($product_array,"");
array_push ($product_array,"               Section 2:");

while ($data=mysql_fetch_array($res2,MYSQL_ASSOC)){
	array_push($product_array,$data['title']);	
	array_push($ids_array,$data['id']);	
}

array_push ($product_array,"");
array_push ($product_array,"               All Other Products:");

while ($data=mysql_fetch_array($res3,MYSQL_ASSOC)){
	array_push($product_array,$data['ID'] . " : " . $data['title']);	
	array_push($ids_array,$data['id']);	
}

$all_products = join("\",\"",$product_array);
?>
var products_names_array = new Array("<?php echo $all_products;?>" );
//-->
</script>
<form name="update_table" method="post" action="site.php?s=1&action=process_update_table&s=1&content=12&filter_id=34" enctype="multipart/form-data" style="padding:0px; margin:0px">
<input type="hidden" name="tablename" value="sales">
<input type="hidden" name="edit_type" value="add_row">
<input type="hidden" name="rowid_for_edit" value="">
<input type="hidden" name="add_data" value="0">
<input type="hidden" name="filter_id" value="34">
<input type="hidden" name="dbf_ido" value="">
<input type="hidden" name="pass_keys_as_hidden_fields" value="after_update,export">
<input type="hidden" name="after_update" value="display_content">
<input type="hidden" name="export" value="html">
<table class="form_table" style="padding-top:0px; margin-top:0px">
<tr><td  align="left" valign="middle" align="right">
<div id="allparas" style="padding-top:0px; margin-top:0px">
<a style="font-weight:bold; clear:both; padding-top:10px; margin-top:10px;" href="#" onclick="
if( !document.createElement || !document.childNodes ) {
	window.alert('Your browser is not DOM compliant');
} else {
	// set up paragraph and select
	randomNumber = Math.floor(Math.random()*9999999999999999) 
	var theNewParagraph = document.createElement('p');
	var divName = 'div_' + randomNumber;
	var theSelect = document.createElement('select');
	newSelectName= 'select_' + document.getElementsByTagName('p').length;
        newText1Name= 'quantity_' + document.getElementsByTagName('p').length;
        newText2Name= 'price_' + document.getElementsByTagName('p').length;
        myRandomNumber = (Math.random()*9999999999999999);
	newSelectName += myRandomNumber;
        newText1Name += myRandomNumber;
        newText2Name += myRandomNumber;
        theSelect.setAttribute('name',newSelectName);
        theSelect.setAttribute('style','font-size:10px; width:300px; font-family:arial,verdana,helvetica;');
    
	//passToFunction='loadInputField(this.value,\'' + randomNumber + '\')';
	//theSelect.setAttribute('onChange',passToFunction);

	// set up options for select
	firstOption = document.createElement('option');
	firstOption.setAttribute('value','');
	firstOption.setAttribute('style','font-weight:bold;');
	firstText = document.createTextNode('Select A Product:');
	theSelect.appendChild(firstOption);
	firstOption.appendChild(firstText);
	arrayOptions=Array();
	arrayText=Array();
    
    for (p=0;p<products_options_array.length;p++){
    	arrayOptions[p] = document.createElement('option');
        arrayOptions[p].setAttribute('value',products_options_array[p]);
        arrayOptions[p].setAttribute('style','font-size:10px; font-family:Arial,Helvetica,Verdana;');
        arrayText[p] = document.createTextNode(products_names_array[p]);
        theSelect.appendChild(arrayOptions[p]);
        arrayOptions[p].appendChild(arrayText[p]);
      
    }
       
    var theTextField = document.createElement('input'); // first input field
    theTextField.setAttribute('id','myid');
    //var theTextField2 = document.createElement('input'); // second input field
	var theDeleteImage = document.createElement('img');
	
	//set up theNewParagraph
	newParaName= 'para_' + document.getElementsByTagName('p').length;
	newParaName += randomNumber; 
	theNewParagraph.setAttribute('id',newParaName);
	theNewParagraph.setAttribute('style','padding-top:10px; font-size:12px; clear:both;'); // there was clear:both; here too

	//theSelect.setAttribute('name','');	// dont know why this is here!
	theTextField.setAttribute('type','text');
    theTextField.setAttribute('id',newText1Name);
    theTextField.setAttribute('name',newText1Name);
	theTextField.setAttribute('size','2');
    theTextField.setAttribute('style','width:35px;');
    //theTextField2.setAttribute('type','text');
    //theTextField2.setAttribute('id',newText2Name);
    //theTextField2.setAttribute('name',newText2Name);
	//theTextField2.setAttribute('size','4');
    //theTextField2.setAttribute('style','width:55px;');
    //theTextField2.setAttribute('onBlur','calcTotal()');
        //theTextField2.setAttribute('onChange','calcTotal()');
        
	theDeleteImage.setAttribute('src','graphics/icons/page_white_delete.png');
	deleteImageName = 'delete_' + document.getElementsByTagName('p').length;
	deleteImageName += randomNumber; 
	
	theDeleteImage.setAttribute('id',deleteImageName);
	theDeleteImage.setAttribute('hspace','20');

	theDiv = document.createElement('div');
	theDiv.setAttribute('id',divName); 
	theDiv.setAttribute('style','display:inline; float:left;'); 
    
    selectDiv = document.createElement('div');
    selectDiv.setAttribute('style','display:inline; float:left; clear:none;');

 	//prepare the text nodes
	var theText1 = document.createTextNode('Product Name:');
    secondText = document.createTextNode('Quantity Required:');
    //thirdText = document.createTextNode('&nbsp; &nbsp; Total &pound;: ');
    
	//put together the whole paragraph
    
	theNewParagraph.appendChild(theText1);
	theNewParagraph.appendChild(theSelect);
	theNewParagraph.appendChild(theDiv);
    theDiv.appendChild(secondText);
	theDiv.appendChild(theTextField);
    //theDiv.appendChild(thirdText);
    //theDiv.appendChild(theTextField2);
	theDiv.appendChild(theDeleteImage);
	
	//insert it into the document somewhere
	this.parentNode.parentNode.insertBefore(theNewParagraph,this.parentNode);

	//make the paragraph delete itself when they click on it
	document.getElementById(deleteImageName).onclick = function () { node_delete(this.parentNode.parentNode.id); //this.parentNode.parentNode.removeChild(this.parentNode);
       	};
}
return false;
"><p><div>Click Here To Add A Product:</div></p></a>

</td><td></td></tr>
<tr><td colspan="2"><hr size="1" /></td></tr>
<tr><td align="left" valign="middle" align="right"></td></tr><tr><td colspan=2 valign="middle">

<input type="text" name="new_value_of_sale" value=0 size="5"> </td><td></td></tr>
<input type="hidden" name="new_added_by" value="<?php echo user_data_from_cookie("id"); ?>">
<input type="hidden" name="new_date_entered" value="NOW()">
<tr><td colspan="2"><hr size=1></td></tr>
<tr bgcolor="#f1f1f1"><td colspan=2></td><td><input type="button" class="general_button" value="Enter Sale" onClick="checkForm()"></td></tr>
</table>

</form>
<?php
?>
