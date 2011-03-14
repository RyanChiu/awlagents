<?php
class TransFee extends AppModel {
	var $name = 'TransFee';
	
	var $validate = array(
		'price' => array(
			'rule' => array('decimal', 2),
			'message' => 'Sorry, it\'s not a valid decimal number, please fill out one like "20.00".'
		),
		'earning' => array(
			'rule' => array('decimal', 2),
			'message' => 'Sorry, it\'s not a valid decimal number, please fill out one like "30.00".'
		)
	);
}
?>