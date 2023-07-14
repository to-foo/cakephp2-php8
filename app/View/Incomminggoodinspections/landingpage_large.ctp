<h3><?php echo __('Incomig goods inspection'); ?></h3>
<div class="users index inhalt">
<div class="quicksearch">
<?php	echo $this->element('incomminggoodinspection/search_quick',array('target_id' => 'id','targedaction' => 'view','action' => 'quicksearch','minLength' => 1,'discription' => __('Intern number', true)));?>
</div>
<table id="table_incoming_goods_inspection" class="table_infinite_sroll advancetool">
<thead>
<tr>
<th><?php echo __('Bestellung',true);?></th>
<th class="small_cell"><?php echo __('Material-Id',true);?></th>
<th class="small_cell"><?php echo __('Technischer Platz',true);?></th>
<th class="small_cell"><?php echo __('Charge',true);?></th>
<th class="small_cell"><?php echo __('Zertifikate',true);?></th>
</tr>
</thead>
<tbody>
<tr>
<td>B123</td>
<td>M123</td>
<td>T123</td>
<td>C123 C1234</td>
<td>
<a href="javascript" class="icon icon_download_file">test</a>
<a href="javascript" class="icon icon_download_file">test</a>
</td>
</tr>
<tr>
<td>B1234</td>
<td>M1234</td>
<td>T1234</td>
<td>C123</td>
<td>
<a href="javascript" class="icon icon_download_file">test</a>
</td>
</tr>
<tr>
<td>B12345</td>
<td>M1234</td>
<td>T1234</td>
<td>C1234 C2512 C7853 C6971</td>
<td>
<a href="javascript" class="icon icon_download_file">test</a>
<a href="javascript" class="icon icon_download_file">test</a>
<a href="javascript" class="icon icon_upload_red">test</a>
<a href="javascript" class="icon icon_download_file">test</a>
</td>
</tr>
</tbody>
</table>
</div>
<?php
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
</div>
