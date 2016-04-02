<?php
//Подключение БД--------------------------------------------------------------------------------------------
function db_plug()
{

    $connection = new \PDO("mysql:host=localhost;dbname=blog", 'root', 'vagrant', [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    ]);
    return $connection;
}

//Авторизация пользователя---------------------------------------------------------------------------------------------
function autorisation()
{
    //Логика
    $connection = db_plug();
    if (!empty($_POST['email']) && !empty($_POST['pass'])) {
        $sql = $connection->prepare(" SELECT * FROM `users` WHERE `email`=:_email AND `password`=:_pass");
        $sql->execute([':_email' => $_POST['email'], ':_pass' => md5($_POST['pass'])]);

        if (!empty($data = $sql->fetchAll())) {
            $_SESSION["autorisation"] = true;
            $_SESSION["user"] = $data[0]['email'];
            $_SESSION["id"]= $data[0]['id'];

        } else {
            $_SESSION["wrong_user_alert"] = true;
        }

    }
    if (!empty($_POST['btn_logout'])) {
        $_SESSION["autorisation"] = false;
        $_SESSION["id"]=null;
        $_SESSION["user"]=null;
        $_SESSION["wrong_user_alert"] = false;
        header("Location:http://192.168.100.220/index.php");
    }
  
   echo template('templates/autorisation.php', [
        'autorisation' => $_SESSION["autorisation"],
        'user' => $_SESSION["user"],
        'id' => $_SESSION["id"],
        'alert' => $_SESSION["wrong_user_alert"],
        
    ]);
    
}

//Генерация токена-----------------------------------------------------------------------------------------------------
function get_token() {
    $token = uniqid();
    $_SESSION['token'] = $token;
    return $token;
}
//Проверка токена------------------------------------------------------------------------------------------------
function valid_token($token) {
    return !empty($_SESSION['token']) && $token == $_SESSION['token'];
}
//Шаблон---------------------------------------------------------------------------------------------------------
function template($name, array $vars = [])
{
    if (!is_file($name)) {
        throw new exception("Could not load template file {$name}");
    }
    ob_start();
    extract($vars);
    require($name);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
}
?>