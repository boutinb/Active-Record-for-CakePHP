<?php
App::uses('TWriter', 'Model');

/**
 * Writer Test Case
 *
 */
class TWriterTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.t_writer_group', 'app.t_writer', 'app.t_profile', 'app.t_post', 'app.t_join_post_tag', 'app.t_tag');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TWriter = ClassRegistry::init('TWriter');
	}

   private function _checkARTWriter($writer, $id, $name, $writer_group_id) {
      $this->assertEquals($writer->id, $id);
      $this->assertEquals($writer->name, $name);
      $this->assertEquals($writer->writer_group_id, $writer_group_id);
   }

   private function _checkARTWriterGroup($writer_group, $id, $name) {
      $this->assertEquals($writer_group->id, $id);
      $this->assertEquals($writer_group->name, $name);
   }

   private function _checkARTProfile($profile, $id, $writer_id, $gender, $tel) {
      $this->assertEquals($profile->id, $id);
      $this->assertEquals($profile->writer_id, $writer_id);
      $this->assertEquals($profile->gender, $gender);
      $this->assertEquals($profile->tel, $tel);
   }

   private function _checkARTPost($post, $id, $writer_id, $title, $message) {
      $this->assertEquals($post->id, $id);
      $this->assertEquals($post->writer_id, $writer_id);
      $this->assertEquals($post->title, $title);
      $this->assertEquals($post->message, $message);
   }

   private function _checkARTTag($tag, $id, $name) {
      $this->assertEquals($tag->id, $id);
      $this->assertEquals($tag->name, $name);
   }

   public function testActiveRecordFalse() {
      ActiveRecord::clearPool();
      $writers = $this->TWriter->find('all', array('recursive' => -1, 'activeRecord' => false));
      foreach ($writers as $writer) {
         $this->assertInternalType('array', $writer);
      }
   }

   public function testFindAll() {
      ActiveRecord::clearPool();
      $writers = $this->TWriter->find('all', array('recursive' => -1, 'activeRecord' => true));
      foreach ($writers as $id => $writer) {
         $this->assertInstanceOf('ARTWriter', $writer);
         $this->_checkARTWriter($writer, $id + 1, 'Name' . ($id + 1), 1);
      }
   }

   public function testFindFirst() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $this->assertInstanceOf('ARTWriter', $writer);
      $this->_checkARTWriter($writer, 1, 'Name1', 1);
   }

   public function testAssociationBelongsToDirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('contain' => array('WriterGroup'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $writer_group = $writer->WriterGroup;
      $this->assertInstanceOf('ARTWriterGroup', $writer_group);
      $this->_checkARTWriterGroup($writer_group, '1', 'Group1');
   }

   public function testAssociationBelongsToIndirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $writer_group = $writer->WriterGroup;
      $this->assertInstanceOf('ARTWriterGroup', $writer_group);
      $this->_checkARTWriterGroup($writer_group, '1', 'Group1');
   }
   
   public function testPoolAndBelongsTo() {
      ActiveRecord::clearPool();
      $writer_org = $this->TWriter->find('first', array('contain' => array('WriterGroup'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $writer_org->name = 'Test';
      $writer_org->WriterGroup->name = 'Test';
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $writer_group = $this->TWriter->WriterGroup->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->_checkARTWriter($writer, 1, 'Name1', 1);
      $this->_checkARTWriterGroup($writer_group, '1', 'Group1');
      $this->_checkARTWriterGroup($writer->WriterGroup, '1', 'Group1');
   }

   public function testAssociationHasOneDirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('contain' => array('Profile'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $profile = $writer->Profile;
      $this->assertInstanceOf('ARTProfile', $profile);
      $this->_checkARTProfile($profile, '1', '1', '1', '123');
   }

   public function testAssociationHasOneIndirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $profile = $writer->Profile;
      $this->assertInstanceOf('ARTProfile', $profile);
      $this->_checkARTProfile($profile, '1', '1', '1', '123');
   }

   public function testPoolAndHasOne() {
      ActiveRecord::clearPool();
      $writer_org = $this->TWriter->find('first', array('contain' => array('Profile'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $writer_org->name = 'Test';
      $writer_org->Profile->gender = 2;
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $profile = $this->TWriter->Profile->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->_checkARTWriter($writer, 1, 'Name1', 1);
      $this->_checkARTProfile($profile, '1', '1', '1', '123');
      $this->_checkARTProfile($writer->Profile, '1', '1', '1', '123');
   }

   public function testAssociationHasManyDirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('contain' => array('Posts'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $posts = $writer->Posts;
      foreach ($posts as $id => $post) {
         $this->assertInstanceOf('ARTPost', $post);
         $this->_checkARTPost($post, ($id + 1), '1', 'Title' . ($id+1), 'Message' . ($id+1));
      }
   }

   public function testAssociationHasManyIndirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $posts = $writer->Posts;
      $id = 1;
      foreach ($posts as $post) {
         $this->assertInstanceOf('ARTPost', $post);
         $this->_checkARTPost($post, $id, '1', 'Title' . $id, 'Message' . $id);
         $id++;
      }
   }

   public function testPoolAndHasMany() {
      ActiveRecord::clearPool();
      $writer_org = $this->TWriter->find('first', array('contain' => array('Posts'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $writer_org->name = 'Test';
      foreach ($writer_org->Posts as $post) {
         $post->title = 'Test';
      }
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $posts = $this->TWriter->Posts->find('all', array('recursive' => -1, 'conditions' => array('writer_id' => 1), 'activeRecord' => true));
      $this->_checkARTWriter($writer, 1, 'Name1', 1);
      $id = 1;
      foreach ($posts as $post) {
         $this->_checkARTPost($post, $id, '1', 'Title' . $id, 'Message' . $id);
         $id++;
      }
      $id = 1;
      foreach ($writer->Posts as $post) {
         $this->_checkARTPost($post, $id, '1', 'Title' . $id, 'Message' . $id);
         $id++;
      }
   }

   private function _testHBTM($posts) {
      $this->assertEquals(count($posts), 3);
      foreach ($posts as $post) {
         $this->assertInstanceOf('ARTPost', $post);
         $tags = $post->Tags;
         switch ($post->id) {
            case 1: {
               $this->assertEquals(count($tags), 1);
               foreach ($tags as $tag) {
                  $this->assertInstanceOf('ARTTag', $tag);
                  $this->_checkARTTag($tag, 1, 'Tag1');
               }
               break;
            }
            case 2: {
               $this->assertEquals(count($tags), 2);
               foreach ($tags as $tag) {
                  $this->assertInstanceOf('ARTTag', $tag);
                  if ($tag->id == 1) {
                     $this->_checkARTTag($tag, 1, 'Tag1');
                  } else if ($tag->id == 2) {
                     $this->_checkARTTag($tag, 2, 'Tag2');
                  } else {
                     $this->assertEquals($tags, null);
                  }
               }
               break;
            }
            case 3: {
               $this->assertEquals(count($tags), 0);
               break;
            }
            default:
               $this->assertEquals(true, false);
         }
      }
   }

   public function testAssociationHBTMDirect() {
      ActiveRecord::clearPool();
      $posts = $this->TWriter->Posts->find('all', array('contain' => array('Tags'), 'activeRecord' => true));
      $this->_testHBTM($posts);
   }

   public function testAssociationHBTMIndirect() {
      ActiveRecord::clearPool();
      $posts = $this->TWriter->Posts->find('all', array('recursive' => -1, 'activeRecord' => true));
      $this->_testHBTM($posts);
   }

   private function _testDeepAssociation($writer) {
      $posts = $writer->Posts;
      $this->assertEquals(count($posts), 3);
      foreach ($posts as $post) {
         $this->assertInstanceOf('ARTPost', $post);
         $tags = $post->Tags;
         switch($post->id) {
            case 1: $this->assertEquals(count($tags), 1); break;
            case 2: $this->assertEquals(count($tags), 2); break;
            case 3: $this->assertEquals(count($tags), 0); break;
            default: $this->assertEquals(true, false);
         }
         foreach ($tags as $tag) {
            $this->assertInstanceOf('ARTTag', $tag);
         }
      }
   }

   public function testDeepAssociationDirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('contain' => array('Posts.Tags'), 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $this->_testDeepAssociation($writer);
   }

   public function testDeepAssociationIndirect() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('TWriter.id' => 1), 'activeRecord' => true));
      $this->_testDeepAssociation($writer);
   }


/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TWriter);

		parent::tearDown();
	}

}
