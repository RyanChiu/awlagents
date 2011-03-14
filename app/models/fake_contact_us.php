<?php
/*
 * Only for validations
 */
class FakeContactUs extends AppModel {
	var $name = 'FakeContactUs';
	var $useTable = false;
	var $validate = array(
		'subject' => array(
			'rule' => 'notEmpty'
		),
		'message' => array(
			'rule' => 'notEmpty'
		),
		'email' => array(
			'rule' => 'email',
			'message' => 'Please fill out a valid email address.'
		)
	);
}
?>