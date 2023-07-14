<script type="text/javascript">
	$(document).ready(function() {

		$("a.add_assigned").click( function() {

			var data = new Array();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "dialog", value: 1});
			data.push({name: "linked", value: 1});
			data.push({name: "type", value: "assign"});
      data.push({name: "setGenerally", value: $('.modalarea .hint input:checked').val()});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $(this).attr("href"),
				data	: data,
				success: function(data) {
		    		$("#dialog").html(data);
		    		$("#dialog").show();
				}
			});
			return false;
		});

		$('.modalarea table tr:not(:first-child) a.round').css('cursor', 'move');

		$('.modalarea table').sortable({
			'items': 'tr:not(:first-child)',
			'helper': 'clone',
			'handle': 'a.round',
			'cursor': 'move',
			'update': function(ev, ui) {
				$('.modalarea table').sortable('disable');
				$('<div class="hint" id="saveHint"><?php echo __('saving - please wait a moment'); ?></div>').insertAfter('.modalarea h2');

				data = [{'name': 'ajax_true', 'value': 1}];
				$('.modalarea tr a.round').each(function(id, elem) {
					data.push({'name': 'data[sort]['+id+'][id]', 'value': $(elem).attr('href').replace(/.*\/([0-9]+)$/,'$1') });
					data.push({'name': 'data[sort]['+id+'][subindex]', 'value': id });
				});

				$.ajax({
					'url': '<?php echo Router::url(array_merge(array('controller'=>'reportnumbers', 'action'=>'assignedReports'), $this->request->projectvars['VarsArray'])); ?>',
					'type': 'post',
					'data': data,
					'success': function(data) {
						$('#dialog').html(data);
					},
					'complete': function() {
						$('.modalarea table').sortable('enable');
						$('.modalarea #saveHint').remove();
					}
				});
			}
		});

	});
</script>
