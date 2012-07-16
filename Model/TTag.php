<?php
App::uses('AppModel', 'Model');
/**
 * Tag Model
 *
 * @property JoinPost $JoinPost
 */
class TTag extends AppModel {
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
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Posts' => array(
			'className' => 'TPost',
			'joinTable' => 't_join_post_tags',
			'foreignKey' => 'tag_id',
			'associationForeignKey' => 'post_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

}
