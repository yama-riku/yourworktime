<?php

session_start();

require_once '../class/UserLogic.php';


$result = UserLogic::checkLogin();
if($result) {
    header('Location: task.php');
    return;
}


$err = $_SESSION;

// セッションを消す
$_SESSION = array();
session_destroy();



?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel = "stylesheet" href = "login_form.css">
    <title>worktime/ログイン画面</title>
</head>
<body>
    <header class = "page-header">
        <h1>WorkTime</h1>
    </header>
    <main>

        <div class = "login">
            <h2>ログイン</h2>
            <?php if (isset($err['msg'])) : ?>
                <p class = "alert"><?php echo $err['msg']; ?></p>
            <?php endif; ?>
            <form action = "login.php" method = "POST">

                <div class = "employeeData">
                    <p>ユーザーID</p>
                    <input type = "email" name = "email">
                    <?php if (isset($err['email'])) : ?>
                        <p class = "alert"><?php echo $err['email']; ?></p>
                    <?php endif; ?>
                    <p>パスワード</p>
                    <input type = "password" name = "password">
                    <?php if (isset($err['password'])) : ?>
                        <p class = "alert"><?php echo $err['password']; ?></p>
                    <?php endif; ?>
                    
                </div>

                <div class = "loginButton">
                    <button type = "submit">login</button>
                </div> 
                <br>
                <a href = "signup_form.php">※新規登録画面へ</a>
            
            </form>
        </div>

    </main>
    <footer>
        <div class = "footerClass">
            <p><small>&copy; 2022 RIKU</small></p>
        </div>
    </footer>
</body>
</html>