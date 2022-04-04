<?php


session_start();

require_once '../class/UserLogic.php';


// エラーが発生したらエラーメッセージの配列に入れる
$err = [];

// バリデーション
if(!$email = filter_input(INPUT_POST, 'email')) {
    $err['email'] = '社員IDを記入してください。';
}
if(!$password = filter_input(INPUT_POST,'password')
) {
    $err['password'] = 'パスワードを記入してください。';

}

if(count($err) > 0) {
    // エラーがあった場合は戻す
    $_SESSION = $err;
    header('Location: login_form.php');
    return;
}

// ログイン成功時の処理
$result = UserLogic::login($email,$password);

// ログイン失敗時の処理
if(!$result) {
    header('Location: login_form.php');
    return;

}else {
    // ログイン成功後はタスクページに自動遷移
    header('Location: task.php');
}



?>




<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "stylesheet" href = "login.css">
    <title>worktime/ログイン完了画面</title>
</head>
<body>
    <header class = "page-header">
        <h1>WorkTime</h1>
    </header>
    <main>
        <p>ログイン完了</p>
        <a href = "./task.php">タスクページへ</a>

        
    </main>
    <footer>
        <div class = "footerClass">
            <p><small>&copy; 2022 RIKU</small></p>
        </div>
    </footer>
</body>
</html>