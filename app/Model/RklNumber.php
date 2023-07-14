<?php
App::uses('AppModel', 'Model');
/**
 * Topproject Model
 *
 *
 */
class RklNumber extends AppModel {


  public $hasMany = array(
		'RklsAnsiBranchConnection' => array(
			'className' => 'RklsAnsiBranchConnection',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiBranchHeadData' => array(
			'className' => 'RklsAnsiBranchHeadData',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiBranchTypAB' => array(
			'className' => 'RklsAnsiBranchTypAB',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiFlangedPipesLenght' => array(
			'className' => 'RklsAnsiFlangedPipesLenght',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiFluid' => array(
			'className' => 'RklsAnsiFluid',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiPipingClassBasicData' => array(
			'className' => 'RklsAnsiPipingClassBasicData',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiPipingClassNote' => array(
			'className' => 'RklsAnsiPipingClassNote',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiPipingClassPart' => array(
			'className' => 'RklsAnsiPipingClassPart',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiPipingClassTorque' => array(
			'className' => 'RklsAnsiPipingClassTorque',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiPipingClassTorquesNote' => array(
			'className' => 'RklsAnsiPipingClassTorquesNote',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiPressureTemp' => array(
			'className' => 'RklsAnsiPressureTemp',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiReducersTableId' => array(
			'className' => 'RklsAnsiReducersTableId',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiWallThickness' => array(
			'className' => 'RklsAnsiWallThickness',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),
    'RklsAnsiWallThicknessNoteWt' => array(
			'className' => 'RklsAnsiWallThicknessNoteWt',
			'foreignKey' => 'rkl_number_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		),

	);


}
?>
