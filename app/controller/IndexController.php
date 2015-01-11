<?php

/**
 * Class IndexController
 */
class IndexController extends AbstractController
{
    /**
     * Index Action
     */
    public function actionIndex()
    {
        $this->render('index');
    }

    /**
     * List all users
     */
    public function actionList()
    {
        $stmt = $this->getDb()->prepare("select name, email from users");
        $stmt->execute();
        $result = $stmt->fetchall(PDO::FETCH_ASSOC);
        $this->render('list', array('list' => $result));
    }

    /**
     * Add
     */
    public function actionAdd()
    {
        session_start();
        $token= md5(uniqid());
        $_SESSION['csrf']= $token;
        session_write_close();
        $this->render('add', array('token' => $token));

    }

    /**
     * Save
     */
    public function actionSave()
    {
        session_start();
        $token = $_SESSION['csrf'];
        unset($_SESSION['csrf']);
        session_write_close();

        if ($token && $_POST['token']==$token) {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);

            // sanitize comment
            $name = strip_tags($name);
            $email = strip_tags($email);

            // validate data
            $regexString = array("options"=>array("regexp"=>"/^[A-Z][a-zA-Z -]+$/"));
            if (!filter_var($name, FILTER_VALIDATE_REGEXP, $regexString)) {
                exit("Full Name must contain letters, dashes and spaces only and must start with upper case letter.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                exit("The email is not valid");
            }

            $this->getDb()
                ->prepare("INSERT INTO users(name, email) VALUES (:name, :email);")
                ->execute(array(
                    ':name' => $name,
                    ':email' => $email));

            header('Location: http://challenge/list', 200);

        } else {
            throw new RuntimeException('CSRF attack');
        }
    }
}