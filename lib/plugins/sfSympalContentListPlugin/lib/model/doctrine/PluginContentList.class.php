<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginContentList extends BaseContentList
{
  public function buildPager($page = 1)
  {
    $table = Doctrine_Core::getTable('Content');

    if ($this->table_method)
    {
      $method = $this->table_method;
      $q = $table->$method();
    } else if ($this->dql_query) {
      $q = $table->createQuery()->query($this->dql_query);
    } else {
      $q = $table->getTypeQuery($this->ContentType->name);
      if ($table->hasColumn($this->sort_column) && $this->sort_order) 
	    { 
	      $q->orderBy('c.'.$this->sort_column.' '.$this->sort_order); 
	    }
    }

    $pager = new sfDoctrinePager('Content', ($this->rows_per_page > 0 ? $this->rows_per_page : sfSympalConfig::get('rows_per_page', null, 10)));
    $pager->setQuery($q);
    $pager->setPage($page);
    $pager->init();

    return $pager;
  }
}