<?php

class loginController {

	function process_login(){
		global $user;
		var_dump($_REQUEST);
		$result = $user->process_login();
	}


}

?>
