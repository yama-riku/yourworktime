<?php

session_start();
require_once '../class/UserLogic.php';
require_once '../function.php';

// ログインしているか判定し、していなければ新規登録画面へ遷移
$result = UserLogic::checkLogin();

if(!$result) {
    $_SESSION['login_err'] = 'ユーザーを登録してログインしてください';
    header('Location: signup_form.php');
    return;
}

$login_user = $_SESSION['login_user'];

// 時間軸を日本に設定
date_default_timezone_set('Asia/Tokyo');
$yyyymm = date('Y-m');


// 月の一覧データを取得
// プルダウン
if(isset($_GET['m'])) {
    $yyyymm = $_GET['m'];
    $day_count = date('t',strtotime($yyyymm));
}else {
    $yyyymm = date('Y-m');
    $day_count = date('t');
}

// データ取得
$pdo = connect();
$monthData = UserLogic::getMonthData($login_user['email'],$yyyymm);
// var_dump($monthData);

// 出勤日数の実装-------------------------------------------------
// 出勤日数のカウント変数を準備
$workDay = 0;
for($i = 1;$i <= $day_count;$i++) {
    // 年月を分割
    [$yy,$mm] = explode("-",$yyyymm);
    $dayFormat = "%04d-%02d-%02d";
    $prepareDay = sprintf($dayFormat,$yy,$mm,$i);

    if(isset($monthData[$prepareDay]['actual_time'])) {
        
        // 実働時間を入れる
        $actualTime = $monthData[$prepareDay]['actual_time'];
        // 秒は切り捨てる
        [$acHH,$acMM,$acSS] = explode(":",$actualTime);
        $acFormat = "%02d:%02d";
        $actualTime = sprintf($acFormat,$acHH,$acMM);        
        
        if($actualTime != "00:00") {
            
            // 出勤日数を計算
            $workDay += 1;

        }

    }
    
}

// 稼働時間の実装----------------------------------------------------
$monthTime = "00:00:00";
for($i = 1;$i <= $day_count;$i++) {
    // 年月を分割
    [$yy,$mm] = explode("-",$yyyymm);
    $dayFormat = "%04d-%02d-%02d";
    $prepareDay = sprintf($dayFormat,$yy,$mm,$i);

    if(isset($monthData[$prepareDay]['actual_time'])) {
        
        // 実働時間を入れる
        $actualTime = $monthData[$prepareDay]['actual_time'];
        // echo $actualTime;
        
        $monthTime = addTime($monthTime,$actualTime);

        
        

    }
    
}


// 時間の表記を変換
[$monthH,$monthM,$monthS] = explode(":",$monthTime);
$monthM = ($monthM / 60);
$monthM = number_format($monthM,1);
$monthTime = $monthH + $monthM;
$monthTime = sprintf("%.1f",$monthTime);



// 残業時間の実装----------------------------------------------------
$overMTime = "00:00:00";
for($i = 1;$i <= $day_count;$i++) {
    // 年月を分割
    [$yy,$mm] = explode("-",$yyyymm);
    $dayFormat = "%04d-%02d-%02d";
    $prepareDay = sprintf($dayFormat,$yy,$mm,$i);

    if(isset($monthData[$prepareDay]['over_time'])) {
        
        // 実働時間を入れる
        $overTime = $monthData[$prepareDay]['over_time'];
        // echo $actualTime;
        
        $overMTime = addTime($overMTime,$overTime);
        

    }
    
}

// 時間の表記を変換
[$overH,$overM,$overS] = explode(":",$overMTime);
$overM = ($overM / 60);
$overM = number_format($overM,1);
$overMTime = $overH + $overM;
$overMTime = sprintf("%.1f",$overMTime);







?>




<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 ,viewport-fit=cover">
    <link rel = "stylesheet" href = "oparation.css">
    <script src = "oparation.js" defer></script>
    <title>worktime/稼働画面</title>

</head>
<body>
    <header class = "page-header">
        <h1>WorkTime</h1>

        <div id = "nav-wrapper" class = "nav-wrapper">
            <div class = "hamburger" id = "js-hamburger">
                <span class = "hamburger__line hamburger__line--1"></span>
                <span class = "hamburger__line hamburger__line--2"></span>
                <span class = "hamburger__line hamburger__line--3"></span>
            </div>
            <nav class = "sp-nav">
                <ul>
                    <h2>Menu</h2>
                    <div class = "menuBorder"></div>
                    <li><a class = "hmSList" href = "task.php">task</a></li>
                    <li><a class = "hmSList" href = "home.php">home</a></li>
                    <li><a class = "hmSList" href = "oparation.php">oparation</a></li>
                </ul>
            </nav>
            <div class = "black-bg" id = "js-black-bg"></div>
        </div>

        <nav id = "nav" class = "pcMenu">
            <ul class = "headerMenu">
                <li><a class = "hmList" href = "task.php">task</a></li>
                <li><a class = "hmList" href = "home.php">home</a></li>
                <li><a class = "hmList" href = "oparation.php">oparation</a></li>
            </ul>

        </nav>

    </header>

    <main>
        <form action = "oparation.php">

            <div class = "oparation">

                <h1>稼働詳細</h1>

                <select class = "form-control rounded-pill mb-3" name = "m" onchange = "submit(this.form)">
                    <option value = "<?= date('Y-m')?>"><?= date('Y/m')?></option>
                    <?php for($i = 1;$i < 12;$i++):?>
                        <? $target_yyyymm = strtotime("-{$i}months");?>
                    <option value = "<?= date('Y-m',$target_yyyymm) ?>"<?php if($yyyymm == date('Y-m',$target_yyyymm)) echo 'selected' ?>><?= date('Y/m',$target_yyyymm) ?></option>
                    <?php endfor;?>
                </select>

                <div class = "oparationBlock">

                    <div class = "totalDay">
                        <h2>出勤</h2>
                        <h1><?= $workDay?></h1>
                        <h3>日</h3>

                    </div>

                    <div class = "totalActual">
                        <h2>実働</h2>
                        <h1><?= $monthTime?></h1>
                        <h3>時間</h3>
                    </div>

                    <div class = "totalOver">
                        <h2>残業</h2>
                        <h1><?= $overMTime?></h1>
                        <h3>時間</h3>

                        
                    </div>

                    <div class = "userData">
                        <h2>ユーザー情報</h2>
                        <h3>ユーザーID</h3>
                        <p><?php echo h ($login_user['email']) ?></p>
                        <h3>ユーザー名</h3>
                        <p><?php echo h ($login_user['name']) ?></p>
                    </div>

                    <div class = "space"></div>
                
                </div>



            </div>

        </form>

    </main>

    <footer>
        <div class = "footerClass">
            <p><small>&copy; 2022 RIKU</small></p>
        </div>
    </footer>
    

    
</body>
</html>