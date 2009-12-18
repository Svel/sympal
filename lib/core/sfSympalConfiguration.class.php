<?php

class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_bootstrap,
    $_plugins = array(),
    $_modules = array(),
    $_layouts = array();

  public function __construct(sfEventDispatcher $dispatcher, ProjectConfiguration $projectConfiguration)
  {
    $this->_dispatcher = $dispatcher;
    $this->_projectConfiguration = $projectConfiguration;

    $this->_initializeSymfonyConfig();
    $this->_loadGuardRouting();

    sfOutputEscaper::markClassesAsSafe(array(
      'Content',
      'ContentTranslation',
      'ContentSlot',
      'ContentSlotTranslation',
      'MenuItem',
      'MenuItemTranslation',
      'sfSympalContentRenderer',
      'sfSympalMenu',
      'sfParameterHolder',
      'sfSympalDataGrid',
      'sfSympalUpgradeFromWeb'
    ));

    $this->_dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
    $this->_dispatcher->connect('component.method_not_found', array(new sfSympalActions(), 'extend'));
    $this->_dispatcher->connect('controller.change_action', array($this, 'initializeTheme'));
  }

  /**
   * Callable attached to Symfony event context.load_factories. When this event
   * is triggered we also create the Sympal context.
   */
  public function bootstrap()
  {
    $record = Doctrine_Core::getTable('sfGuardUser')->getRecordInstance();
    $this->_dispatcher->notify(new sfEvent($record, 'sympal.user.set_table_definition', array('object' => $record)));

    $this->_cache = new sfSympalCache($this);

    $this->_sympalContext = sfSympalContext::createInstance(
      sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')),
      sfContext::getInstance()
    );

    $this->_enableModules();

    $this->_redirectIfNotInstalled();

    $this->initializeTheme();

    if (sfConfig::get('sf_debug'))
    {
      $this->_checkPluginDependencies();
    }

    $this->_projectConfiguration->loadHelpers(array('Sympal', 'I18N'));
  }

  public function getCache()
  {
    return $this->_cache;
  }

  public function getSympalContext()
  {
    return $this->_context;
  }

  public function getContentTypes()
  {
    return $this->_cache->getContentTypes();
  }

  public function getProjectConfiguration()
  {
    return $this->_projectConfiguration;
  }

  public function getRequiredPlugins()
  {
    $requiredPlugins = array();
    foreach ($this->_projectConfiguration->getPlugins() as $pluginName)
    {
      if (strpos($pluginName, 'sfSympal') !== false)
      {
        $dependencies = sfSympalPluginToolkit::getPluginDependencies($pluginName);
        $requiredPlugins = array_merge($requiredPlugins, $dependencies);
      }
    }

    return array_values(array_unique($requiredPlugins));
  }

  public function getCorePlugins()
  {
    return sfSympalPluginConfiguration::$dependencies;
  }

  public function getInstalledPlugins()
  {
    return $this->getOtherPlugins();
  }

  public function getAddonPlugins()
  {
    return sfSympalPluginToolkit::getAvailablePlugins();
  }

  public function getOtherPlugins()
  {
    return array_diff($this->getPlugins(), $this->getRequiredPlugins());
  }

  public function getAllManageablePlugins()
  {
    $plugins = array_merge($this->getAddonPlugins(), $this->getInstalledPlugins());
    $plugins = array_unique($plugins);

    return $plugins;
  }

  public function getPlugins()
  {
    return array_keys($this->getPluginPaths());
  }

  public function getPluginPaths()
  {
    if (!$this->_plugins)
    {
      $configuration = ProjectConfiguration::getActive();
      $pluginPaths = $configuration->getAllPluginPaths();
      $this->_plugins = array();
      foreach ($pluginPaths as $pluginName => $path)
      {
        if (strpos($pluginName, 'sfSympal') !== false)
        {
          $this->_plugins[$pluginName] = $path;
        }
      }
    }

    return $this->_plugins;
  }

  public function getModules()
  {
    return $this->getCache()->getModules();
  }

  public function getLayouts()
  {
    return $this->getCache()->getLayouts();
  }

  public function initializeTheme()
  {
    $request = sfContext::getInstance()->getRequest();

    if (!$request->isXmlHttpRequest() && $request->getParameter('module') != 'sympal_content_renderer')
    {
      $layout = sfSympalConfig::get($request->getParameter('module'), 'layout');
      if (!$layout)
      {
        $layout = sfSympalConfig::get('default_layout');
      }
      sfSympalTheme::change($layout);
    }
  }

  private function _enableModules()
  {
    if (sfSympalConfig::get('enable_all_modules', null, true))
    {
      $modules = sfConfig::get('sf_enabled_modules', array());
      if (sfSympalConfig::get('enable_all_modules'))
      {
        $modules = array_merge($modules, $this->getModules());
      }
      $modules = array_merge($modules, sfSympalConfig::get('enabled_modules', null, array()));

      sfConfig::set('sf_enabled_modules', $modules);
    }
  }

  private function _redirectIfNotInstalled()
  {
    $sfContext = sfContext::getInstance();
    $request = $sfContext->getRequest();

    // Redirect to install module if...
    //  not in test environment
    //  sympal has not been installed
    //  module is not already sympal_install
    if (sfConfig::get('sf_environment') != 'test' && !sfSympalConfig::get('installed') && $request->getParameter('module') != 'sympal_install')
    {
      $sfContext->getController()->redirect('@sympal_install');
    }
  }

  private function _checkPluginDependencies()
  {
    foreach ($this->_projectConfiguration->getPlugins() as $pluginName)
    {
      if (strpos($pluginName, 'sfSympal') !== false)
      {
        $dependencies = sfSympalPluginToolkit::getPluginDependencies($pluginName);
        sfSympalPluginToolkit::checkPluginDependencies($pluginName, $dependencies);
      }
    }
  }

  private function _initializeSymfonyConfig()
  {
    sfConfig::set('sf_admin_module_web_dir', sfSympalConfig::get('admin_module_web_dir', null, '/sfSympalPlugin'));
    sfConfig::set('app_sf_guard_plugin_success_signin_url', '@homepage');

    if (sfConfig::get('sf_login_module') == 'default')
    {
      sfConfig::set('sf_login_module', 'sympal_auth');
      sfConfig::set('sf_login_action', 'signin');
    }

    if (sfConfig::get('sf_secure_module') == 'default')
    {
      sfConfig::set('sf_secure_module', 'sympal_auth');
      sfConfig::set('sf_secure_action', 'secure');
    }

    if (sfConfig::get('sf_error_404_module') == 'default')
    {
      sfConfig::set('sf_error_404_module', 'sympal_default');
      sfConfig::set('sf_error_404_action', 'error404');
    }

    if (sfConfig::get('sf_module_disabled_module') == 'default')
    {
      sfConfig::set('sf_module_disabled_module', 'sympal_default');
      sfConfig::set('sf_module_disabled_action', 'disabled');
    }
  }

  private function _loadGuardRouting()
  {
    if (sfSympalConfig::get('auto_enable_sf_guard', null, true))
    {
      $this->_dispatcher->connect('routing.load_configuration', array('sfGuardRouting', 'listenToRoutingLoadConfigurationEvent'));

      foreach (array('sfGuardUser', 'sfGuardGroup', 'sfGuardPermission', 'sfGuardRegister', 'sfGuardForgotPassword') as $module)
      {
        $this->_dispatcher->connect('routing.load_configuration', array('sfGuardRouting', 'addRouteFor'.str_replace('sfGuard', '', $module)));
      }
    }
  }
}