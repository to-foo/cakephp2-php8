<div class="modalarea">
<h2><?php echo __('Show video');?></h2>
<div id="image_wrapper">
 <video width="100%" height="100%" controls>
  <source src="<?php echo $videoPath;?>" type="video/mp4">
Your browser does not support the video tag.
</video> 
</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
	});
</script>
<?php  echo $this->JqueryScripte->ModalFunctions(); ?> 