<?php
class TJoinPostTagFixture extends CakeTestFixture {
   public $import = 'TJoinPostTag';


	public $records = array(
		array('id' => 1, 'post_id' => 1, 'tag_id' => 1),
		array('id' => 2, 'post_id' => 2, 'tag_id' => 1),
		array('id' => 3, 'post_id' => 2, 'tag_id' => 2),
	);
}
?>
