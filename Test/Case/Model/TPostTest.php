<?php
App::uses('TPost', 'Model');
App::uses('ARTPost', 'Model/ActiveRecord');
App::uses('ARTProfile', 'Model/ActiveRecord');
App::uses('ARTComment', 'Model/ActiveRecord');
App::uses('ARTWriter', 'Model/ActiveRecord');
App::uses('ARTWriterGroup', 'Model/ActiveRecord');

/**
 * Post Test Case
 *
 */
class TPostTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.t_post', 'app.t_writer', 'app.t_writer_group', 'app.t_join_post_tag', 'app.t_profile', 'app.t_tag', 'app.t_comment');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TPost = ClassRegistry::init('TPost');
		$this->TComment = ClassRegistry::init('TComment');
		$this->TWriter = ClassRegistry::init('TWriter');
      $this->TWriterGroup = ClassRegistry::init('TWriterGroup');
		$this->TProfile = ClassRegistry::init('TProfile');
	}

   // Update Post title
   public function testUpdate() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post->title = 'Test';
      $this->assertEquals($post->title, 'Test');
      $post->save();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post->title, 'Test');
   }

   // Delete a Post
   public function testDelete() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post->delete();
      $post->save();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post, null);
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post, null);
   }

   // Delete a Post and update it: post must be deleted without being updated afterwards
   public function testDeleteAndUpdate() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post->delete();
      $post->message = 'TestDeleteAndUpdate';
      $post->save();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post, null);
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post, null);
   }

   // Create a new Post
   public function testCreate() {
      ActiveRecord::clearPool();
      $post = new ARTPost(array('title' => 'TestTitle', 'message' => 'TestMessage', 'writer_id' => 1));
      $post->save();
      $id = $post->id;
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => $id), 'activeRecord' => true));
      $this->assertEquals($post->title, 'TestTitle');
      $this->assertEquals($post->message, 'TestMessage');
   }

   public function testCreateAndDelete() {
      ActiveRecord::clearPool();
      $post_count = $this->TPost->find('count');
      $post = new ARTPost(array('title' => 'TestTitle', 'message' => 'TestMessage1', 'writer_id' => 1));
      $post->delete();
      $post->save();
      $this->assertEquals($post_count, $this->TPost->find('count'));
      $post = new ARTPost(array('title' => 'TestTitle', 'message' => 'TestMessage2', 'writer_id' => 1));
      $post->delete();
      ActiveRecord::saveAll();
      $this->assertEquals($post_count, $this->TPost->find('count'));
   }

   // Update Post title, calls Refresh -> old title should be shown
   // Update Post title, requery Post -> old title should be shown
   public function testRefresh() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $org_title = $post->title;
      $post->title = 'Test';
      $post->refresh();
      $this->assertEquals($post->title, $org_title);
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post->title = 'Test';
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post->title, $org_title);
   }

   // Update Post Writer name, Refresh Post -> new Writer name should be shown
   // Update Post Writer name, requery Post with association -> old writer name should be shown
   public function testRefreshAssociation() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => 1, 'conditions' => array('TPost.id' => 1), 'activeRecord' => true));
      $org_name = $post->Writer->name;
      $new_name = 'TestName';
      $post->Writer->name = $new_name;
      $post->refresh();
      $this->assertEquals($post->Writer->name, $new_name);
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => 1, 'conditions' => array('TPost.id' => 1), 'activeRecord' => true));
      $post->Writer->name = $new_name;
      $post = $this->TPost->find('first', array('recursive' => 1, 'conditions' => array('TPost.id' => 1), 'activeRecord' => true));
      $this->assertEquals($post->Writer->name, $org_name);
   }

   public function testRefreshWithRecordsAssociation() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => 1, 'conditions' => array('TPost.id' => 1), 'activeRecord' => true));
      $org_name = $post->Writer->name;
      $new_name = 'TestName';
      $post->Writer->name = $new_name;
      $post_records = $this->TPost->find('first', array('recursive' => 1, 'conditions' => array('TPost.id' => 1), 'activeRecord' => false));
      $post->refresh($post_records);
      $this->assertEquals($post->Writer->name, $org_name);
   }

   // Post belongs to a Writer
   // Change the Writer of a Post
   public function testSetBelongsTo() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post->Writer->name, 'Name1');
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('name' => 'Name2'), 'activeRecord' => true));
      $post->Writer = $writer;
      $this->assertEquals($post->Writer->name, 'Name2');
      $post->save();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($post->Writer->name, 'Name2');
   }

   // Writer may belong to a WriterGroup
   // Set the WriterGroup of a Writer to null
   public function testSetToNullBelongsTo() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($writer->WriterGroup->name, 'Group1');
      $writer_group = $writer->WriterGroup;
      $writer->WriterGroup = null;
      $this->assertNull($writer->WriterGroup);
      $writer->save();
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertNull($writer->WriterGroup);
      $writer->WriterGroup = null;
      $this->assertNull($writer->WriterGroup);
      $writer->WriterGroup = $writer_group;
      $this->assertEquals($writer->WriterGroup->name, 'Group1');
   }


   // Comment belongs to a Post
   // Change the Post of a Comment with a new Post record
   public function testSetWithNew1BelongsTo() {
      ActiveRecord::clearPool();
      $comment = $this->TComment->find('first', array('recursive' => -1, 'conditions' => array('message' => 'Message1'), 'activeRecord' => true));
      $old_post = $comment->Post;
      $new_post = new ARTPost(array('Writer' => $old_post->Writer, 'title' => 'TestTitle', 'message' => 'TestMessage'));
      $comment->Post = $new_post;
      $this->assertEquals($new_post->save(), true);
      $this->assertEquals($comment->save(), true);
      $this->assertEquals($comment->Post->title, 'TestTitle');
      $this->assertEquals($new_post->Comments[0]->message, 'Message1');
      ActiveRecord::clearPool();
      $comment = $this->TComment->find('first', array('recursive' => -1, 'conditions' => array('message' => 'Message1'), 'activeRecord' => true));
      $this->assertEquals($comment->Post->title, 'TestTitle');
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('title' => 'TestTitle'), 'activeRecord' => true));
      $this->assertEquals($post->Comments[0]->message, 'Message1');
   }

   // Comment belongs to a Post
   // Post & Comment are both new records
   public function testSetWithNew2BelongsTo() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $new_post = new ARTPost(array('Writer' => $writer, 'title' => 'TestTitle', 'message' => 'TestMessage'));
      $new_comment = new ARTComment(array(
         'message' => 'TestMessage',
         'Post' => $new_post));
      $this->assertEquals($new_post->save(), true);
      $this->assertEquals($new_comment->save(), true);
      $this->assertEquals($new_post->Comments[0]->message, 'TestMessage');
      $this->assertEquals($new_comment->Post->title, 'TestTitle');
      ActiveRecord::clearPool();
      $comment = $this->TComment->find('first', array('recursive' => -1, 'conditions' => array('message' => 'TestMessage'), 'activeRecord' => true));
      $this->assertEquals($comment->Post->title, 'TestTitle');
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('title' => 'TestTitle'), 'activeRecord' => true));
      $this->assertEquals($post->Comments[0]->message, 'TestMessage');
   }

   // Writer has one Profile
   // Change the Profile of a Writer
   public function testSetHasOne() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $old_profile = $writer->Profile;
      $this->assertEquals($old_profile->gender, 1);
      $new_profile = $this->TProfile->find('first', array('recursive' => -1, 'conditions' => array('gender' => 0), 'activeRecord' => true));
      $writer->Profile = $new_profile;
      $old_profile->delete();
      $this->assertEquals($writer->Profile->gender, 0);
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($writer->Profile->gender, 0);
   }

   // Writer has one Profile with deleteWhenNotAssociated property set to true
   // This time, we don't have to delete the old profile.
   // Change the Profile of a Writer
   public function testSetHasOneWithDelete() {
      ActiveRecord::clearPool();
      $this->TWriter->hasOne['Profile']['deleteWhenNotAssociated'] = true;
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $old_profile = $writer->Profile;
      $old_profile_id = $old_profile->id;
      $this->assertEquals($old_profile->gender, 1);
      $new_profile = $this->TProfile->find('first', array('recursive' => -1, 'conditions' => array('gender' => 0), 'activeRecord' => true));
      $writer->Profile = $new_profile;
      $this->assertEquals($writer->Profile->gender, 0);
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($writer->Profile->gender, 0);
      $profile = $this->TProfile->find('first', array('recursive' => -1, 'conditions' => array('id' => $old_profile_id), 'activeRecord' => true));
      $this->assertFalse($profile);
   }

   // Writer may have one Profile
   // Set the Profile of a Writer to null: this will delete the profile
   public function testSetToNullHasOne() {
      ActiveRecord::clearPool();
      $this->TWriter->hasOne['Profile']['deleteWhenNotAssociated'] = true;
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertNotNull($writer->Profile);
      $profile = $writer->Profile;
      $tel = $profile->tel;
      $writer->Profile = null;
      $this->assertNull($writer->Profile);
      $this->assertTrue(ActiveRecord::saveAll()); // This will delete the profile
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertNull($writer->Profile);
      $writer->Profile = null;
      $this->assertNull($writer->Profile);
      $this->assertTrue(ActiveRecord::saveAll());
      $writer->Profile = $profile;
      $this->assertTrue(ActiveRecord::saveAll()); // This will create the profile
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($writer->Profile->tel, $tel);
   }



   // Writer has one Profile
   // Change the Profile of a Writer with a new Profile record
   public function testSetWithNew1HasOne() {
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('name' => 'Name1'), 'activeRecord' => true));
      $old_profile = $writer->Profile;
      $old_profile->delete();
      $new_profile = new ARTProfile(array('Writer' => $writer, 'gender' => 1, 'tel' => '888'));
      $writer->Profile = $new_profile;
      $this->assertEquals(ActiveRecord::saveAll(), true);
      $this->assertEquals($writer->Profile->tel, '888');
      $this->assertEquals($new_profile->Writer->name, 'Name1');
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('name' => 'Name1'), 'activeRecord' => true));
      $this->assertEquals($writer->Profile->tel, '888');
      ActiveRecord::clearPool();
      $profile = $this->TProfile->find('first', array('recursive' => -1, 'conditions' => array('tel' => '888'), 'activeRecord' => true));
      $this->assertEquals($profile->Writer->name, 'Name1');
   }

   // Writer has one Profile
   // Writer & Profile are both new records
   public function testSetWithNew2HasOne() {
      ActiveRecord::clearPool();
      $writer_group = $this->TWriterGroup->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $new_profile = new ARTProfile(array('gender' => 1, 'tel' => '999'));
      $new_writer = new ARTWriter(array(
         'name' => 'testUpdateWithNew2HasOne',
         'WriterGroup' => $writer_group,
         'Profile' => $new_profile));
      $this->assertEquals(ActiveRecord::saveAll(), true);
      $this->assertEquals($new_writer->Profile->tel, '999');
      $this->assertEquals($new_profile->Writer->name, 'testUpdateWithNew2HasOne');
      ActiveRecord::clearPool();
      $writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('name' => 'testUpdateWithNew2HasOne'), 'activeRecord' => true));
      $this->assertEquals($writer->Profile->tel, '999');
      ActiveRecord::clearPool();
      $profile = $this->TProfile->find('first', array('recursive' => -1, 'conditions' => array('tel' => '999'), 'activeRecord' => true));
      $this->assertEquals($profile->Writer->name, 'testUpdateWithNew2HasOne', 'Profile has for Writer ' . $profile->Writer->name . ' instead of testUpdateWithNew2HasOne');
   }

   // Post has many Comments
   // Update each message of the comments of one Post
   public function testUpdateHasMany() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
      foreach($post->Comments as $comment) {
         $comment->message .= 'TestHasMany';
      }
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
      foreach($post->Comments as $comment) {
         $this->assertStringEndsWith('TestHasMany', $comment->message);
      }
   }

   // A post has 0 or many comments
   // Set the comments from Post1 to Post2
   // -> Post1 loses its comments and Post2 gets the comments of Post1
   public function testSetToExistingRecordsHasMany() {
      $this->TPost->hasMany['Comments']['deleteWhenNotAssociated'] = true;
      ActiveRecord::clearPool();
      $post1 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post2 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $tags1 = $post1->Comments;
      $tags2 = $post2->Comments;
      $count2 = count($post2->Comments);
      $tag_names = array();
      foreach ($tags2 as $tag) {
         $tag_names[] = $tag->message;
      }
      $post1->Comments = $tags2;
      ActiveRecord::saveAll();
      $post1->refresh();
      $post2->refresh();
      $this->assertEquals($count2, count($post1->Comments));
      $i = 0;
      foreach ($post1->Comments as $tag) {
         $this->assertEquals($tag_names[$i], $tag->message);
         $i++;
      }
      $this->assertEquals(0, count($post2->Comments));
   }

   // A post has 0 or many comments
   // Set the comments from a Post to an array of new comments
   public function testSetToNewRecordsHasMany() {
      $this->TPost->hasMany['Comments']['deleteWhenNotAssociated'] = true;
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertGreaterThan(0, count($post->Comments));
      $comment1 = new ARTComment(array('message' => 'Test1'));
      $comment2 = new ARTComment(array('message' => 'Test2'));
      $comment3 = new ARTComment(array('message' => 'Test3'));
      $post->Comments = array($comment1, $comment2, $comment3);
      ActiveRecord::saveAll();
      $post->refresh();
      $this->assertEquals(3, count($post->Comments));
      $i = 1;
      foreach ($post->Comments as $comment) {
         $this->assertEquals('Test' . $i, $comment->message);
         $i++;
      }
   }


   // A post has 0 or many comments
   // Take a post that has 2 comments
   // Set the comments to this post to null : as the association has deleteWhenNotAssociated set to true, this
   // will delete the 2 comments
   // Save it and re-query again.
   // Set the 2 old comments to the post: this will re-create the 2 comments.
   public function testSetToNullHasMany() {
      $this->TPost->hasMany['Comments']['deleteWhenNotAssociated'] = true;
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
      $comments = $post->Comments;
      $comment1 = $comments[0];
      $comment2 = $comments[1];
      // Maybe a bit tricky, but the $post->Comments is the association. This association will lose
      // its 2 records. So we have to keep these 2 records in another array.
      $comments = array($comment1, $comment2);
      $post->Comments = null;
      $this->assertTrue(ActiveRecord::saveAll());   // This will delete the 2 comments
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 0);
      $post->Comments = null;
      $this->assertEquals(count($post->Comments), 0);
      $post->Comments = $comments;
      ActiveRecord::saveAll();                      // This will recreate the 2 comments
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
   }

   // Update the association to null without having been initialized before:
   // an association is initilized the first time it is called, but it must be
   // also initialized when it is set without being first called.
   public function testSetToNull2HasMany() {
      $this->TPost->hasMany['Comments']['deleteWhenNotAssociated'] = true;
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post->Comments = null;
      $this->assertTrue(ActiveRecord::saveAll());   // This will delete the 2 comments
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 0);
   }

   // Add a new Comment to a Post
   public function testAddHasMany() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post->Comments->add(new ARTComment(array('message' => 'Hallo')));
      $this->assertEquals(count($post->Comments), 3);
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 3);
      $found = false;
      foreach($post->Comments as $comment) {
         if ($comment->message == 'Hallo') {
            $found = true;
         }
      }
      $this->assertEquals($found, true);
   }

   // Remove one Comment to a Post
   public function testRemoveHasMany() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
      $first_comment = $post->Comments[0];
      $post->Comments->remove($first_comment);
      $this->assertEquals(count($post->Comments), 1);
      $first_comment->delete();
      $this->assertEquals(ActiveRecord::saveAll(), true);
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 1);
   }

   // Remove a new Comment to a Post
   public function testRemoveNewHasMany() {
      $this->TPost->hasMany['Comments']['deleteWhenNotAssociated'] = true;
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $count = count($post->Comments);
      $new_comment = new ARTComment(array('message' => 'TestRemoveNewHasMany'));
      $post->Comments->add($new_comment);
      $post->Comments->remove($new_comment);
      $this->assertTrue(ActiveRecord::saveAll());
      $this->assertEquals($count, count($post->Comments));
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals($count, count($post->Comments));
   }

   // Replace one Comment by a new Comment in a Post
   public function testReplaceHasMany() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
      $first_comment = $post->Comments[0];
      $old_message = $first_comment->message;
      $new_message = 'Hello';
      $post->Comments->replace($first_comment, new ARTComment(array('message' => $new_message)));
      $first_comment->delete();
      $post->saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertEquals(count($post->Comments), 2);
      $old_message_exists = false;
      $new_message_exists = false;
      foreach($post->Comments as $comment) {
         if ($comment->message == $old_message) {
            $old_message_exists = true;
         }
         if ($comment->message == $new_message) {
            $new_message_exists = true;
         }
      }
      $this->assertEquals($new_message_exists, true);
      $this->assertEquals($old_message_exists, false);

   }
   
   
   public function testSwitchRecordsInHasManyAssociation() {
      $this->TPost->hasMany['Comments']['deleteWhenNotAssociated'] = true;
      ActiveRecord::clearPool();
      $post1 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post2 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $count1 = count($post1->Comments);
      $count2 = count($post2->Comments);
      $message1 = $post1->Comments[0]->message;
      $message2 = $post2->Comments[0]->message;
      $comments1 = array();
      foreach ($post1->Comments as $comment) {
         $comments1[] = $comment;
      }
      $post1->Comments = $post2->Comments;
      $post2->Comments = $comments1;
      
      $this->assertEquals($count1, count($post2->Comments));
      $this->assertEquals($count2, count($post1->Comments));
      $this->assertEquals($message2, $post1->Comments[0]->message);
      $this->assertEquals($message1, $post2->Comments[0]->message);
      
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $post1 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post2 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals($count1, count($post2->Comments));
      $this->assertEquals($count2, count($post1->Comments));
      $this->assertEquals($message1, $post2->Comments[0]->message);
      $this->assertEquals($message2, $post1->Comments[0]->message);
   }


   // A Post has many Tags (and Tags has many Posts
   // Update the name of all Tags of a Post
   public function testUpdateHBTM() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
      foreach($post->Tags as $id => $tag) {
         $tag->name .= 'TestHBTM';
      }
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
      foreach($post->Tags as $id => $tag) {
         $this->assertStringEndsWith('TestHBTM', $tag->name);
      }
   }

   // A Post has 0 or many Tags and a Tag has 0 or many Posts
   // Set the Tags from Post1 to Post2
   // -> Post1 keeps its Tags and Post2 gets the Tags of Post1
   public function testSetToExistingRecordsHBTM() {
      ActiveRecord::clearPool();
      $post1 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $post2 = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $tags2 = $post2->Tags;
      $count = count($post2->Tags);
      $tag_names = array();
      foreach ($tags2 as $tag) {
         $tag_names[] = $tag->name;
      }
      $post1->Tags = $tags2;
      ActiveRecord::saveAll();
      $post1->refresh();
      $post2->refresh();
      $this->assertEquals($count, count($post1->Tags));
      $i = 0;
      foreach ($post1->Tags as $tag) {
         $this->assertEquals($tag_names[$i], $tag->name);
         $i++;
      }
      $this->assertEquals($count, count($post2->Tags));
      $i = 0;
      foreach ($post2->Tags as $tag) {
         $this->assertEquals($tag_names[$i], $tag->name);
         $i++;
      }
   }

   // A Post has 0 or many Tags and a Tag has 0 or many Posts
   // Set the tags from a Post to an array of new tags
   public function testSetToNewRecordsHBTM() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $this->assertGreaterThan(0, count($post->Tags));
      $tag1 = new ARTTag(array('name' => 'Test1'));
      $tag2 = new ARTTag(array('name' => 'Test2'));
      $tag3 = new ARTTag(array('name' => 'Test3'));
      $post->Tags = array($tag1, $tag2, $tag3);
      ActiveRecord::saveAll();
      $post->refresh();
      $this->assertEquals(3, count($post->Tags));
      $i = 1;
      foreach ($post->Tags as $tag) {
         $this->assertEquals('Test' . $i, $tag->name);
         $i++;
      }
   }

   // Set the Tags of a Post to null
   public function testSetToNullHBTM() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
      $tags = $post->Tags;
      $tag1 = $tags[0];
      $tag2 = $tags[1];
      $tags = array($tag1, $tag2);
      $post->Tags = null;
      $this->assertEquals(count($post->Tags), 0);
      ActiveRecord::saveAll();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 0);
      $post->Tags = null;
      $this->assertEquals(count($post->Tags), 0);
      $post->Tags = $tags;
      $this->assertEquals(count($post->Tags), 2);
      ActiveRecord::saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
   }

   public function testAddHBTM() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $new_name = 'Hallo';
      $post->Tags->add(new ARTTag(array('name' => $new_name)));
      $this->assertEquals(count($post->Tags), 3);
      $post->saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 3);
      $found = false;
      foreach($post->Tags as $tag) {
         $name = $tag->name;
         if ($tag->name == $new_name) {
            $found = true;
         }
      }
      $this->assertEquals($found, true);
   }

   public function testRemoveHBTM() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
      $post->Tags->remove($post->Tags[0]);
      $post->saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 1);
   }

   public function testReplaceHBTM() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
      $first_tag = $post->Tags[0];
      $old_name = $first_tag->name;
      $new_name = 'Hello';
      $post->Tags->replace($first_tag, new ARTTag(array('name' => $new_name)));
      $post->saveAll();
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $this->assertEquals(count($post->Tags), 2);
      $old_name_exists = false;
      $new_name_exists = false;
      foreach($post->Tags as $tag) {
         if ($tag->name == $old_name) {
            $old_name_exists = true;
         }
         if ($tag->name == $new_name) {
            $new_name_exists = true;
         }
      }
      $this->assertEquals($new_name_exists, true);
      $this->assertEquals($old_name_exists, false);
   }

   public function testUndoAll() {
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => 1), 'activeRecord' => true));
      $org_message = $post->message;
      $post->message = 'TestUndoAll';
      $new_writer = $this->TWriter->find('first', array('recursive' => -1, 'conditions' => array('id' => 2), 'activeRecord' => true));
      $org_writer_name = $post->Writer->name;
      $this->assertNotEquals($org_writer_name, $new_writer->name);
      $post->Writer = $new_writer;
      $org_comments_count = count($post->Comments);
      $post->Comments->add(new ARTComment(array('message' => 'TestMessage')));
      $org_tags_count = count($post->Tags);
      $this->assertGreaterThan(0, $org_tags_count);
      $post->Tags = null;

      ActiveRecord::undoAll();
      $this->assertEquals($org_message, $post->message);
      $this->assertEquals($org_writer_name, $post->Writer->name);
      $this->assertEquals($org_comments_count, count($post->Comments));
      $this->assertEquals($org_tags_count, count($post->Tags));
      ActiveRecord::saveAll();
      $this->assertEquals($org_message, $post->message);
      $this->assertEquals($org_writer_name, $post->Writer->name);
      $this->assertEquals($org_comments_count, count($post->Comments));
      $this->assertEquals($org_tags_count, count($post->Tags));
   }

   public function testCreateSaveAllAndUpdate() {
      ActiveRecord::clearPool();
      $post = new ARTPost(array('title' => 'TestTitle', 'message' => 'TestMessage', 'writer_id' => 1));
      ActiveRecord::saveAll();
      $new_title = 'Test2Title';
      $post->title = $new_title;
      ActiveRecord::saveAll();
      $id = $post->id;
      ActiveRecord::clearPool();
      $post = $this->TPost->find('first', array('recursive' => -1, 'conditions' => array('id' => $id), 'activeRecord' => true));
      $this->assertEquals($new_title, $post->title);
   }
   
/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TPost);

		parent::tearDown();
	}

}
