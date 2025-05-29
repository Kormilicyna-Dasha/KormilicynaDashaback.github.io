
<?php

/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин и id пользователя.
 * После авторизации пользователь перенаправляется на главную страницу для изменения ранее введенных данных.
 **/

// Отправляем браузеру правильную кодировку,
// файл login.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');


function isValid($login, $db) {
  $count;
  try{
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $count = $stmt->fetchColumn();
  } 
  catch (PDOException $e){
    print('Error : ' . $e->getMessage());
    exit();
  }
  return $count > 0;
}

function password_check($login, $password, $db) {
  $passw;
  try{
    $stmt = $db->prepare("SELECT password FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $passw = $stmt->fetchColumn();
    //print($password);
    if($passw===false){
      return false;
    }
    //print(" ");
    //print($passw);
    return password_verify($password, $passw);
  } 
  catch (PDOException $e){
    print('Error : ' . $e->getMessage());
    return false;
  }
  
}



// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.

$session_started = false;

if (isset($_COOKIE[session_name()]) && session_start()) {
  $session_started = true;
  if (!empty($_SESSION['login'])) {
    // Если есть логин в сессии, то пользователь уже авторизован.
    // TODO: Сделать выход (окончание сессии вызовом session_destroy()
    // при нажатии на кнопку Выход).
    // Делаем перенаправление на форму.

    if(isset($_POST['logout'])){
      session_unset();
      session_destroy();
      header('Location: login.php');
      exit();
    }


    header('Location: ./');
    exit();
  }
}

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

  
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"  href="style.css">
    <title> LAB5 </title>
  </head>
  <body>

  <?php
      if (!empty($messages)) {
        print('<div id="login_messages">');
        foreach ($messages as $message) {
          print($message);
        }
        print('</div>');
      }
      ?>

    <form class="login_form" action="" method="post">
      <label> 
        Логин <br/>
        <input name="login" />
      </label> <br/>
      <label> 
        Пароль <br/>
        <input name="password" />
      </label> <br/>
      <input class="login_button" type="submit" value="Войти" />
    </form>

  </body>
</html>

<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {
  $login_messages='';
  $login = $_POST['login'];
  //$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $password=$_POST['password'];

  $user = 'u68607';
  $pass = '7232008';
  $db = new PDO('mysql:host=localhost;dbname=u68607', $user, $pass,
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

  // TODO: Проверть есть ли такой логин и пароль в базе данных.
  // Выдать сообщение об ошибках.

  if (!$session_started) {
    session_start();
  }
  // Если все ок, то авторизуем пользователя.
  if (isValid($login, $db) && password_check($login, $password, $db)){
    $_SESSION['login'] = $_POST['login'];
    // Записываем ID пользователя.

    $_SESSION['uid'];
      try {
          $stmt_select = $db->prepare("SELECT id FROM users WHERE login=?");
          $stmt_select->execute([$_SESSION['login']]);
          $_SESSION['uid']  = $stmt_select->fetchColumn();
      } catch (PDOException $e){
          print('Error : ' . $e->getMessage());
          exit();
      }

      // Делаем перенаправление.
      header('Location: ./');
  }
  else {
    $messages[] = 'Неверный логин или пароль';
    //$login_messages="<div class='login_messages'>Неверный логин или пароль</div>";
    //header('Location: login.php');
    print('Неверный логин или пароль'); 
    //exit();
  }


}