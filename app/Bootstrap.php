<?php

/**
 * Class Bootstrap
 */
class Bootstrap
{
    static $foldersToLoad = array(
        'abstract',
        'controller'
    );

    static $routeMap = array(
        '/' => 'Index',
        '/list' => 'List',
        '/add' => 'Add',
        '/save' => 'Save'
    );

    public function __construct()
    {
        $this->autoLoad();
    }

    public function init()
    {
        $this->runAction();
    }

    private function runAction()
    {
        /**
         * the route will not be used, anyway I used htmlspecialchars to prevent XSS attacks on URL
         */
        $route = htmlspecialchars($_SERVER['REQUEST_URI']);
        if (isset(static::$routeMap[$route])) {
            $controller = new IndexController();
            $controller->{'action'. static::$routeMap[$route]}();
        }else {
            header("HTTP/1.0 404 Not Found");
        }
    }

    /**
     * Auto load the files included on paths
     * listed on $foldersToLoad
     */
    private function autoLoad()
    {
        foreach (static::$foldersToLoad as $folder) {
            $path = APP_PATH . $folder;
            $files = array_diff(scandir($path), array('..', '.'));
            foreach ($files as $file) {
                require $path . '/' . $file;
            }
        }
    }

}