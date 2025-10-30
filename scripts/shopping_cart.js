function showAttributes(divname){
	document.getElementById(divname).style.display="block";
}

function hideAttributes(divname){
	document.getElementById(divname).style.display="none";
}

function update_local_quantity(item,direction){
	elementName="item_" + item;
	if (direction=="up"){
		document.forms['cart'].elements[elementName].value++;
	} else {
		document.forms['cart'].elements[elementName].value--;
	}
	if (document.forms['cart'].elements[elementName].value=="0"){
		document.forms['cart'].elements[elementName].value="1";
		alert("You cannot order a quantity of less than 1. If you wish to remove an item from your shopping basket, please click 'Remove' on each product line.");	
	}
}

function update_local_preorder_quantity(item,direction){
	elementName="item_" + item;
	if (direction=="up"){
		document.forms['preorder_cart'].elements[elementName].value++;
	} else {
		document.forms['preorder_cart'].elements[elementName].value--;
	}
	if (document.forms['preorder_cart'].elements[elementName].value=="0"){
		document.forms['preorder_cart'].elements[elementName].value="1";
		alert("You cannot order a quantity of less than 1. If you wish to remove an item from your shopping basket, please click 'Remove' on each product line.");	
	}
}

function terms_and_conditions_popup(){
	window.open("terms-and-conditions-popup.html","terms_and_conditions","width=450,height=300");
}

function check_payment_method(){
	radioObj = document.forms['place_order_form'].elements['payment_method'];
	myPaymentMethod=getCheckedValue(radioObj);
	if (!myPaymentMethod){
		alert("Please select a payment method");
		return;
	} else {
		myTermsAndConditions=1;
		if (document.forms['place_order_form'].elements['confirm_terms_and_conditions']){
			myTermsAndConditions=document.forms['place_order_form'].elements['confirm_terms_and_conditions'].checked;
		}
		if (!myTermsAndConditions){
			alert("Please check the box to indicate that you have read and agree to our terms and conditions of sale");
			return;
		}
		document.forms['place_order_form'].submit();
	}
}


