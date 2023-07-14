<div class="quicksearch">
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Technischer Platz', true)); ?>
</div>
<div class="inhalt">
<h3><?php echo __('Expediting',true); ?> <?php echo __('Overview',true); ?></h3>

<table cellspacing="0" cellpadding="0">
	<tbody><tr>
			<th>Technischer Platz</th>
			<th>Zeichnungen</th>
			<th>Material</th>
			<th>Produktion</th>
			<th>Abnahmen</th>
            <th>Lieferung/Enddokumentation</th>
	</tr>
		<tr class=" altrow">
		<td>
        <a href="expeditings/edit/12/16/2/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal round">K-1201 - Vorkammer</a>
        <a href="reportnumbers/index/12/16/2/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="ajax icon overview" title="Technischen Platz anzeigen">-></a>
        </td>
        <td>
        <a href="expeditings/detail/12/16/2/1/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">100%</a> 
        <span class="status status_okay" title="complete"></span>
        </td>        
        <td>
        <a href="expeditings/detail/12/16/2/2/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">100%</a> 
        <span class="status status_okay" title="complete"></span></td>        
        <td>
        <a href="expeditings/detail/12/16/2/3/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">33,33%</a> 
        <span class="status status_over" title="time delay"></span>
        <span class="status status_error" title="error"></span></td>        
        <td>
        <a href="expeditings/detail/12/16/2/4/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">0%</a> 
        <span class="status status_notstarted" title="not started"></span>
		<span class="status status_over" title="time delay"></span>
       </td>        
        <td>
        <a href="expeditings/detail/12/16/2/4/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">0%</a> 
       <span class="status status_notstarted" title="not started"></span></td>        
	</tr>
	</tr>
		<tr class=" altrow">
		<td>
        <a href="expeditings/edit/12/16/1/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="round modal">K-1201 - Schwimmkopf</a>
        <a href="reportnumbers/index/12/16/1/0/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="ajax icon overview" title="Technischen Platz anzeigen">-></a>
        </td>
        <td>
        <a href="expeditings/detail/12/16/1/1/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">100%</a> 
        <span class="status status_okay" title="complete"></span>
        </td>        
        <td>
        <a href="expeditings/detail/12/16/1/2/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">100%</a> 
        <span class="status status_okay" title="complete"></span></td>        
        <td>
        <a href="expeditings/detail/12/16/1/3/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">33,33%</a> 
        <span class="status status_over" title="time delay"></span><span class="status status_error" title="error"></span></td>        
        <td>
        <a href="expeditings/detail/12/16/1/4/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">0%</a> 
        <span class="status status_notstarted" title="not started"></span></td>        
        <td>
        <a href="expeditings/detail/12/16/1/4/0/0/0/0/0/0/0/0/0/0/0/0/0/0" class="modal">0%</a> 
       <span class="status status_notstarted" title="not started"></span></td>        
	</tr>
	</tbody></table>


</div>
<div class="pagin_links">
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
<script>
$(function() {
$("span.status").tooltip({
		content: function () {
		var output = "<p>Detailinfos einblenden</p><ul><li>Punkt 1</li><li>Punkt 2</li><li>Punkt 3</li></ul>";
		return output;
		}
	});	
});
</script>