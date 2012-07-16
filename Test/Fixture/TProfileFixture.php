<?php
/**
 * ProfileFixture
 *
 */
class TProfileFixture extends CakeTestFixture {
   public $import = 'TProfile';


/**
 * Fields
 *
 * @var array
 */
//	public $fields = array(
//		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
//		'writer_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'index'),
//		'gender' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
//		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'FK_profiles_writer' => array('column' => 'writer_id', 'unique' => 0)),
//		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
//	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array('id' => 1,'writer_id' => 1,'gender' => 1, 'tel' => '123'),
		array('id' => 2,'writer_id' => 2,'gender' => 0, 'tel' => '345')
	);
}
