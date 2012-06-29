<?php

class ActiveRecordException extends Exception {
   public function  __construct($message) {
      $trace = debug_backtrace();
      parent::__construct(
         $message .
         ' in ' . $trace[1]['file'] .
         ' on line ' . $trace[1]['line']);
      
   }
}

class _ActiveRecordAssociation {
   public $name;
   public $association;                // public part of the association
   public $reference_active_record;    // ActiveRecord that owns this association
   public $type;
   public $model;
   public $definition;                 // definition of the association as defined in cakePHP
   public $associated_active_records;  // Array of ActiveRecords associated to the $reference_active_record
   public $changed = false;            // Set to true when the association has been modified
   public $initialized = false;

   public function __construct($name, ActiveRecord $reference_active_record, $type, $definition, $record, $check_record) {
      $this->name = $name;
      $this->association = new ActiveRecordAssociation($this);
      $this->reference_active_record = $reference_active_record;
      $this->type = $type;
      $reference_model = $reference_active_record->getModel();
      $this->model = $reference_model->{$name};
      $this->definition = $definition;
      $this->associated_active_records = array();

      if (isset($record[$name])) {
         $this->_initializeWithRecord($record[$name], $check_record);
      }
   }

   private function _setAssociatedRecordsWithForeignKeys($active_records, $is_new_records = false) {
      if ($active_records == null) {
         $active_records = array();
      }
      if (!is_array($active_records) && !($active_records instanceof ActiveRecordAssociation)) {
         $active_records = array($active_records);
      }
      if ($this->type == 'belongsTo' || $this->type == 'hasOne') {
         if (count($active_records) > 1) {
            throw new ActiveRecordException('Too many records for a ' . $this->type . ' association (name: ' . $this->name . ')');
         }
         $new_active_record = (count($active_records) == 1) ? $active_records[0] : null;
         $old_active_record = (count($this->associated_active_records) == 1) ? $this->associated_active_records[0] : null;
         if ($old_active_record && $new_active_record) {
            $this->replaceAssociatedRecord($old_active_record, $new_active_record);
         } else if ($old_active_record == null && $new_active_record) {
            $this->addAssociatedRecord($new_active_record);
         } else if ($old_active_record && $new_active_record == null) {
            $this->removeAssociatedRecord($old_active_record);
         }
      } else {
         foreach($this->associated_active_records as $old_active_record) {
            $this->removeAssociatedRecord($old_active_record);
         }
         foreach($active_records as $new_active_record) {
            $this->addAssociatedRecord($new_active_record);
         }
      }

      if ($is_new_records) {
         $this->changed = true;
         //$this->reference_active_record->setChanged();
      }
      $this->initialized = true;
   }

   public function setAssociatedRecords($active_records) {
      if (!$this->initialized) {
         $this->_initialize();
      }
      $this->_setAssociatedRecordsWithForeignKeys($active_records, true);
   }

   public function setForeignKey(ActiveRecord $active_record = null) {
      switch($this->type) {
         case 'belongsTo':
            $reference_record = &$this->reference_active_record->getRecord();
            if ($active_record == null) {
               $reference_record[$this->definition['foreignKey']] = '';
            } else {
               $associated_record = &$active_record->getRecord();
               if (isset($associated_record[$active_record->getModel()->primaryKey])) {
                  $reference_record[$this->definition['foreignKey']] = $associated_record[$active_record->getModel()->primaryKey];
               } else {
                  $active_record->addForeignKeyToBeSet($this, $active_record);
               }
            }
            $this->reference_active_record->setChanged();
            break;
         case 'hasOne':
         case 'hasMany':
            if ($active_record != null) {
               $associated_record = &$active_record->getRecord();
               $reference_record = $this->reference_active_record->getRecord();
               if (isset($reference_record[$this->reference_active_record->getModel()->primaryKey])) {
                  $associated_record[$this->definition['foreignKey']] = $reference_record[$this->reference_active_record->getModel()->primaryKey];
               } else {
                  $this->reference_active_record->addForeignKeyToBeSet($this, $active_record);
               }
               $active_record->setChanged();
            }
            break;
         case 'hasAndBelongsToMany':
            $this->reference_active_record->setChanged();
            break;
      }
   }

   public function addAssociatedRecord(ActiveRecord $active_record) {
      $this->setForeignKey($active_record);
      if ($this->type == 'belongsTo' || $this->type == 'hasOne') {
         $this->associated_active_records = array($active_record);
      } else {
         $this->associated_active_records[] = $active_record;
      }
      $this->changed = true;
   }

   public function removeAssociatedRecord(ActiveRecord $active_record) {
      $checked = false;
      $record_to_be_removed = &$active_record->getRecord();
      foreach ($this->associated_active_records as $key => $associated_active_record) {
         $associated_record = &$associated_active_record->getRecord();
         if ($associated_record === $record_to_be_removed) {
            $checked = true;
            break;
         }
      }
      
      if ($checked) {
         switch ($this->type) {
            case 'belongsTo':
               $reference_record = &$this->reference_active_record->getRecord();
               $reference_record[$this->definition['foreignKey']] = null;
               unset($this->associated_active_records[$key]);
               $this->reference_active_record->setChanged();
               break;
            case 'hasOne':
            case 'hasMany':
               if (!empty($this->definition['deleteWhenNotAssociated'])) {
                  $associated_active_record->delete();
               }
               $associated_record = &$active_record->getRecord();
               $associated_record[$this->definition['foreignKey']] = null;
               unset($this->associated_active_records[$key]);
               $active_record->setChanged();
               break;
            case 'hasAndBelongsToMany':
               unset($this->associated_active_records[$key]);
               $this->reference_active_record->setChanged();
               break;
         }
         $this->changed = true;
      }
   }

   public function replaceAssociatedRecord(ActiveRecord $old_active_record, ActiveRecord $new_active_record) {
      $this->removeAssociatedRecord($old_active_record);
      $this->addAssociatedRecord($new_active_record);
   }

   public function getActiveRecords() {
      if (!$this->initialized) {
         $this->_initialize();
      }

      if ($this->type == 'belongsTo' || $this->type == 'hasOne') {
         if (count($this->associated_active_records) == 0) {
            return null;
         } else {
            return $this->associated_active_records[0]; // Give the Active Record
         }
      } else {
         return $this->association; // Give the public part of the association
      }
   }

   public function refresh($records) {
      if ($this->initialized) {
         if ($this->type == 'hasOne' || $this->type == 'belongsTo') {
            if (count($this->associated_active_records) == 1) {
               $this->associated_active_records[0]->refresh($records);
            } else {
               $active_record = ActiveRecord::getActiveRecord($this->model, $records);
               $this->associated_active_records = array($active_record);
            }
         } else {
            $old_records = array();
            foreach ($this->associated_active_records as $associated_active_record) {
               $old_records[$associated_active_record->{$this->model->primaryKey}] = $associated_active_record;
            }
            $result = array();
            foreach ($records as $record) {
               if (array_key_exists($record[$this->model->primaryKey], $old_records)) {
                  $result[] = $associated_active_record->refresh($record);
               } else {
                  $result[] = ActiveRecord::getActiveRecord($this->model, $record);
               }
            }
            $this->associated_active_records = $result;
         }
      } else {
         $this->_initializeWithRecord($records);
      }
      $this->changed = false;

   }

   private function _initializeWithRecord($records, $check_records = false) {
      $associated_records = array();
      switch($this->type) {
         case 'hasOne':
         case 'belongsTo' : {
            if ($records instanceof ActiveRecord) {
               $active_record = $records;
            } else {
               $active_record = ActiveRecord::getActiveRecord($this->model, $records);
            }
            $associated_records = array($active_record);
            break;
         }
         case 'hasMany':
         case 'hasAndBelongsToMany': {
            $associated_records = array();
            foreach($records as $related_record) {
               if ($related_record instanceof ActiveRecord) {
                  $active_record = $related_record;
               } else {
                  $active_record = ActiveRecord::getActiveRecord($this->model, $related_record);
               }
               $associated_records[] = $active_record;
            }
            break;
         }
      }

      if ($check_records) {
         $this->_setAssociatedRecordsWithForeignKeys($associated_records);
      } else {
         $this->associated_active_records = $associated_records;
      }
      $this->changed = false;
      $this->initialized = true;
   }

   private function _initialize() {
      $reference_record = $this->reference_active_record->getRecord();
      $reference_model = $this->reference_active_record->getModel();
      $related_active_records = array();

      switch($this->type) {
         case 'belongsTo': {
            $related_active_record = null;
            if (isset($reference_record[$this->definition['foreignKey']]) && $reference_record[$this->definition['foreignKey']] != null) {
               // The record has a foreign key, but has not the associated Active Record.
               // First try to find the Active Record in the pool, if not query it.
               $related_active_record = ActiveRecord::findActiveRecordInPool($this->model, $reference_record[$this->definition['foreignKey']]);
               if ($related_active_record === false) {
                  $related_record = $this->model->find('first', array(
                                          'conditions' => array($this->model->primaryKey => $reference_record[$this->definition['foreignKey']]),
                                          'recursive' => -1,
                                          'activeRecord' => false));
                  if ($related_record) {
                     $related_active_record = ActiveRecord::getActiveRecord($this->model, $related_record);
                  } else {
                     $related_active_record = null;
                  }
               }
            }
            $related_active_records = array($related_active_record);
            break;
         }
         case 'hasOne': {
            $related_active_record = false;
            // If the association has no condition, try first to find it in the pool
            if (empty($this->definition['conditions'])) {
               $related_active_record = ActiveRecord::findActiveRecordInPoolWithSecondaryKey($this->model, $this->definition['foreignKey'], $reference_record[$reference_model->primaryKey]);
            }
            if ($related_active_record === false) {
               if (!$reference_model->Behaviors->attached('Containable')) {
                  $reference_model->Behaviors->load('Containable');
               }
               $result = $reference_model->find('first',
                       array(
                          'conditions' => array($reference_model->alias . '.' . $reference_model->primaryKey => $reference_record[$reference_model->primaryKey]),
                          'contain' => array($this->name),
                          'activeRecord' => false));
               if (!empty($result[$this->name][$this->model->primaryKey])) {
                  $related_active_record = ActiveRecord::getActiveRecord($this->model, $result[$this->name]);
               } else {
                  $related_active_record = null;
               }
            }
            $related_active_records = array($related_active_record);
            break;
         }
         case 'hasMany':
         case 'hasAndBelongsToMany': {
            if (!$reference_model->Behaviors->attached('Containable')) {
               $reference_model->Behaviors->load('Containable');
            }
            // We can never be sure that all records are stored in the pool. So we must query them.
            $result = $reference_model->find('first',
                    array(
                       'conditions' => array($reference_model->alias . '.' . $reference_model->primaryKey => $reference_record[$reference_model->primaryKey]),
                       'contain' => array($this->name),
                       'activeRecord' => false));
            foreach ($result[$this->name] as $related_record) {
               $related_active_records[] = ActiveRecord::getActiveRecord($this->model, $related_record);
            }
         }
      }

      $this->associated_active_records = $related_active_records;
      $this->changed = false;
      $this->initialized = true;
   }
}

class ActiveRecordAssociation implements IteratorAggregate, Countable, ArrayAccess {
   private $_association;  // private part of the association

   public function __construct(_ActiveRecordAssociation $association) {
      $this->_association = $association;
   }

   public function getIterator() {
      $result = new ArrayObject($this->_association->associated_active_records);
      return $result->getIterator();
   }

   public function count() {
      return count($this->_association->associated_active_records);
   }

   public function offsetSet($offset, $value) {
      if (is_null($offset)) {
         $this->add($value);
      } else {
         if (isset($this->_association->associated_active_records[$offset])) {
            $this->replace($this->_association->associated_active_records[$offset], $value);
         } else {
            $this->add($value);
         }
      }
   }

   public function offsetExists($offset) {
      return isset($this->_association->associated_active_records[$offset]);
   }

   public function offsetUnset($offset) {
      if (isset($this->_association->associated_active_records[$offset])) {
         $this->remove($this->_association->associated_active_records[$offset]);
      }
   }

   public function offsetGet($offset) {
      return isset($this->_association->associated_active_records[$offset]) ? $this->_association->associated_active_records[$offset] : null;
   }

   public function add(ActiveRecord $active_record = null) {
      if ($active_record == null) {
         return;
      }

      $this->_association->addAssociatedRecord($active_record);
   }

   public function remove(ActiveRecord $active_record = null) {
      $this->_association->removeAssociatedRecord($active_record);
   }

   public function replace($old_record, $new_record) {
      $this->_association->replaceAssociatedRecord($old_record, $new_record);
   }
}

class ActiveRecord {
   private $_model;
   private $_record = array();
   private $_original_record = array();
   private $_associations = array();  // Associated array: association name => _ActiveRecordAssociation object
   private $_changed = false;
   private $_created = false;
   private $_deleted = false;
   private $_foreign_keys_not_yet_set = array();
   private $_internal_id;
   private $_direct_delete = false;

   private static $active_records_pool = array();
   private static $active_records_to_be_created = array();
   private static $active_record_counter = 0;

   public static function clearPool() {
      self::$active_records_pool = array();
   }

   public static function findActiveRecordInPool(Model $model, $id) {
      if (isset(self::$active_records_pool[$model->name]['records'][$id])) {
         return self::$active_records_pool[$model->name]['records'][$id];
      } else {
         return false;
      }
   }

   public static function findActiveRecordInPoolWithSecondaryKey(Model $model, $key, $value) {
      if (isset(self::$active_records_pool[$model->name])) {
         foreach(self::$active_records_pool[$model->name]['records'] as $record) {
            if ($record->{$key} == $value) {
               return $record;
            }
         }
      }
      return false;
   }

   public static function getActiveRecordProperties(Model $model, &$record) {
      if (method_exists($model, 'getActiveRecordProperties')) {
         $result = $model->getActiveRecordProperties($record);
      } else {
         $active_record_name = ActiveRecordBehavior::$prefix . $model->name;
         App::import('Model' . ActiveRecordBehavior::$subfolder, $active_record_name);
         if (!class_exists($active_record_name)) {
            $active_record_name = 'ActiveRecord';
         }
         $result = array('active_record_name' => $active_record_name, 'record' => $record);
      }
      return $result;
   }

   public static function getActiveRecord(Model $model, array $record) {
      if (count($record) == 0) {
         return null;
      } else if (isset($record[$model->alias][$model->primaryKey])) {
         $id = $record[$model->alias][$model->primaryKey];
      } else if (isset($record[$model->primaryKey])) {
         $id = $record[$model->primaryKey];
      } else {
         throw new ActiveRecordException('No primary key defined in record for model ' . $model->name);
      }

      $result = self::findActiveRecordInPool($model, $id);
      if ($result === false) {
         $active_record_class_properties = self::getActiveRecordProperties($model, $record);
         if (isset($active_record_class_properties['model'])) {
            $model = $active_record_class_properties['model'];
         }
         $options = array('model' => $model, 'create' => false);
         $result = new $active_record_class_properties['active_record_name']($active_record_class_properties['record'], $options);
         if (!isset(self::$active_records_pool[$model->name])) {
            self::$active_records_pool[$model->name] = array('records' => array(), 'model' => $model, 'data_source_name' => $model->useDbConfig);
         }
         self::$active_records_pool[$model->name]['records'][$id] = $result;
      } else {
         $result->refresh($record, $model->alias);
      }
      return $result;
   }

   public static function saveAll() {
      $all_active_records_per_data_source = array();
      foreach (self::$active_records_to_be_created as $active_record) {
         $data_source_name = $active_record->_model->useDbConfig;
          if (!isset($all_active_records_per_data_source[$data_source_name])) {
             $all_active_records_per_data_source[$data_source_name] = array('data_source' => $active_record->_model->getDataSource(), 'records' => array());
          }
          $all_active_records_per_data_source[$data_source_name]['records'][] = $active_record;
      }
      foreach (self::$active_records_pool as $active_records) {
         if (!isset($all_active_records_per_data_source[$active_records['data_source_name']])) {
            $all_active_records_per_data_source[$active_records['data_source_name']] = array('data_source' => $active_records['model']->getDataSource(), 'records' => array());
         }

         foreach ($active_records['records'] as $active_record) {
            if (!array_key_exists($active_record->_internal_id, self::$active_records_to_be_created)) {
               $all_active_records_per_data_source[$active_records['data_source_name']]['records'][] = $active_record;
            }
         }
      }
      self::$active_records_to_be_created = array();

      foreach ($all_active_records_per_data_source as $active_records) {
         $active_records['data_source']->begin();
         foreach ($active_records['records'] as $active_record) {
            $result = $active_record->save();
            if (!$result) {
               $active_records['data_source']->rollback();
               return false;
            }
         }
         $active_records['data_source']->commit();
      }
      return true;
   }

   public static function undoAll() {
      foreach (self::$active_records_pool as $active_records) {
         foreach ($active_records['records'] as $active_record) {
            $active_record->undo();
         }
      }
      self::$active_records_to_be_created = array();
   }

   public function __construct(array $record, array $options = null) {
      $this->_internal_id = self::$active_record_counter++;
      if (isset($options['model'])) {
         $this->_model = $options['model'];
      } else {
         if (property_exists($this, 'model_name')){
            $model_name = $this->model_name;
         } else {
            $class_name = get_class($this);
            if (substr($class_name, 0, strlen(ActiveRecordBehavior::$prefix)) == ActiveRecordBehavior::$prefix) {
               $model_name = substr($class_name, strlen(ActiveRecordBehavior::$prefix));
            } else {
               $model_name = $class_name;
            }
         }
         App::import('Model', $model_name);
         $this->_model = ClassRegistry::init($model_name);
      }
      if (isset($record[$this->_model->alias])) {
         $this->_record = $record[$this->_model->alias];
      } else {
         $this->_record = $record;
      }
      $this->_direct_delete = ActiveRecordBehavior::$directDelete;
      if (isset($options['directDelete'])) {
         $this->_direct_delete = $options['directDelete'];
      }
      $create = true;
      if (isset($options['create'])) {
         $create = $options['create'];
      }
      if ($create) {
         self::$active_records_to_be_created[$this->_internal_id] = $this;
         $this->_created = true;
         $this->_changed = true;
      }

      foreach ($this->_model->associations() as $association_type) {
         foreach ($this->_model->{$association_type} as $association_name => $association_definition) {
            $association = new _ActiveRecordAssociation($association_name, $this, $association_type, $association_definition, $record, $create);
            $this->_associations[$association_name] = $association;
            unset($this->_record[$association_name]);
         }
      }

      foreach ($this->_record as $key => $value) {
         $this->_original_record[$key] = $value;
      }

   }

   private function _resetState() {
      $this->_changed = $this->_created = $this->_deleted = false;
   }

   public function refresh($record = null, $alias = null) {
      if ($record) {
         if (!$alias) {
            $alias = $this->_model->alias;
         }
         if (isset($record[$alias])) {
            $this->_record = $record[$alias];
         } else {
            $this->_record = $record;
         }

         foreach($this->_associations as $association_name => $association) {
            if (isset($record[$association_name])) {
               $association->refresh($record[$association_name]);
            } else {
               $association->initialized = false;
            }
         }
         $this->_resetState();
      } else if (!empty($this->_record[$this->_model->primaryKey])) {
         $record = $this->_model->find('first', array(
            'recursive' => -1,
            'conditions' => array($this->_model->primaryKey => $this->_record[$this->_model->primaryKey])));
         $this->_record = $record[$this->_model->alias];
         foreach($this->_associations as $association) {
            $association->initialized = false;
         }
         $this->_resetState();
      }
      return $this;
   }

   public function getModel() {
      return $this->_model;
   }

   public function &getRecord() {
      return $this->_record;
   }

   public function setChanged($changed = true) {
      $this->_changed = $changed;
   }

   public function isCreated() {
      return $this->_created;
   }

   public function isDeleted() {
      return $this->_deleted;
   }

   public function isChanged() {
      return $this->_changed;
   }

   public function __get($name)
   {
      if (array_key_exists($name, $this->_associations)) {
         return $this->_associations[$name]->getActiveRecords();
      } else if (array_key_exists($name, $this->_record)) {
         return $this->_record[$name];
      }

      throw new ActiveRecordException('Undefined property via __get(): ' . $name);
   }

   public function __set($name, $value)
   {
      if (array_key_exists($name, $this->_associations)) {
         $this->_associations[$name]->setAssociatedRecords($value);
      } else if (array_key_exists($name, $this->_record)) {
         $this->_record[$name] = $value;
         $this->_changed = true;
      } else {
         throw new ActiveRecordException('Undefined property via __set(): ' . $name);
      }
   }

   public function __isset($name) {
      return array_key_exists($name, $this->_associations) || array_key_exists($name, $this->_record);
   }

   public function delete() {
      $this->_deleted = true;
      $this->_changed = true;
   }

   public function undo() {
      foreach ($this->_original_record as $key => $value) {
         $this->_record[$key] = $value;
      }
      foreach ($this->_associations as $association) {
         $association->initialized = false;
      }

      if ($this->_created) {
         unset(self::$active_records_to_be_created[$this->_internal_id]);
      }

      $this->_changed = $this->_deleted = $this->_created = false;
   }

   private function _create() {
      $this->_model->create();
      $result = $this->_model->save($this->_record);
      if ($result) {
         $this->_record = $result[$this->_model->alias];
         unset(self::$active_records_to_be_created[$this->_internal_id]);
         $this->_resetState();
         foreach($this->_foreign_keys_not_yet_set as $foreign_key_to_be_set) {
            $foreign_key_to_be_set['association']->setForeignKey($foreign_key_to_be_set['active_record']);
         }
         return true;
      } else {
         return false;
      }
   }

   private function _delete() {
      if (!$this->_created) {
         unset(self::$active_records_pool[$this->_model->name][$this->_model->primaryKey]);
         $model = $this->_model;
         if ($this->_direct_delete) {
            // This avoid 2 select statements
            $result = $model->getDataSource()->delete($model, array($model->alias . '.' . $model->primaryKey => $this->_record[$model->primaryKey]));
         } else {
            $result = $model->delete($this->_record[$model->primaryKey]);
         }
      } else {
         unset(self::$active_records_to_be_created[$this->_internal_id]);
         $result = true;
      }
      $this->_resetState();
      return $result;
   }

   public function addForeignKeyToBeSet(_ActiveRecordAssociation $association, ActiveRecord $active_record) {
      $this->_foreign_keys_not_yet_set[] = array('association' => $association, 'active_record' => $active_record);
      if ($active_record->isCreated() && $this->isCreated()) {
         //$this must be created before $active_record
         $internal_id1 = $this->_internal_id;
         $internal_id2 = $active_record->_internal_id;
         if ($internal_id2 < $internal_id1) {
            // TODO: This is not a 100% waterproof solution...
            $this->_internal_id = $internal_id2;
            $active_record->_internal_id = $internal_id1;
            self::$active_records_to_be_created[$internal_id1] = $active_record;
            self::$active_records_to_be_created[$internal_id2] = $this;
         }
      }
   }

   public function commit() {
      $this->_model->getDataSource()->commit();
   }

   public function rollback() {
      $this->_model->getDataSource()->rollback();
   }

   public function begin() {
      $this->_model->getDataSource()->begin();
   }

   public function save() {
      if (method_exists($this, 'beforeSave')) {
         $this->beforeSave();
      }
      if (!$this->_changed) {
         return true;
      }

      if ($this->_deleted) {
         return $this->_delete();
      }

      if ($this->_created) {
         $this->_create(); // This reset the _changed property
      }

      $record = array($this->_model->alias => $this->_record);
      foreach ($this->_associations as $association) {
         if ($association->changed && $association->type == 'hasAndBelongsToMany') {
            $this->_changed = true;
            $associated_active_records = $association->getActiveRecords();
            if (count($associated_active_records) == 0) {
               // All associated records must be delete in the join table
               // Maybe not the most beautiful way to do it...
               if (!empty($association->definition['joinTable']) && !empty($association->definition['foreignKey'])) {
                  $this->_model->getDataSource()->execute(
                          'DELETE FROM ' . $association->definition['joinTable'] .
                          ' WHERE ' . $association->definition['foreignKey'] . ' = ' . $this->_record[$this->_model->primaryKey]);
               } else {
                  // This should work according to CakePHP doc, but not with my version.
                  $records[$association->name] = array();
               }
            } else {
               $records = array();
               foreach ($associated_active_records as $associated_active_record) {
                  $associated_record = $associated_active_record->getRecord();
                  $records[] = $associated_record[$association->model->primaryKey];
               }
               $record[$association->name] = $records;
            }
            $association->changed = false;
         }
      }

      if (!$this->_changed) {
         return true;
      }

      if ($result = $this->_model->save($record)) {
         $this->_record = $result[$this->_model->alias];
         $this->_resetState();
         return true;
      } else {
         CakeLog::write('ActiverRecord', 'save did nod succeed for record ' . print_r($record, true) . ' with model ' . $this->_model->alias . 
                 '. Error: ' . print_r($this->_model->validationErrors, true));
         return false;
      }
   }
}


class ActiveRecordBehavior extends ModelBehavior {
   public static $prefix = 'AR';
   public static $subfolder = '\\ActiveRecord';
   public static $directDelete = false;

	public $runtime = array();

   public function setup(Model $model, $settings) {
      if (isset($settings['directDelete'])) {
         self::$directDelete = $settings['directDelete'];
         unset($settings['directDelete']);
      }
      if (isset($settings['prefix'])) {
         self::$prefix = $settings['prefix'];
         unset($settings['prefix']);
      }
      if (isset($settings['subfolder'])) {
         self::$subfolder = $settings['subfolder'];
         unset($settings['subfolder']);
      }
      if (!isset($this->settings[$model->name])) {
         $this->settings[$model->name] = array(
            'allFind' => true,
        );
      }
      $this->settings[$model->name] = array_merge(
            $this->settings[$model->name],
            (array)$settings);
   }

   public function beforeFind(Model $model, $query) {
      $this->runtime[$model->name]['activeRecord'] = false;

      if ((isset($query['activeRecord']) && $query['activeRecord'] == true) ||
          (!isset($query['activeRecord']) && $this->settings[$model->name]['allFind'])) {
         if ($model->findQueryType == 'first' || $model->findQueryType == 'all') {
            $this->runtime[$model->name]['activeRecord'] = true;
         }
      }
   }

   public function afterFind(Model $model, array $results, $primary) {
      $records = $results;
      if ($this->runtime[$model->name]['activeRecord']) {
         if ($model->findQueryType == 'first') {
            // The afterFind callback is called before that the find method refines the result to 1 row.
            if (count($results) > 0) {
               $records = array(ActiveRecord::getActiveRecord($model, $results[0]));
            } else {
               $records = array();
            }
            
         } else if ($model->findQueryType == 'all') {
            $records = array();
            foreach($results as $result) {
               $records[] = ActiveRecord::getActiveRecord($model, $result);
            }
         }
      }
      return $records;
   }
}

?>
