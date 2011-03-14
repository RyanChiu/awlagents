<?php
class TransSite extends AppModel {
	var $name = 'TransSite';
	
	var $status = array('0' => 'suspended', '1' => 'activated');
	
	var $validate = array(
		'hostname' => array(
			'hostnameRule_1' => array(
				'rule' => 'notEmpty',
				'message' => 'Please do not let this field empty.'
			),
			'hostnameRule_2' => array(
				'rule' => 'isUnique',
				'message' => 'Sorry, this host name has already been taken.' 
			)
		),
		'sitename' => array(
			'rule' => 'notEmpty',
			'message' => 'Please do not let this field empty.'
		),
		'abbr' => array(
			'abbrRule_1' => array(
				'rule' => array('between', 3, 5),
				'message' => 'Abbreviation must be between 3 and 5 characters long.'
			),
			'abbrRule_2' => array(
				'rule' => 'isUnique',
				'message' => 'Sorry, this abbreviation has already been taken.' 
			)
		),
		'url' => array(
			'rule' => 'url',
			'message' => 'Please fill out a valid url.'
		),
		'contactname' => array(
			'rule' => 'notEmpty',
			'message' => 'Please do not let this field empty.'
		),
		'email' => array(
			'rule' => 'email',
			'message' => 'Please fill out a valid email address.'
		),
		'phonenums' => array(
			'rule' => 'notEmpty',
			'message' => 'Please do not let this field empty.'
		),
		'type' => array(
			'rule' => 'notEmpty',
			'message' => 'Please select the type.'
		),
		'payouts' => array(
			'rule' => array('decimal', 2),
			'message' => 'Please fill out 2 digital numbers after the decimal point.'
		)
	);
	
	var $types = array('WEBCAMS' => 'WEBCAMS', 'ADULT' => 'ADULT', 'DATING' => 'DATING', 'CASINO' => 'CASINO');
}
?>