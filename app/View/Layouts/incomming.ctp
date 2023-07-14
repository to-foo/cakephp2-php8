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
	<meta http-equiv="cache-control" content="max-age=0, no-cache, no-store, must-revalidate" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
	</title>
	
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('jquery-ui-1.9.2.custom.css?'.microtime());
		echo $this->Html->css($this->Navigation->PathToCSS('default') . 'cake.incomming.css?'.microtime(),null,array('media' => 'screen and (min-width: 1100px)'));		
		echo $this->Html->css($this->Navigation->PathToCSS('small') . 'cake.small.incomming.css?'.microtime(),null,array('media' => 'screen and (max-width: 1099px)'));		
		echo '<!--[if lt IE 9]>';
		echo $this->Html->css('ie.css?'.microtime());
		echo '<![endif]-->';	
		echo $this->Html->script('jquery-1.8.3');
		echo $this->Html->script('jquery-ui-1.9.2.custom.min');
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>

<noscript><?php echo __('Sorry, this Application will run only with JavaScript, please activate JavaScript in your browser, and try again.', true);?></noscript>
<div id="testdeviceforms"></div>
	<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
	<div id="content"><?php echo $this->fetch('content'); ?></div>
<div id="footer" class="clear"><?php  echo $this->element('sql_dump'); ?></div>
    
</body>
</html>
