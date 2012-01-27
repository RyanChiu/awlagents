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
				'rule' => 'isUniqueInSite',
				'message' => 'Sorry, this type name has already been taken in the site.' 
			)
		),
		'url' => array(
			'rule' => 'url',
			'message' => 'Please fill out a valid url.'
		)
	);
	
	function isUniqueInSite($check) {
		$data = $this->data[$this->name];
		if (!isset($data) && !empty($data['siteid'])) return true;
		$rs = $this->find('first',
			array(
				'conditions' => array(
					'siteid' => $data['siteid'],
					'typename' => $check['typename']
				)
			)
		);
		if (isset($this->data[$this->name]['id'])) {// if it's an updating operation
			if (empty($rs)) return true;
			else {
				return ($rs[$this->name]['id'] == $this->data[$this->name]['id'] ? true : false);
			}
		} else {// otherwise, it's an inserting opreation
			return empty($rs);
		}
	}
}
?>