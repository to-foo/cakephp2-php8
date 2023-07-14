<?php
if(!file_exists(ROOT.DS.'config'.DS.'_current'.DS.'bootstrap.ini')){
	die('The config file could not be loaded. (2)');
}

$config = parse_ini_file(ROOT.DS.'config'.DS.'_current'.DS.'bootstrap.ini',true);

/**

 * This file is loaded automatically by the app/webroot/index.php file after core.php

 *

 * This file should load/create any application wide configuration settings, such as

 * Caching, Logging, loading additional configuration files.

 *

 * You should also use this file to include any files that provide global functions/constants

 * that your application uses.

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

 * @package       app.Config

 * @since         CakePHP(tm) v 0.10.8.2117

 * @license       http://www.opensource.org/licenses/mit-license.php MIT License

 */



// Setup a 'default' cache configuration for use in the application.

Cache::config('default', array('engine' => 'File'));

/**

 * The settings below can be used to set additional paths to models, views and controllers.

 *

 * App::build(array(

 *     'Model'                     => array('/path/to/models', '/next/path/to/models'),

 *     'Model/Behavior'            => array('/path/to/behaviors', '/next/path/to/behaviors'),

 *     'Model/Datasource'          => array('/path/to/datasources', '/next/path/to/datasources'),

 *     'Model/Datasource/Database' => array('/path/to/databases', '/next/path/to/database'),

 *     'Model/Datasource/Session'  => array('/path/to/sessions', '/next/path/to/sessions'),

 *     'Controller'                => array('/path/to/controllers', '/next/path/to/controllers'),

 *     'Controller/Component'      => array('/path/to/components', '/next/path/to/components'),

 *     'Controller/Component/Auth' => array('/path/to/auths', '/next/path/to/auths'),

 *     'Controller/Component/Acl'  => array('/path/to/acls', '/next/path/to/acls'),

 *     'View'                      => array('/path/to/views', '/next/path/to/views'),

 *     'View/Helper'               => array('/path/to/helpers', '/next/path/to/helpers'),

 *     'Console'                   => array('/path/to/consoles', '/next/path/to/consoles'),

 *     'Console/Command'           => array('/path/to/commands', '/next/path/to/commands'),

 *     'Console/Command/Task'      => array('/path/to/tasks', '/next/path/to/tasks'),

 *     'Lib'                       => array('/path/to/libs', '/next/path/to/libs'),

 *     'Locale'                    => array('/path/to/locales', '/next/path/to/locales'),

 *     'Vendor'                    => array('/path/to/vendors', '/next/path/to/vendors'),

 *     'Plugin'                    => array('/path/to/plugins', '/next/path/to/plugins'),

 * ));

 *

 */



/**

 * Custom Inflector rules, can be set to correctly pluralize or singularize table, model, controller names or whatever other

 * string is passed to the inflection functions

 *

 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));

 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));

 *

 */



/**

 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call

 * Uncomment one of the lines below, as you need. make sure you read the documentation on CakePlugin to use more

 * advanced ways of loading plugins

 *

 * CakePlugin::loadAll(); // Loads all plugins at once

 * CakePlugin::load('DebugKit'); //Loads a single plugin named DebugKit

 *

 */



/**

 * You can attach event listeners to the request lifecycle as Dispatcher Filter . By Default CakePHP bundles two filters:

 *

 * - AssetDispatcher filter will serve your asset files (css, images, js, etc) from your themes and plugins

 * - CacheDispatcher filter will read the Cache.check configure variable and try to serve cached content generated from controllers

 *

 * Feel free to remove or add filters as you see fit for your application. A few examples:

 *

 * Configure::write('Dispatcher.filters', array(

 *		'MyCacheFilter', //  will use MyCacheFilter class from the Routing/Filter package in your app.

 *		'MyPlugin.MyFilter', // will use MyFilter class from the Routing/Filter package in MyPlugin plugin.

 * 		array('callable' => $aFunction, 'on' => 'before', 'priority' => 9), // A valid PHP callback type to be called on beforeDispatch

 *		array('callable' => $anotherMethod, 'on' => 'after'), // A valid PHP callback type to be called on afterDispatch

 *

 * ));

 */

Configure::write('Dispatcher.filters', array(

	'AssetDispatcher',

	'CacheDispatcher'

));

App::uses('CakeLog', 'Log');

CakeLog::config('debug', array(

	'engine' => $config['debug']['engine'],

	'types' => $config['debug']['types'],

	'file' => $config['debug']['file'],

));

CakeLog::config('error', array(

	'engine' => $config['error']['engine'],

	'types' => $config['error']['types'],

	'file' => $config['error']['file'],

));

// Pfade zu den Ordern anpassen
if(isset($config['main']['owner_extension']) && $config['main']['owner_extension'] != ''){

	if(is_dir(implode(DS,explode(' ',$config['main']['owner_extension']))) === true){
		$owner_extension = implode(DS,explode(' ',$config['main']['owner_extension']));
	} else {
		$owner_extension = ROOT;
	}
} else {
	$owner_extension = ROOT;
}

$OwnerArray = explode(' ',$config['main']['owner']);

if(count($OwnerArray) > 1){
	$Owner = NULL;
	foreach($OwnerArray as $_key => $_owner){
		if($_key > 0) $Owner .= DS;
		$Owner .= $_owner;
	}
} else {
	$Owner = $config['main']['owner'];
}

// Pfade konfigurieren
Configure::write('data_folder_name', $Owner);
Configure::write('root_folder', $owner_extension.DS.$Owner.DS);

@mkdir(Configure::read('root_folder'), 0775, true);
foreach($config['path_inter'] as $_key => $_path){
	Configure::write($_key, Configure::read('root_folder') . implode(DS,explode(' ',$_path['path'])). DS);
	@mkdir(Configure::read($_key), $_path['right'], true);

}

foreach($config['path_extern'] as $_key => $_path){
	Configure::write($_key, WWW_ROOT . implode(DS,explode(' ',$_path['path'])). DS);
	@mkdir(Configure::read($_key), $_path['right'], true);

}

App::build(array('Model' => array(Configure::read('root_folder').$config['main']['models'].DS)));


foreach($config['functions'] as $_key => $_functions){
	Configure::write($_key, $_functions);
}

$SubdivisionValues = array();

foreach($config['navigation']['SubdivisionValues'] as $_key => $_SubdivisionValues){
	$SubdivisionValues[$_key] = __($_SubdivisionValues, true);
}

Configure::write('SubdivisionValues',array($SubdivisionValues));
Configure::write('SubdivisionMax',$config['navigation']['SubdivisionMax']);
Configure::write('SubdivisionSingleHidden', $config['navigation']['SubdivisionSingleHidden']);

/*
$breadcrumpList = array();

foreach($config['breadcrumpList'] as $_key => $_breadcrumpList){
	foreach($_breadcrumpList as $__key => $__breadcrumpList){
		$breadcrumpList[$__key][$_key] = $__breadcrumpList;
	}
}
*/

Configure::write('breadcrumpList', array(

	0 => array('param'=>'projectID', 'controller'=>'equipmenttypes', 'action'=>'overview', 'show'=>true, 'idForSkip'=>null),

	1 => array('param'=>'cascadeID', 'controller'=>'cascades', 'action'=>'index', 'show'=>true, 'idForSkip'=>null),

	2 => array('param'=>'orderID', 'controller'=>'reportnumbers', 'action'=>'index', 'show'=>true, 'idForSkip'=>null),

	3 => array('param'=>'reportID', 'controller'=>'reportnumbers', 'action'=>'show', 'show'=>true, 'idForSkip'=>null),

	4 => array('param'=>'reportnumberID', 'controller'=>'reportnumbers', 'action'=>null, 'show'=>true, 'idForSkip'=>null),

	5 => array('param'=>'evalId', 'controller'=>'reportnumbers', 'action'=>'editevaluation', 'show'=>true, 'idForSkip'=>null),

	6 => array('param'=>'weldedit', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	7 => array('param'=>'count', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	8 => array('param'=>'dropdown', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	9 => array('param'=>'dropdownID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	10 => array('param'=>'dependencyID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	11 => array('param'=>'dependencyokayID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	12 => array('param'=>'linkinOkay', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	13 => array('param'=>'examiniererID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	14 => array('param'=>'dependencyID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	15 => array('param'=>'examinerID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	16 => array('param'=>'deviceID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	17 => array('param'=>'certificateID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

/*


	5 => array('param'=>'reportnumberID', 'controller'=>'reportnumbers', 'action'=>null, 'show'=>true, 'idForSkip'=>null),



	8 => array('param'=>'dropdown', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	9 => array('param'=>'count', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	10 => array('param'=>'dropdownID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	11 => array('param'=>'linkinID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	12 => array('param'=>'linkinOkay', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	13 => array('param'=>'examiniererID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	14 => array('param'=>'dependencyID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	15 => array('param'=>'examinerID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	16 => array('param'=>'deviceID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null),

	17 => array('param'=>'certificateID', 'controller'=>null, 'action'=>null, 'show'=>false, 'idForSkip'=>null)
*/
));

Configure::write('FileUploadErrors',array(
        0 => __('The file uploaded with success',true),
        1 => __('The uploaded file exceeds the max filesize',true),
        2 => __('The uploaded file exceeds the max filesize directive that was specified in the HTML form',true),
        3 => __('The uploaded file was only partially uploaded',true),
        4 => __('No file was uploaded',true),
        6 => __('Missing a temporary folder',true)
		)
	);

if(Configure::read('debug') > 0){
	if(!file_exists(ROOT.DS.'config'.DS.'_current'.DS.'database.ini')){
		die('The config file could not be loaded. (4)');
	}

	$config = parse_ini_file(ROOT.DS.'config'.DS.'_current'.DS.'database.ini',true);

	if(FULL_BASE_URL != $config['wwwroot']['url']){
		Configure::write('databaseinfo_dev',$config);
	}
}

$odbs = Configure::read('overwriteDbSetting');
if($odbs == true && isset($_SERVER['REQUEST_URI'])) {
    $test = $_SERVER['REQUEST_URI'];
    $test = explode('/',$test);

    isset($test[3])?$projid = $test[3]:$projid=''; // auf numerischen Wert überprüfen

    if(is_numeric($projid) && $projid <> 0){

        $Settings = array();
        App::uses('ClassRegistry', 'Utility');
        $Settings = ClassRegistry::init("Setting")->find('first',array('conditions'=>array('Setting.topproject_id' => $projid)));
        if(isset($Settings['Setting'])){
                foreach ($Settings['Setting'] as $key => $value) {
                    $schema =  ClassRegistry::init("Setting")->schema($key);
                    $nkey = str_replace('0', '.', $key);

                    $nSettings [$nkey] = $value;
                    if(($value == 1 || $value  == 0) && $schema['type'] == 'integer'&& $nkey <> 'CloseMethodeTime' && $nkey <> 'RefreshReportTime' && $nkey <> 'SignatoryKeepOpen' && $nkey <> 'sendreportminstatus' && $nkey <> 'requiredsignval') {
                        $value == 0 ? Configure::write($nkey,false) : Configure::write($nkey,true);

                    }
                    else Configure::write($nkey,$value);
    //}

                }
        }
    }
}

CakePlugin::load('JwtAuth');
