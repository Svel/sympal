<?php

abstract class sfSympalPluginManager
{
  protected
    $_name,
    $_pluginName,
    $_contentTypeName,
    $_configuration,
    $_dispatcher,
    $_formatter,
    $_filesystem;

  protected static
    $_lockFile = null;

  public function __construct($name, ProjectConfiguration $configuration = null, sfFormatter $formatter = null)
  {
    $this->_name = sfSympalPluginToolkit::getShortPluginName($name);
    $this->_pluginName = sfSympalPluginToolkit::getLongPluginName($this->_name);
    $this->_contentTypeName = $this->getContentTypeForPlugin($this->_pluginName);

    $this->_configuration = is_null($configuration) ? ProjectConfiguration::getActive():$configuration;
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_formatter = is_null($formatter) ? new sfFormatter():$formatter;
    $this->_filesystem = new sfFilesystem($this->_dispatcher, $this->_formatter);
  }

  public static function getActionInstance($name, $action, ProjectConfiguration $configuration = null, sfFormatter $formatter = null)
  {
    if (is_null($name))
    {
      throw new sfException('You must speciy the plugin name you want to get the action instance for.');
    }

    $name = sfSympalPluginToolkit::getShortPluginName($name);
    $pluginName = sfSympalPluginToolkit::getLongPluginName($name);

    $class = $pluginName.ucfirst($action);

    if (!class_exists($class))
    {
      $class = 'sfSympalPluginManager'.ucfirst($action);
    }
    return new $class($pluginName, $configuration, $formatter);
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    ProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->_formatter->formatSection($section, $message, $size, $style))));
  }

  protected function _setDoctrineProperties($obj, $properties)
  {
    foreach ($properties as $key => $value)
    {
      if ($value instanceof Doctrine_Record)
      {
        $obj->$key = $value;
        unset($properties[$key]);
      }
    }

    $obj->fromArray($properties, true);
  }

  public function newContentTemplate($name, $contentType, $properties = array())
  {
    if (is_string($contentType))
    {
      $contentType = Doctrine_Core::getTable('ContentType')->findOneByName($contentType);
    }

    $contentTemplate = new ContentTemplate();
    $contentTemplate->name = $name;
    $contentTemplate->ContentType = $contentType;
    $contentTemplate->Site = Doctrine_Core::getTable('Site')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')));

    $this->_setDoctrineProperties($contentTemplate, $properties);

    return $contentTemplate;
  }

  public function newContent($contentType, $properties = array())
  {
    if (is_string($contentType))
    {
      $contentType = Doctrine_Core::getTable('ContentType')->findOneByName($contentType);
    }

    $content = new Content();
    $content->Type = $contentType;
    $content->CreatedBy = Doctrine_Core::getTable('User')->findOneByIsSuperAdmin(1);
    $content->Site = Doctrine_Core::getTable('Site')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')));
    $content->is_published = true;

    $name = $contentType['name'];
    $content->$name = new $name();

    $content->trySettingTitleProperty('Sample '.$contentType['label']);

    $this->_setDoctrineProperties($content, $properties);

    return $content;
  }

  public function newContentType($name, $properties = array())
  {
    $contentType = new ContentType();
    $contentType->name = $name;
    $contentType->label = sfInflector::humanize(sfInflector::tableize($name));
    $contentType->Site = Doctrine_Core::getTable('Site')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')));

    $this->_setDoctrineProperties($contentType, $properties);

    return $contentType;
  }

  public function newMenuItem($name, $properties = array())
  {
    $menuItem = new MenuItem();
    $menuItem->name = $name;
    $menuItem->Site = Doctrine_Core::getTable('Site')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')));
    $menuItem->is_published = true;

    $this->_setDoctrineProperties($menuItem, $properties);

    return $menuItem;
  }

  public function saveMenuItem(MenuItem $menuItem)
  {
    $roots = Doctrine_Core::getTable('MenuItem')->getTree()->fetchRoots();
    $root = $roots[0];
    $menuItem->getNode()->insertAsLastChildOf($root);
  }

  public function getContentTypeForPlugin($name)
  {
    try {
      $pluginName = sfSympalPluginToolkit::getLongPluginName($name);
      $path = ProjectConfiguration::getActive()->getPluginConfiguration($pluginName)->getRootDir();
      $schema = $path.'/config/doctrine/schema.yml';

      if (file_exists($schema))
      {
        $array = (array) sfYaml::load($schema);
        foreach ($array as $modelName => $model)
        {
          if (isset($model['actAs']) && !empty($model['actAs']))
          {
            foreach ($model['actAs'] as $key => $value)
            {
              if (is_numeric($key))
              {
                $name = $value;
              } else {
                $name = $key;
              }
              if ($name == 'sfSympalContentType')
              {
                return $modelName;
              }
            }
          }
        }
      }
    } catch (Exception $e) {}

    return false;
  }

  public function rebuildFilesFromSchema()
  {
    $this->logSection('sympal', 'Re-build all classes and generate sql');

    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $task->run(array(), array('all-classes', 'sql'));
  }
}