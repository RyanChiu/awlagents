<?php
class TransType extends AppModel {
	var $name = 'TransType';
	
	var $validate = array(
		'siteid' => array(
			'rule' => 'notEmpty',
			'message' => 'Please do not let this field empty.'
		),
		'typename' => array(
			'typenameRule_1' => array(
				'rule' => 'notEmpty',
				'message' => 'Please do not let this field empty.'
			),
			'typenameRule_2' => array(
				'rule' => 'isUnique',
				'message' => 'Sorry, this type name has already been taken.' 
			)
		),
		'url' => array(
			'rule' => 'url',
			'message' => 'Please fill out a valid url.'
		)
	);
}
?>