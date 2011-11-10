<h1>Update ALERTS</h1>
<?php
//echo print_r($results, true);
$userinfo = $session->read('Auth.TransAccount');
echo $form->create(null, array('controller' => 'trans', 'action' => 'addnews'));
?>
<table width="100%">
	<tr>
		<td align="center">
		ALERTS
		</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('Bulletin.info', array('label' => '', 'rows' => '60', 'cols' => '80'));
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
echo $form->input('Bulletin.id', array('type' => 'hidden'));
echo $form->end();
?>

<script type="text/javascript">
	CKEDITOR.replace('BulletinInfo',
		{
	        filebrowserUploadUrl : '/act/trans/upload',
	        filebrowserWindowWidth : '640',
	        filebrowserWindowHeight : '480'
	    }
	);
	CKEDITOR.config.height = '500px';
	CKEDITOR.config.width = '840px';
	CKEDITOR.config.resize_maxWidth = '840px';
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
