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
        $route = $_SERVER['REQUEST_URI'];
        if (isset(static::$routeMap[$route])) {
            $controller = new IndexController();
            $controller->{'action'. static::$routeMap[$route]}();
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