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

            // validate comment
            if (empty($name) && empty($email)) {
                exit("must provide a comment");
            }

            // sanitize comment
            $name = strip_tags($name);
            $email = strip_tags($email);

            $this->getDb()->prepare("INSERT INTO users(name, email) VALUES (:name, :email);")
                ->execute(array(
                    ':name' => $name,
                    ':email' => $email));

            header('Location: http://challenge/list', 200);

        } else {
            throw new RuntimeException('CSRF attack');
        }
    }
}