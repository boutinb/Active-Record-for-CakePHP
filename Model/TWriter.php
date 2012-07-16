<?php
App::uses('AppModel', 'Model');
/**
 * Writer Model
 *
 * @property Profile $Profile
 * @property WriterGroup $WriterGroup
 * @property Post $Post
 */
class TWriter extends AppModel {
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
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'Profile' => array(
			'className' => 'TProfile',
			'foreignKey' => 'writer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'WriterGroup' => array(
			'className' => 'TWriterGroup',
			'foreignKey' => 'writer_group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Posts' => array(
			'className' => 'TPost',
			'foreignKey' => 'writer_id',
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
