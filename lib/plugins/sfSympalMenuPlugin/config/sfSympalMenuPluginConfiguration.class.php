<?php

/**
 * Plugin configuration for the menu plugin
 * 
 * @package     sfSympalMenuPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMenuPluginConfiguration extends sfPluginConfiguration
{
  protected
    $_sympalContext;

  public function initialize()
  {
    self::_markClassesAsSafe();
    
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load', array($this, 'bootstrap'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $event->getSubject()
      ->getChild('site_administration')
      ->addChild('Menus', '@sympal_menu_items')->setCredentials(array('ManageMenus'));
  }

  /**
   * Listens to the sympal.load event
   */
  public function bootstrap(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    
    $event->getSubject()->getApplicationConfiguration()->loadHelpers('SympalMenu');
    
    // Listen to sfSympalContent's change_content event
    $this->dispatcher->connect('sympal.content.set_content', array(
      $event->getSubject()->getService('menu_manager'),
      'listenContentSetContent'
    ));
    
    // extend the component/action class
    $actions = new sfSympalMenuActions();
    $this->dispatcher->connect('component.method_not_found', array($actions, 'extend'));
  }

  /**
   * Mark necessary Sympal classes as safe
   * 
   * These classes won't be wrapped with the output escaper
   *
   * @return void
   */
  private static function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalMenuItem',
      'sfSympalMenuItemTranslation',
      'sfSympalMenu',
    ));
  }
}