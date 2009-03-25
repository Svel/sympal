<?php

/**
 * BasesfGuardGroup
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property Doctrine_Collection $users
 * @property Doctrine_Collection $permissions
 * @property Doctrine_Collection $sfGuardGroupPermission
 * @property Doctrine_Collection $sfGuardUserGroup
 * @property Doctrine_Collection $MenuItems
 * @property Doctrine_Collection $MenuItemGroups
 * @property Doctrine_Collection $Content
 * @property Doctrine_Collection $ContentGroups
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 5441 2009-01-30 22:58:43Z jwage $
 */
abstract class BasesfGuardGroup extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('sf_guard_group');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => '4'));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'unique' => true, 'length' => '255'));
        $this->hasColumn('description', 'string', 1000, array('type' => 'string', 'length' => '1000'));
    }

    public function setUp()
    {
        $this->hasMany('sfGuardUser as users', array('refClass' => 'sfGuardUserGroup',
                                                     'local' => 'group_id',
                                                     'foreign' => 'user_id'));

        $this->hasMany('sfGuardPermission as permissions', array('refClass' => 'sfGuardGroupPermission',
                                                                 'local' => 'group_id',
                                                                 'foreign' => 'permission_id'));

        $this->hasMany('sfGuardGroupPermission', array('local' => 'id',
                                                       'foreign' => 'group_id'));

        $this->hasMany('sfGuardUserGroup', array('local' => 'id',
                                                 'foreign' => 'group_id'));

        $this->hasMany('MenuItem as MenuItems', array('refClass' => 'MenuItemGroup',
                                                      'local' => 'group_id',
                                                      'foreign' => 'menu_item_id'));

        $this->hasMany('MenuItemGroup as MenuItemGroups', array('local' => 'id',
                                                                'foreign' => 'group_id'));

        $this->hasMany('Content', array('refClass' => 'ContentGroup',
                                        'local' => 'group_id',
                                        'foreign' => 'content_id'));

        $this->hasMany('ContentGroup as ContentGroups', array('local' => 'id',
                                                              'foreign' => 'group_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}