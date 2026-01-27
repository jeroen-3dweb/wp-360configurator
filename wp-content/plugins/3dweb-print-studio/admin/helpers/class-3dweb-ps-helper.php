<?php

class DWeb_PS_Admin_HELPER
{
  public static function isLocalhost() :bool
  {
      $whitelist = array('127.0.0.1', '::1');
      return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
  }
}