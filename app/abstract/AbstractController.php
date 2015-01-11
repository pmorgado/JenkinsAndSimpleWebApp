<?php

/**
 * Class AbstractController
 */
abstract class AbstractController
{
    /**
     * @var PDO
     */
    private $db = null;

    /**
     * initDB
     */
    private function initDB()
    {
        $dir = 'sqlite:'. APP_PATH .'/database.db';
        try {
            $conn = new PDO($dir);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db = $conn;
        } catch(PDOException $e) {
            exit("Connection failed: " . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * @return PDO
     */
    protected function getDb()
    {
        if(!$this->db)
        {
            $this->initDB();
        }
        return $this->db;
    }

    /**
     * @param $file
     * @param array $variables
     * @return string
     */
    protected function render($file, $variables = array())
    {
        extract($variables);
        ob_start();
        include APP_PATH . '/view/' . $file . '.phtml';
        $renderedView = ob_get_clean();
        echo $renderedView;
        return $renderedView;
    }
}