<?php
namespace core;
class Config {
    private static $_config = array();
    public static function get($name = null)
    {
        if (empty(self::$_config)) {
            self::autoload();
        }
        if (!$name) {
            return self::$_config;
        }
        if (isset(self::$_config[$name])) {
            return self::$_config[$name];
        }
        return null;
    }

    public static function load($file)
    {
        $configFile = CONFIG_PATH.DS.$file.'.php';
        $sysConfigFile = SYS_CONF_PATH.DS.$file.'.php';
        if (App::inFileMap($configFile) || App::inFileMap($sysConfigFile)) {
            return true;
        }
        $config = array();
        if (is_file($configFile)) {
            $config = require_once $configFile;
            App::addFileMap($configFile);
        }
        elseif (is_file($sysConfigFile)) {
            $config = require_once $sysConfigFile;
            App::addFileMap($sysConfigFile);
        }
        if (!empty($config)) {
            foreach($config as $k=>$v) {
                self::$_config[$k] = $v;
            }
            return true;
        }
        return false;
    }

    public static function autoload()
    {
        self::load('defaults');
        self::load('config');
        $autoloadFiles = self::get('autoload');
        if (isset($autoloadFiles['config']) && !empty($autoloadFiles['config'])) {
            foreach($autoloadFiles as $file) {
                self::load($file);
            }
        }
    }
}