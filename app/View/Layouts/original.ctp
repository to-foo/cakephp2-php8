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

$cakeDescription = __d('cake_dev', 'Prüfberichtsdatenbank');
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=9"/>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="pragma" content="no-cache" />

	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
	</title>
	
	<?php
		echo $this->Html->meta('icon').PHP_EOL;

		echo $this->Html->css('jquery-ui-1.9.2.custom').PHP_EOL;
		echo $this->Html->css('jquery.datetimepicker').PHP_EOL;
		echo $this->Html->css('fileUploader').PHP_EOL;
//		echo $this->Html->css(array("/lib/elfinder/css/elfinder.min")).PHP_EOL;
//		echo $this->Html->css(array("/lib/elfinder/css/theme")).PHP_EOL;
		echo $this->Html->css('cake.default').PHP_EOL;

		echo $this->Html->script('jquery-1.8.3').PHP_EOL;
		echo $this->Html->script('jquery-ui-1.9.2.custom.min').PHP_EOL;
		echo $this->Html->script('jquery.fileUploader').PHP_EOL;
		echo $this->Html->script('jquery.datetimepicker').PHP_EOL;
		echo $this->Html->script('jquery.jeditable.mini').PHP_EOL;
//		echo $this->Html->script('jquery.history');
//		echo $this->Html->script(array("/lib/elfinder/js/elfinder.min"));

		echo $this->fetch('meta').PHP_EOL;
		echo $this->fetch('css').PHP_EOL;
		echo $this->fetch('script').PHP_EOL;
	?>
</head>
<body>

<noscript><?php echo __('Sorry, this Application will run only with JavaScript, please activate JavaScript in your browser, and try again.', true);?></noscript>
	<div id="container">
		<div id="header">
		</div>
		<hr class="clear dotted" />
		<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
		<div id="content"><?php echo $this->fetch('content'); ?></div>
		<div id="footer"></div>				
	</div>
<div id="dialog"></div>
</body>
</html>
