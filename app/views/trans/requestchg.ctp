<h1>Request Changes Just Sent ...</h1>
<?php
//echo '<br/>' . print_r($data, true);
$userinfo = $session->read('Auth.TransAccount');
?>
<div style="min-height:30px;vertical-align:middle;line-height:30px;">
<?php
echo "<b>(Attention! Fields marked with an asterisk (*) are required! Otherwise, the request will be ignored!)</b>";
?>
</div>
<div style="margin-left:3px;margin-bottom:3px;">
<?php
if ($userinfo['role'] == 1) {//means that only an office could do this
	echo $form->button('Request Another New Agent',
		array(
			'style' => 'width:230px;',
			'onclick' => 'javascript:location.href="' .
				$html->url(array('controller' => 'trans', 'action' => 'regagent')) . '"'
		)
	);
}
?>
</div>
<div style="margin-left:3px;margin-top:3px;margin-bottom:3px;padding:5px 5px 5px 5px;background-color:#DADAB4;">
<?php
$content = str_replace("\n", "<br/>", $content);
echo "<br/>" . $content;
?>
</div>