Active-Record-for-CakePHP
=========================

Active Record for CakePHP

I wanted to build a state engine with CakePHP, and I realize that I needed a kind of Active Record pattern. 
So I first build a bahavior that allows me to retrieve objects in place of associative arrays.
To use it:
. Copy the ActiveRecordBehavior.php in your Behavior folder
. Tell your model to use it: $actsAs = array('ActiveRecord' => array(<options>))
. When you use a find('all') or find('first) function, add the option 'activeRecord' => true

I chose this way, because I did not want to retrieve always objects when a find function was called. 
But it is possible to use it in another way: add in the constructor of the behavior the option 'allFind' => true, and if you do not want an object after a find add 'activeRecord' => false (this possibility was not yet thouroughly tested: i'm afraid that cake generates sometimes a 'find' call that needs associative arrays).

Anyway, when you retrieve an object record, you can use it in this way:


