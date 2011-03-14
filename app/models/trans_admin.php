<?php
class TransAdmin extends AppModel {
	var $name = "TransAdmin";
	
	var $validate = array(
		'email' => array(
			'rule' => 'email',
			'message' => 'Please fill out a valid email address.'
		)
	);
}
?>