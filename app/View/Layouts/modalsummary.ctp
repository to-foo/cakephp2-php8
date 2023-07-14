<script type="text/javascript">
		if(!window.console) {
			var console = {
				debug: function(data) {
					$("#error_console").html(data);
				},

				info: function(data) {
					$("#error_console").html(data);
				},

				log: function(data) {
					$("#error_console").html(data);
				}
			}

			$("body").prepend('<div id="error_console" style="display: none"></div>');
		}

			$(document).ready(function(){
			setTimeout( function(){$("#message_wrapper_dialog").hide("slow");} , 5000);
			});

</script>
<?php  echo $this->fetch('script'); ?>
<div id="message_wrapper_dialog"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->fetch('content');?>
<div id="footer" class="clear"><?php  echo $this->element('sql_dump'); ?></div>
