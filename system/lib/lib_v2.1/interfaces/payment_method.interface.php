<?php

interface payment_method {

    public function make_payment();
	
    public function itemise_payment();

    public function load_payment_success();

    public function load_payment_error();
}


?>
