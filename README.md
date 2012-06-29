Active-Record-for-CakePHP
=========================

Active Record for CakePHP

I wanted to build a state engine with CakePHP, and I realize that I needed a kind of Active Record pattern. 
So I first build a bahavior that allows me to retrieve objects in place of associative arrays.
To use it:
* Copy the ActiveRecordBehavior.php in your Behavior folder
* Tell your model to use it: $actsAs = array('ActiveRecord' => array(<options>))
* When you use a find('all') or find('first) function, add the option 'activeRecord' => true

I chose this way, because I did not want to retrieve always objects when a find function was called. 
But it is possible to use it in another way: add in the constructor of the behavior the option 'allFind' => true, and if you do not want an object after a find add 'activeRecord' => false (this possibility was not yet thouroughly tested: i'm afraid that cake generates sometimes a 'find' call that needs associative arrays).

Anyway, when you retrieve an object record, you can use it in this way: assume that you have the following models:
* Post (title, message) belongsTo Writer, hasMany Comments, hasAndBelongsToMany Tags
* Writer (name) hasMany Posts, belongsTo WriterGroup
* WriterGroup (name) hasMany Writers
* Comment (message) belongsTo Post
* Tag (name) hasAndBelongsToMany Posts

Call for example find('first') to retrieve a Post then:
* $message = $post->message : this retrieves the message of the post
* $post->message = 'Hallo' : this updates the message of the post
* $writer = $post->Writer : this retrieves the writer ActiveRecord object
* $comments = $post->Comments : this retrieves the ActiveRecordAssociation object

This behavior makes a difference between belongsTo and hasOne associations with hasMany and hasAndBelongsToMany associations:
* with belongsTo and hasOne associations, the ActiveRecord object pointed by the association is retrieved, and you can use
it directly: e.g. $post->Writer->WriterGroup
* with hasMany and hasAndBelongsToMany associations, the ActiveRecordAssociation object is retrieved. The class of this object implements the IteratorAggregate, Countable, ArrayAccess interfaces so that you can use it as an array:
e.g.:  
foreach ($post->Comments as $comment);  
count($post->Comments);  
$comments = $post->Comments; $first_comment = $comments[0];  
But also, the ActiveRecordAssociation class has 3 functions:
  * $comments->add($new_comment);
  * $comments->remove($first_comment);
  * $comments->replace($first_comment, $new_comment);

In order for the developer to clearly see the difference beween the 2 kinds of associations, I advice to use a plural name for hasMany and hasAndBelongsToMany associations, and singular name for hasOne and belongsTo associations.

Per default, the object you retrieve is of the class ActiveRecord. But you can of course extend this class and make a special one for one model.
Per default, the behavior will look for a class in the subfolder Model\ActiveRecord with name 'AR<model name>', e.g.: ARPost or ARComment (the prefix 'AR' and the subfolder name can be changed in the bahavior constructor options).
In the file ARPost.php:
    <?php
    App::import('Model\Behavior', 'ActiveRecord');
    
    class ARTPost extends ActiveRecord {
        public $var;
        public function func() {...}
    }
    ?>

If you need to use the constructor:   
    <?php
    public function __construct(array $record, array $options = array()) {
       parent::__construct($record, $options);
       ...
    }
    ?>

    <?php
    App::import('Model\Behavior', 'ActiveRecord');
    
    class ARTPost extends ActiveRecord {
        public $var;
        public function func() {...}
    }
    ?>


Then you can use $post->var and $post->func() in your code.  
You can also create new object:
    $post = new ARPost(array(
       'title' => 'My title',
       'message' => 'OK',
       'Writer' => $writer));
Here it becomes to be quite nice: in place of telling that the writer_id of the post should $writer->id, you can directly say 'Writer' => $writer
You can also do:
    $post->Comments = array($comment);
This will set automatically the $comment->post_id to the right one. 

I realize that when using hasMany (or hasOne association), when you do
    $post->Comments->remove($comment);
You want not only to remove the $comment from $post, but most of the time you want to delete $comment. 
I thought it would quite handy if this is done automatically. For this, if you set in the association definition 'deleteWhenNotAssociated' to true, the behavior will automatically delete all records that are removed from the association.

The behavior offers also the possibility to delete, refresh and undo an ActiveRecord:
    $post->delete(); // delete this post record
    $post->refresh(); // query the values of post in the database.
    $post->undo(); // undo all modification done in the $post record.
    
The modifications done in the active records are not sent to the database. This is done only when calling the save() method.  
But $post->save() will only save the modification in the post record, not in its associated records. To save all modifications you made (explicitely and implicitely), use $post->saveAll() or ActiveRecord::saveAll() method
Morevover saveAll() takes care that the records are saved in the right order. For example:
    $comment = new ARComment(array('message' => 'New message'));
    $post = new ARPost(array('title' => 'New title', 'message' => 'New Message', 'Writer' => $writer));
    $post->Comment = array($comment);
    ActiveRecord::saveAll();
Then saveAll() takes care that $post is created first so that its id can be set to $comment->post_id

    








