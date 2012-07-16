<?php
App::uses('AppModel', 'Model');
/**
 * WriterGroup Model
 *
 * @property Writer $Writer
 */
class TWriterGroup extends AppModel {
/**
 * Use database config
 *
 * @var string
 */
	public $useDbConfig = 'test';
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Writers' => array(
			'className' => 'TWriter',
			'foreignKey' => 'writer_group_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
