<?php
/**
 * WriterFixture
 *
 */
class TWriterFixture extends CakeTestFixture {
   public $import = 'TWriter';
/**
 * Fields
 *
 * @var array
 */
//	public $fields = array(
//		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
//		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
//		'writer_group_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
//		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'FK_writers_group' => array('column' => 'writer_group_id', 'unique' => 0)),
//		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
//	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array('id' => 1,'name' => 'Name1','writer_group_id' => 1),
		array('id' => 2,'name' => 'Name2','writer_group_id' => 1),
	);
}
