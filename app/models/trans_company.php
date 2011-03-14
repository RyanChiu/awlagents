<?php
class TransCompany extends AppModel {
	var $name = 'TransCompany';
	var $validate = array(
		'officename' => array(
			'officenameRule_1' => array(
				'rule' => 'notEmpty',
				'message' => 'Please do not let this field empty.'
			),
			'officenameRule_2' => array(
				'rule' => 'isUnique',
				'message' => 'Sorry, this "Office Name" has already been taken.' 
			)
		),
		'man1stname' => array(
			'rule' => 'notEmpty'
		),
		'manlastname' => array(
			'rule' => 'notEmpty'
		),
		'manemail' => array(
			'rule' => 'email',
			'message' => 'Please fill out a valid email address.'
		),
		'mancellphone' => array(
			'rule' => 'notEmpty'
		),
		'country' => array(
			'rule' => 'notEmpty'
		)
	);
	
	var $payouttype = array('0' => 'Pay by Check', '1' => 'Pay by Webstern Union', '2' => 'Pay by Wire'); 
}
?>