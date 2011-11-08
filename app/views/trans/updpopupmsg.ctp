<h1>Update Popup Message</h1>
<?php
//echo print_r($results, true);
$userinfo = $session->read('Auth.TransAccount');
echo $form->create(null, array('controller' => 'trans', 'action' => 'updpopupmsg'));
?>
<table width="100%">
	<tr>
		<td align="center">
		Popup Message
		</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAdmin.notes', array('label' => '', 'rows' => '30', 'cols' => '80'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><?php echo $form->submit('Update', array('style' => 'width:112px;')); ?></td>
	</tr>
</table>
<?php
echo $form->input('TransAdmin.id', array('type' => 'hidden'));
echo $form->end();
?>

<script type="text/javascript">
	CKEDITOR.replace('TransAdminNotes',
		{
	        filebrowserUploadUrl : '/act/trans/upload',
	        filebrowserWindowWidth : '640',
	        filebrowserWindowHeight : '480'
	    }
	);
	CKEDITOR.config.height = '300px';
	CKEDITOR.config.width = '850px';
	CKEDITOR.config.resize_maxWidth = '850px';
	CKEDITOR.config.toolbar =
		[
		    ['Source','-','NewPage','Preview','-','Templates'],
		    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
		    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		    '/',
		    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
		    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		    ['Link','Unlink','Anchor'],
		    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		    '/',
		    ['Styles','Format','Font','FontSize'],
		    ['TextColor','BGColor']
		];
</script>
