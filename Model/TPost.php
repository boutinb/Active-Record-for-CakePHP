<?php

App::uses('AppModel', 'Model');

/**
 * Post Model
 *
 * @property Writer $Writer
 * @property JoinPostTag $JoinPostTag
 */
class TPost extends AppModel {

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
   public $displayField = 'title';

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
   public $hasMany = array(
       'Comments' => array(
           'className' => 'TComment',
           'foreignKey' => 'post_id',
       )
   );
   public $hasAndBelongsToMany = array(
       'Tags' => array(
           'className' => 'TTag',
           'joinTable' => 't_join_post_tags',
           'foreignKey' => 'post_id',
           'associationForeignKey' => 'tag_id',
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
