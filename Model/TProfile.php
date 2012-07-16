<?php
App::uses('AppModel', 'Model');
/**
 * Profile Model
 *
 * @property Writer $Writer
 */
class TProfile extends AppModel {
/**
 * Use database config
 *
 * @var string
 */
	public $useDbConfig = 'test';

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Writer' => array(
			'className' => 'TWriter',
			'foreignKey' => 'writer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
