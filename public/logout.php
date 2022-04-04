<?php

session_start();
require_once '../class/UserLogic.php';

if(!$logout = filter_input(INPUT_POST, 'logout'))
{
    exit('不正なリクエストです。');
}

// ログインしているか判定し、セッションが切れていたらログインしてくださいとメッセージを出す。
$result = UserLogic::checkLogin();

if(!$result) {
    exit('セッションが切れましたのでログインし直してください。');

}

// ログアウトする
UserLogic::logout();
// ログイン画面へ自動遷移
header('Location: login_form.php');




?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "stylesheet" href = "logout.css">
    <title>worktime/ログアウト画面</title>
</head>
<body>
    <header class = "page-header">
        <h1>WorkTime</h1>

        <a class = "manuicon" id = "menuicon"> 
            <div></div>
            <div></div>
            <div></div>
        </a>

        <nav id = "nav">
            <ul class = "headerMenu">
                <li><a>task</a></li>
                <li><a>home</a></li>
                <li><a>oparation</a></li>
            </ul>

        </nav>

    </header>

    <main>
        <div class = "logout">
            <h1>ログアウト完了</h1>
            <a href = "login_form.php">ログイン画面へ</a>
        </div>
        
    </main>

    <footer>
        <div class = "footerClass">
            <p><small>&copy; 2022 RIKU</small></p>
        </div>
    </footer>
</body>

</html>