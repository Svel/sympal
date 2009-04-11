<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginVersion extends BaseVersion
{
  protected $_record;

  public function getRevertArray()
  {
    $changes = array();
    foreach ($this->Changes as $change)
    {
      $changes[$change['field']] = $change['revert_value'];
    }
    return $changes;
  }

  public function getChangesArray()
  {
    $changes = array();
    foreach ($this->Changes as $change)
    {
      $changes[$change['field']] = array(
        'revert_value' => $change['revert_value'],
        'old_value' => $change['old_value'],
        'new_value' => $change['new_value']
      );
    }
    return $changes;
  }

  public function setChangesArray($changesArray)
  {
    foreach ($changesArray as $field => $value)
    {
      $versionChange = new VersionChange();
      $versionChange->Version = $this;
      $versionChange->field = $field;
      $versionChange->old_value = (string) $value['old_value'];
      $versionChange->new_value = (string) $value['new_value'];

      $this->Changes[] = $versionChange;
    }

    $this->num_changes = count($changesArray);
  }

  public function getRecord()
  {
    if (!$this->_record)
    {
      $parent = str_replace('Translation', '', $this['record_type']);
      if(class_exists($parent))
      {
        Doctrine::initializeModels($parent);
      }

      $this->_record = Doctrine::getTable($this['record_type'])
        ->createQuery()
        ->where('id = ?', $this['record_id'])
        ->fetchOne();
    }

    return $this->_record;
  }

  public function revert()
  {
    return $this->getRecord()->revert($this['version']);
  }

  public function __toString()
  {
    return '#'.$this['version'].' - '.date('m/d/Y h:i:s', strtotime($this['created_at']));
  }
}