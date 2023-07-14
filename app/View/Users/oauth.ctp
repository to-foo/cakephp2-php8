<?php
if(isset($Error) && is_array($Error) && count($Error) > 0){
echo '<ul class="login_message">';
foreach($Error['Message'] as $_Error) echo '<li>' . $_Error . '</li>';
echo '</ul>';
}

if(!isset($this->request->data['User'])) return;
pr($this->request->data['User']);
?>
<?php echo $this->Form->create('User', array('url' => 'login'));?>
	<fieldset>
	<?php
		echo $this->Form->input('username',array('type'=>'hidden'));
		echo $this->Form->input('password',array('type'=>'hidden'));
		echo $this->Form->input('Language.beschreibung', array('type'=>'hidden'));
	?>
	</fieldset>

<?php echo $this->Form->end(); ?>
<script type="text/javascript">
$(document).ready(function(){
	$("#UserLoginForm").submit();
});
</script>

<?php return;?>
