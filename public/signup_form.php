<?php

session_start();

require_once '../function.php';
require_once '../class/UserLogic.php';

$result = UserLogic::checkLogin();
if($result) {
    header('Location: task.php');
    return;
}

$login_err = isset($_SESSION['login_err']) ? $_SESSION['login_err'] : null;
unset($_SESSION['login_err']);





?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "stylesheet" href = "signup_form.css">
    <title>worktime/新規登録画面</title>
</head>
<body>
    <header class = "page-header">
        <h1>WorkTime</h1>
    </header>
    <main>

        <div class = "login">

            <div class = "signupUser">

                <h2>新規登録</h2>
                <?php if (isset($login_err)) : ?>
                    <p class = "alert"><?php echo $login_err; ?></p>
                <?php endif;?>
                
                
                <form action = "register.php" method = "POST">

                    <div class = "employeeData">
                        <p>ユーザー名</p>
                        <input type = "text" name = "username">
                        <p>ユーザーID</p>
                        <input type = "email" name = "email">
                        <p>パスワード</p>
                        <input type = "password" name = "password">
                        <p>パスワード確認</p>
                        <input type = "password" name = "password_conf">
                        <input type = "hidden" name = "csrf_token" value = "<?php echo h(setToken()); ?>">
                    </div>

                    <div class = "loginButton">
                        <button type = "submit">registry</button>
                    </div> 
                    <br>

                    <a href = "login_form.php">※ログイン画面へ</a>
                
                </form>
            </div>

        </div>

    </main>
    <footer>
        <div class = "footerClass">
            <p><small>&copy; 2022 RIKU</small></p>
        </div>
    </footer>
</body>
</html>