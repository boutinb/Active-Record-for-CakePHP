<?php
/**
 * PostFixture
 *
 */
class TPostFixture extends CakeTestFixture {
   public $import = 'TPost';

/**
 * Fields
 *
 * @var array
 */
//	public $fields = array(
//		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
//		'writer_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'index'),
//		'title' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
//		'message' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
//		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'FK_posts_writer' => array('column' => 'writer_id', 'unique' => 0)),
//		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
//	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array('id' => 1, 'writer_id' => 1, 'title' => 'Title1', 'message' => 'Message1'),
		array('id' => 2, 'writer_id' => 1, 'title' => 'Title2', 'message' => 'Message2'),
		array('id' => 3, 'writer_id' => 1, 'title' => 'Title3', 'message' => 'Message3'),
	);
}
