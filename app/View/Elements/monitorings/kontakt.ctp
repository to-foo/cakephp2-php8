<h4><?php echo __('Kontakte',true); ?></h4>
<div class="index inhalt">
  <table class="advancetool" cellpadding="0" cellspacing="0">
    <tr>
      <th><?php echo __('Name', true); ?></th>
      <th><?php echo __('Firma', true); ?></th>
      <th><?php echo __('Zuständigkeit', true); ?></th>
      <th><?php echo __('Tel.', true); ?></th>
    </tr>
<?php $contacts = array(array('Peter Krause','Kesselbau','Kesselbauer','0162-0000-0000'),array('Torsten Plaue','Qualitätssicherungsfirma','Sicherheitsbeauftragter','0151-0000-0000'),array('Thomas Bach','Reinigungsfirma','Facility Manager','0151-0000-0000'),array('Joachim Meyer','NDT Firma','ZFP Prüfer Stufe 3','0151-0000-0000'));?>

<?php foreach ($contacts as $ckey => $c_vlaue):?>
<tr><?php foreach ($c_vlaue as $ck => $cv) { echo '<td>'.$cv.'</td>';}?></tr>
<?php endforeach;?>
</table>
</div>
