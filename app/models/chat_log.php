<?php
class ChatLog extends AppModel {
	var $name = 'ChatLog';
	
	var $validate = array(
		'clientusername' => array(
			'clientusernameRule_1' => array(
				'rule' => 'alphaNumeric',
				'message' => 'Client usernames must only contain letters and numbers.'
			)
		),
		'conversation' => array(
			'rule' => 'notEmpty'
		)
	);
}
?>