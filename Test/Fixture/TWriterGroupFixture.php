<?php
/**
 * WriterGroupFixture
 *
 */
class TWriterGroupFixture extends CakeTestFixture {
   public $import = 'TWriterGroup';

/**
 * Fields
 *
 * @var array
 */
//	public $fields = array(
//		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
//		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
//		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
//		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
//	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'name' => 'Group1'
		),
	);
}
