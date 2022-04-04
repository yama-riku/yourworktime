<?php

session_start();

require_once '../class/UserLogic.php';

// エラーが発生したらエラーメッセージの配列に入れる
$err = [];

$token = filter_input(INPUT_POST, 'csrf_token');
// トークンがない、もしくは一致しない場合、処理を中止
if(!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
    exit('不正なリクエスト');
}

unset($_SESSION['csrf_token']);

// バリデーション
if(!$username = filter_input(INPUT_POST, 'username')) {
    $err[] = '社員名を記入してください。';
}
if(!$email = filter_input(INPUT_POST, 'email')) {
    $err[] = '社員IDを記入してください。';
}
$password = filter_input(INPUT_POST,'password');
// 正規表現
if(!preg_match("/\A[a-z\d]{8,100}+\z/i",$password)) {
    $err[] = 'パスワードは英数字8文字以上100文字以下にしてください。';
}
$password_conf = filter_input(INPUT_POST,'password_conf');
if($password !== $password_conf) {
    $err[] = '確認用のパスワードと異なっています。';
}

if(count($err) === 0) {
    // ユーザーを登録する処理
    $hasCreated = UserLogic::createUser($_POST);

    if(!$hasCreated) {
        $err[] = '登録に失敗しました';
    }

}


?>




<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "stylesheet" href = "signup_form.css">
    <title>worktime/社員登録完了画面</title>
</head>
<body>
    <header class = "page-header">
        <h1>WorkTime</h1>
    </header>
    <main>
        <?php if(count($err) > 0) : ?>
            <?php foreach($err as $e) :?>
                <p><?php echo $e ?></p>
            <?php endforeach ?>
        <?php else : ?>
            <p>登録が完了しました</p>
        <?php endif ?>
        <a href = "./signup_form.php">戻る</a>

        
    </main>
    <footer>
        <div class = "footerClass">
            <p><small>&copy; 2022 RIKU</small></p>
        </div>
    </footer>
</body>
</html>