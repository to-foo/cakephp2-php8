<div class="diagramm_image">
<?php
$img = $this->Image->get($this->fetch('content'));
if(!$img) echo $this->fetch('content');
else echo $img;
?>
</div>
