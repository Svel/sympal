<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContentList extends BasesfSympalContentList
{
  public function buildDataGrid(sfWebRequest $request)
  {
    if ($this->table_method)
    {
      $typeTable = Doctrine_Core::getTable($this->ContentType->name);
      $method = $this->table_method;
      $q = $typeTable->$method($this, $request);
      if ($q instanceof sfSympalDataGrid)
      {
        $dataGrid = $q;
      } else if ($q instanceof sfDoctrinePager || $q instanceof Doctrine_Query_Abstract) {
        $dataGrid = sfSympalDataGrid::create($q);
      } else {
        throw new sfException(sprintf('ContentList table_method must return an instance of sfSympalDataGrid, sfDoctrinePager or Doctrine_Query_Abstract. An instance of "%s" was returned.', get_class($q)));
      }
    } else {
      $pager = new sfDoctrinePager('sfSympalContent');
      $pager->setQuery($this->_buildQuery($request));

      $dataGrid = sfSympalDataGrid::create($pager)
        ->addColumn('c.title', 'renderer=sympal_data_grid/default_title')
        ->addColumn('c.date_published')
        ->addColumn('u.username', 'label=Created By');
    }

    if ($this->sort_column) 
    { 
      $dataGrid->setDefaultSort($this->sort_column, $this->sort_order);
    }

    $dataGrid->setMaxPerPage(($this->rows_per_page > 0 ? $this->rows_per_page : sfSympalConfig::get('rows_per_page', null, 10)));

    $dataGridRequestInfo = $request->getParameter($dataGrid->getId());
    $dataGrid->init();

    return $dataGrid;
  }

  private function _buildQuery(sfWebRequest $request)
  {
    $table = Doctrine_Core::getTable('sfSympalContent');

    if ($this->dql_query) {
      $q = $table->createQuery()->query($this->dql_query);
    } else {
      $q = $table->getFullTypeQuery($this->ContentType->name);
    }

    return $q;
  }
}