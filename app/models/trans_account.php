<?php
class TransAccount extends AppModel {
	var $name = 'TransAccount';
	var $validate = array(
		'username' => array(
			/*
			'usernameRule_1' => array(
				'rule' => 'alphaNumeric',
				'message' => 'Usernames must only contain letters and numbers.'
			),
			*/
			'usernameRule_2' => array(
				'rule' => 'isCaseInsensitiveUnique',
				'message' => 'Sorry, the username has already been taken.(case-insensitive)'
			),
			'usernameRule_3' => array(
				'rule' => 'isAgentUsernameOrganized',
				'message' => 'Managers, please sign up your agents with the alpha numeric user names/rids, or the sales will not track properly.(If the last user name rid was LL22, the next agent will be LL23, and so on. Note that LL23_1 is valid as it represents an alternative name for LL23)'
			),
			'usernameRule_4' => array(
				'rule' => 'isAgentUsernameInMappings',
				'message' => 'This username is saved for campaign id, please use another one.'
			)
		),
		'password' => array(
			'rule' => 'notEmpty'
		)
	);
	
	var $status = array('0' => 'suspended', '1' => 'activated');
	var $online = array('0' => 'offline', '1' => 'online');
	
	function hashPasswords($data) {
		if (isset($data['TransAccount']['password'])) {
			$data['TransAccount']['password'] = md5($data['TransAccount']['password']);
			return $data; 
		}
		return $data;
	}
	
	function isCaseInsensitiveUnique($check) {
		$r = $this->find('first',
			array(
				'conditions' => array(
					'lower(username)' => strtolower($check['username'])
				)
			)
		);
		if (isset($this->data['TransAccount']['id'])) {//if it's an updating operation
			if (empty($r)) return true;
			else {
				return ($r['TransAccount']['id'] == $this->data['TransAccount']['id'] ? true : false);
			}
		} else {//otherwise, it's an inserting operation
			return empty($r);
		}
	}
	
	function isAgentUsernameOrganized($check) {
		$data = $this->data[$this->name];
		if (isset($data) && $data['role'] == 2) {//only if it's an agent
			$value = array_values($check);
			$value = $value[0];
			//this rule means:the first 2~5 chars should be a-z or A-Z or 0-9 or _, and the following 2~4 chars should be 0-9
			return preg_match('/^[a-z]{1,3}\d{0,4}_{0,1}\d{0,2}$/i', $value);
		}
		return true;
	}
	
	function isAgentUsernameInMappings($check) {
		$data = $this->data[$this->name];
		if (isset($data) && $data['role'] == 2) {//only if it's an agent
			$value = array_values($check);
			$value = $value[0];
			if (strtolower($value) == strtolower($data['username'])) return true;
			$rs = $this->query(
				sprintf(
					'select id from agent_site_mappings where LOWER(campaignid) = "%s"',
					strtolower($value)
				)
			);
			return (empty($rs));
		}
		return true;
	}
}
?>
