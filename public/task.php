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

// 出退勤の実装--------------------------------------------

// 当日の時間取得
// 時間軸を日本に設定
date_default_timezone_set('Asia/Tokyo');
// 西暦・月日
$yyyymm = date('Y-m-d');
// 出退勤ボタンの上部の日付出力
$yyyy = date('Y');
$mm = date('m');
$dd = date('d');
$todayFormat = "%01d年%01d月%01d日";
$toDay = sprintf($todayFormat,$yyyy,$mm,$dd);

// 出勤ボタンを押したとき------------------------------------
if(isset($_POST['start_time'])) {

    // 押したときの時間取得
    $startTime = new DateTime();
    // $startTime = $startTime->format('H:i:s');
    $startTime = $startTime->format('H:i');
    
    
    // ユーザーの出退勤履歴をDBから取得
    if($works = UserLogic::todayData($login_user['email'])) {

        //　出勤時間登録済みのメッセージ出力
        $done = '出勤時間登録済みです';

    }else {
        // まだ出勤時間を登録してない場合
        // DB接続
        $pdo= connect();

        // 当日の日付取得
        $today = $yyyymm;

        // 登録するデータ
        $newDayTime['email'] = $login_user['email'];
        $newDayTime['date'] = $today;
        $newDayTime['start_time'] = $startTime;

        $newDate_Time = UserLogic::newDayTime($newDayTime);

        // 出勤完了のメッセージ
        $done = '出勤しました';

    }

}

// 退勤ボタンを押したとき---------------------------------------
if(isset($_POST['end_time'])) {

    // ユーザーの出退勤履歴をDBから取得
    if($works = UserLogic::todayData($login_user['email'])) {

        // 退勤時間が未入力なら登録
        if($works['end_time'] == NULL) {
            
            // 登録するデータの整理
            // 出勤時間----------
            $startTime = $works['start_time'];
            
            // 退勤時間----------
            $endTime = new DateTime();
            // $endTime = $endTime->format('H:i:s');
            $endTime = $endTime->format('H:i');   //秒まで取ると実働が上手く表示されないため、こちら使用
            // 後の退勤時間入力のために$endedTimeで定義
            $endedTime = $endTime;
            
            // 出勤時間から退勤時間までの時間---------
            // 初期化
            $difftimeoutput = array();
            // function.phpにあるtime_diffを実行
            $difftimeoutput = time_diff($startTime,$endTime);
            // 出勤から退勤までの時間の出力
            $format = "%02d:%02d:%02d";
            $totalTime = sprintf($format,$difftimeoutput['hours'],$difftimeoutput['minutes'],$difftimeoutput['seconds']);
            
            // 休憩時間----------
            // 休憩時間取得
            $breakHour = $_POST['break_hour'];
            $breakMinute = $_POST['break_minute'];
            $breakSecond = $_POST['break_second'];
            // 形式合わせる
            $breakFormat = "%02d:%02d:%02d";
            $breakTime = sprintf($breakFormat,$breakHour,$breakMinute,$breakSecond);
            
            
            // 実働時間----------
            // $totalTimeから$breakTimeを引くことで稼働時間を出力
            // time_diff関数使用のため変数合わせる
            $startTime = $breakTime;
            $endTime = $totalTime;
            // 初期化
            $difftimeoutput = array();
            // function.phpにあるtime_diffを実行
            $difftimeoutput = time_diff($startTime,$endTime);
            // 合計時間から休憩時間の差分である実働時間出力
            $format = "%02d:%02d:%02d";
            // 実働時間を時間、分、秒で取得（後のマイナス判定で使用）
            $actualHour = $difftimeoutput['hours'];
            $actualMinute = $difftimeoutput['minutes'];
            $actualSecond = $difftimeoutput['seconds'];
            $actualTime = sprintf($format,$difftimeoutput['hours'],$difftimeoutput['minutes'],$difftimeoutput['seconds']);
            
            
            // 残業時間----------
            if($difftimeoutput['hours'] >= 8) {

                // 残業時間は８時間超えた時とする
                $standardTime = "08:00:00";
                // timediff関数使用のため変数合わせる
                $startTime = $standardTime;
                $endTime = $actualTime;
                // 初期化
                $difftimeoutput = array();
                // function.phpにあるtime_diffを実行
                $difftimeoutput = time_diff($startTime,$endTime);
                // 実働時間から８時間を引くことで残業時間を出力
                $format = "%02d:%02d:%02d";
                $overTime = sprintf($format,$difftimeoutput['hours'],$difftimeoutput['minutes'],$difftimeoutput['seconds']);
                
            
            }else {
                $overTime = "00:00:00";
            }

            // 実働時間がマイナスでなるか判定
            if($actualHour < 0 || $actualMinute < 0 || $actualSecond < 0) {

                $done = "休憩時間が正しくありません";

            }else {

                // DB接続
            $pdo = connect();
            // 日付取得
            $today = $yyyymm;
            // 登録するデータ(退勤時間、休憩時間、実働時間、残業時間)
            $endDayTime['email'] = $login_user['email'];
            $endDayTime['date'] = $today;
            $endDayTime['end_time'] = $endedTime;
            $endDayTime['break_time'] = $breakTime;
            $endDayTime['actual_time'] = $actualTime;
            $endDayTime['over_time'] = $overTime;

            $endDay_Time = UserLogic::endDayTime($endDayTime);

            // 退勤完了メッセージ
            $done = "退勤しました";

            }    

            

        }else {
            // 退勤時間登録済み
            $done = '退勤済みです';
        }

    }else {
        // 出勤ボタンよりも先に退勤ボタンを押したときのメッセージ
        $done = '出勤ボタンを押してください';
    }
    

}


?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 ,viewport-fit=cover">
    <link rel = "stylesheet" href = "task.css">
    <script src = "task.js" defer></script>
    <title>worktime/タスク画面</title>
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
        <form action = "task.php" method = "POST">

            <div class = "taskLarge">

                <div class = "task">
                    <h2><?php echo $toDay?></h2>
                    <h3 id = "showTime"></h3>
                    <?php if (isset($done)) :?>
                        <p class = "alert"><?php echo $done;?></p>
                    <?php endif;?> 
                    <div class = "taskButton">
                        <button type = "submit" class = "startButton" name = "start_time">出勤</button>
                        <button type = "submit" class = "endButton" name = "end_time">退勤</button>
                    </div>
                    <div class = "breakTime">
                        <h4>休憩時間：</h4>
                        <select name = "break_hour">
                            <?php 
                            $formatI = "%02d";
                            for($i = 0;$i < 25;$i++) {
                                if($i == 0) {
                                    echo '<option value = "'.sprintf($formatI,$i).'" selected>'.sprintf($formatI,$i).'</option>';
                                }else {
                                    echo '<option value = "'.sprintf($formatI,$i).'">'.sprintf($formatI,$i).'</option>';
                                }
                            }                  
                            
                            ?>
                        </select>
                        <p>:</p>
                        <select name = "break_minute">
                            <?php 
                            $formatJ = "%02d";
                            for($j = 0;$j < 60;$j++) {
                                if($j == 0) {
                                    echo '<option value = "'.sprintf($formatJ,$j).'" selected>'.sprintf($formatJ,$j).'</option>';
                                }else {
                                    echo '<option value = "'.sprintf($formatJ,$j).'">'.sprintf($formatJ,$j).'</option>';
                                }
                            }                  
                            
                            ?>
                        </select>
                        <p>:</p>
                        <select name = "break_second">
                            <?php 
                            $formatK = "%02d";
                            for($k = 0;$k < 60;$k++) {
                                if($k == 0) {
                                    echo '<option value = "'.sprintf($formatK,$k).'" selected>'.sprintf($formatK,$k).'</option>';
                                }else {
                                    echo '<option value = "'.sprintf($formatK,$k).'">'.sprintf($formatK,$k).'</option>';
                                }
                            }                  
                            
                            ?>
                        </select>

                    </div>
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