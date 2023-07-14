<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', '');
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=9"/>
	<meta http-equiv="cache-control" content="max-age=0, no-cache, no-store, must-revalidate" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title><?php echo $cakeDescription ?></title>
	
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('jquery-ui-1.9.2.custom.css?'.microtime());
		echo $this->Html->css('jquery.datetimepicker.css?'.microtime());
		echo $this->Html->css('fileUploader.css?'.microtime());
		echo $this->Html->css('multi-select.css');
		echo $this->Html->css('spectrum.css');
		echo $this->Html->css('fancybox/jquery.fancybox.css');
               
		echo $this->Html->css($this->Navigation->PathToCSS('default') . 'cake.default.css?'.microtime(),null,array('media' => 'screen and (min-width: 1100px)'));		
		echo $this->Html->css($this->Navigation->PathToCSS('small') . 'cake.small.css?'.microtime(),null,array('media' => 'screen and (max-width: 1099px)'));	
		echo $this->Navigation->PathToSpecificCSS();			
		echo '<!--[if lt IE 9]>';
		echo $this->Html->css('ie.css?'.microtime());
		echo '<![endif]-->';	
//		echo $this->Html->script('jquery-1.12.4.min');
		echo $this->Html->script('jquery-1.8.3');
		echo $this->Html->script('jquery-ui-1.9.2.custom.min');
		echo $this->Html->script('jquery.fileUploader');
		echo $this->Html->script('jquery.datetimepicker');
		echo $this->Html->script('jquery.jeditable.mini');
		echo $this->Html->script('jquery.ui-contextmenu');
		echo $this->Html->script('jquery.multi-select');
		echo $this->Html->script('spectrum');
		echo $this->Html->script('taphold');
		echo $this->Html->script('jquery.address.min');
		echo $this->Html->script('fancybox/jquery.fancybox.min');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
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
	</script>
</head>
<body>

<noscript><?php echo __('Sorry, this Application will run only with JavaScript, please activate JavaScript in your browser, and try again.', true);?></noscript>
<?php
/*
if(Configure::read('debug') > 0){
    App::Import('ConnectionManager');
    $ds = ConnectionManager::getDataSource('default');
    $dsc = $ds->config;

	echo '<div class="hint"><p>';
	echo '['. Configure::read('databaseinfo_dev.wwwroot.name') . '] ';
	echo '[Datenordner: ' . Configure::read('data_folder_name') . '] ';
	echo '[Datenbank: ' . $dsc['host'] . ' / ' . $dsc['database'] . '] ';
	echo '[Webserver: ' . FULL_BASE_URL . '] ';
	echo '</p></div>';
}

echo '<div class="hint"><p>';
echo '<a href="https://www.qm-systems.info/mps_12/">Login alte Datenbank</a>';
echo '</p></div>';
*/
echo $this->Html->link(__('Maximize Window'),'javascript:',array('class' => 'maximizemodal','id' => 'maximizethismodal', 'title' => __('Maximize Window')));
?>
<div id="testdeviceforms"></div>
<div id="container">
	<div class="header"></div>
	<hr class="clear dotted" />
	<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
	<div id="content"><?php echo $this->fetch('content'); ?></div>
    <div class="footer"></div>
</div>
<div id="dialog"></div>


</body>
</html>
