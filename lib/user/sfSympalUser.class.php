<?php

class sfSympalUser extends sfGuardSecurityUser
{
  protected
    $_forwarded = false,
    $_flash     = false;

  public function checkContentSecurity($content)
  {
    $access = true;
    $allPermissions = $content->getAllPermissions();

    if ($this->isAuthenticated() && !$this->hasCredential($allPermissions))
    {
      $access = false;
    }

    if (!$this->isAuthenticated() && !empty($allPermissions))
    {
      $access = false;
    }

    if (!$access && !$this->_forwarded)
    {
      $this->_forwarded = true;
      return sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
    }
    return $access;
  }

  public function toggleEditMode()
  {
    $this->setAttribute('sympal_edit', !$this->getAttribute('sympal_edit', false));
    $mode = $this->getAttribute('sympal_edit', false) ? 'on':'off';

    if ($mode == 'off')
    {
      $user = $this->getGuardUser();
      Doctrine::getTable('Content')
        ->createQuery()
        ->update()
        ->set('locked_by', 'NULL')
        ->where('locked_by = ?', $user->id)
        ->execute();
    }
    return $mode;
  }

  public function getOpenContentLock()
  {
    $q = Doctrine_Query::create()
      ->from('Content e')
      ->leftJoin('e.Type t')
      ->andWhere('e.locked_by = ?', $this->getGuardUser()->getId());

    $lock = $q->fetchOne();
    if ($lock)
    {
      Doctrine::initializeModels(array($lock['Type']['name']));
      return $lock;
    } else {
      return false;
    }
  }

  public function addFlash($type, $msg)
  {
    $flash = parent::getFlash($type);
    $flash = $flash ? $flash:array();
    $flash[] = $msg;

    parent::setFlash($type, $flash);
  }

  public function setFlash($type, $msg)
  {
    $this->addFlash($type, $msg);
  }

  public function getFlash($type)
  {
    return end($this->getFlashArray($type));
  }

  public function getFlashArray($type)
  {
    $flash = parent::getFlash($type);
    $flash = array_unique($flash);

    $this->getAttributeHolder()->remove($type, null, 'symfony/user/sfUser/flash');

    return $flash;
  }
}