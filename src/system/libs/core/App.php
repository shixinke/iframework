<?php
namespace core;
class App {
    private static $_classMaps = array();
    private static $_fileMaps = array();
    private static $router;
    public static function run()
    {
        spl_autoload_register('core\\App::autoload');
        self::$router = \core\Router::getInstance();
        self::$router->route();
    }

    public static function autoload($className)
    {
        if (isset(self::$_classMaps[$className])) {
            return true;
        }
        $classFile = str_replace('\\', '/', $className);
        if(strpos($className, 'core') === false) {
            //加载应用类库
            $classFile = APP_PATH.DS.trim($classFile, '/').'.php';
        } else {
            //加载核心类库
            $classFile = SYS_LIB_PATH.DS.trim($classFile, '/').'.php';
        }
        if (is_file($classFile)) {
            self::$_classMaps[$className] = true;
            self::$_fileMaps[$classFile] = true;
            require_once $classFile;
            if (!class_exists($className)) {
                error_page(404, $className.'类不存在');
            }
        } else {
            error_page(404, $classFile.'文件不存在');
        }

    }

    public static function addFileMap($file)
    {
        self::$_fileMaps[$file] = true;
    }

    public static function inFileMap($file)
    {
        if (isset(self::$_fileMaps[$file])) {
            return true;
        }
        return false;
    }

    public static function inClassMap($className)
    {
        if (isset(self::$_classMaps[$className])) {
            return true;
        }
        return false;
    }

    public static function getFileMaps()
    {
        return array_keys(self::$_fileMaps);
    }

    public static function getClassMaps()
    {
        return array_keys(self::$_classMaps);
    }

    public static function getRouter()
    {
        return self::$router;
    }
}