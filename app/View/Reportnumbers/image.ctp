<?php
echo '<img id="CurrentReportImage_'.$this->request->projectvars['VarsArray'][6].' " class="draggable" src="data:image/jpeg;base64, ' . $imData . ' " />';
echo $this->Form->input('ThisImageId',array('type' => 'hidden', 'value' => 'CurrentReportImage_'.$this->request->projectvars['VarsArray'][6]));
echo $this->Form->input('ThisImageWidth',array('type' => 'hidden', 'value' => $imageinfo[0]));
echo $this->Form->input('ThisImageHeight',array('type' => 'hidden', 'value' => $imageinfo[1]));
?>
<!--
<span class="dragging_footer">test</span>
-->
<script>
$(document).ready(function () {

		var Id = "#" + $("#ThisImageId").val();
		var imageheight = parseInt($("#ThisImageHeight").val());
		var imagewidth = parseInt($("#ThisImageWidth").val());
    var modalheight = parseInt($(".fancybox-content").height());
		var modalwidth = parseInt($(".fancybox-content").width());

		if(imageheight < modalheight && imagewidth < modalwidth){

			$("img.draggable").css("height",imageheight);
			$("img.draggable").css("width",imagewidth);

			 return false;
		}

		if(imageheight < imagewidth){

			if(imageheight > modalheight){

				var newheight = modalheight;

				$("img.draggable").css("height",newheight);
				$("img.draggable").css("width","auto");

				return false;

			} else {

				$("img.draggable").css("height",imageheight);
				$("img.draggable").css("width","auto");

				return false;

			}

			return false;

		} else {

			if(imagewidth > modalwidth){

				var newwidth = modalwidth;

				$("img.draggable").css("height","auto");
				$("img.draggable").css("width",newwidth);

				return false;

			} else {

				$("img.draggable").css("height","auto");
				$("img.draggable").css("width",imagewidth);

				return false;

			}

			return false;

		}

});
</script>
