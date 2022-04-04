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

// 当日の日付取得
// 時間軸を日本に設定
date_default_timezone_set('Asia/Tokyo');
$yyyymmdd = date('Y-m-d');
$yyyymm = date('Y-m');
// 編集欄の日付出力
$yyyy = date('Y');
$mm = date('m');
$dd = date('d');
$todayFormat = "%01d年%01d月";
$toDay = sprintf($todayFormat,$yyyy,$mm);

// 月の一覧データを取得
// プルダウン
if(isset($_GET['m'])) {
    $yyyymm = $_GET['m'];
    $day_count = date('t',strtotime($yyyymm));
}else {
    $yyyymm = date('Y-m');
    $day_count = date('t');
}

// 編集欄を〇〇〇〇年〇〇月表示にする
[$year,$month] = explode("-",$yyyymm);
$yymmFormat = "%01d年%01d月";
$yymmToday = sprintf($yymmFormat,$year,$month);

$pdo = connect();
$workData = UserLogic::getWorkTable($login_user['email']);

// 編集欄の実装

// 時間とコメントは分けて実装
// 時間編集の実装
// 出勤時間が入力されているのかチェック
if(isset($_POST['s_hour']) && isset($_POST['s_minute']) && isset($_POST['e_hour']) && isset($_POST['e_minute']) && isset($_POST['b_hour']) && isset($_POST['b_minute'])) {

    // 日付が入力されているか確認
    if($_POST['e_day'] == NULL) {
        $done =  '日付を入力してください';
    }else {
        // 日付が入力されているとき
        $e_day = $_POST['e_day'];
        if($e_day > $day_count) {
            $done = '日付が正しくないです';
        }else {
            // 正しい日付が入力されているとき
            // 取得した日付の整理
            $e_yymm = $_POST['e_ym'];
            $e_ddFormat = "%02d";
            $e_yymmdd = $e_yymm."-".sprintf($e_ddFormat,$e_day);

            // 出勤時間・退勤時間・休憩の取得
            $e_sHour = $_POST['s_hour'];
            $e_sMinute = $_POST['s_minute'];
            $e_eHour = $_POST['e_hour'];
            $e_eMinute = $_POST['e_minute'];
            $e_bHour = $_POST['b_hour'];
            $e_bMinute = $_POST['b_minute'];
            $e_Format = "%02d:%02d";
            $e_startTime = sprintf($e_Format,$e_sHour,$e_sMinute);
            $e_endTime = sprintf($e_Format,$e_eHour,$e_eMinute);
            $e_breakTime = sprintf($e_Format,$e_bHour,$e_bMinute);   

            
            // 実働の計算
            // 出勤から退勤までの時間
            // time_diff関数使用のため変数合わせる
            $startTime = $e_startTime;
            $endTime = $e_endTime;
            // 初期化
            $difftimeoutput = array();
            // function.phpにあるtime_diffを実行
            $difftimeoutput = time_diff($startTime,$endTime);
            // 出勤から退勤までの時間の出力
            $format = "%02d:%02d:%02d";
            $totalTime = sprintf($format,$difftimeoutput['hours'],$difftimeoutput['minutes'],$difftimeoutput['seconds']);

            // time_diff関数使用のため変数合わせる
            $startTime = $e_breakTime;
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

                $done = "入力時間が正しくありません";

            }else {  
            
        
                // 出勤時間が登録されているのか確認(後の処理も考えて出勤・退勤・休憩全て取得)
                $e_Time = UserLogic::etData($login_user['email'],$e_yymmdd);

                if(isset($e_Time['date'])) {
                    
                    // DB接続
                    $pdo = connect();
                    // update実行
                    $updateTime['email'] = $login_user['email'];
                    $updateTime['date'] = $e_yymmdd;
                    $updateTime['start_time'] = $e_startTime;
                    $updateTime['end_time'] = $e_endTime;
                    $updateTime['break_time'] = $e_breakTime;
                    $updateTime['actual_time'] = $actualTime;
                    $updateTime['over_time'] = $overTime;
                    $updateTimed = UserLogic::updateTime($updateTime);

                    // ページに反映
                    $workData = UserLogic::getWorkTable($login_user['email']);           
                    
                    

                } else {
                    // // 記入した日付がないとき
                    // // 新規で登録する場合

                    // // 新規で登録するとき
                    $pdo = connect();
                    // // 新規登録の実行
                    $newTime['email'] = $login_user['email'];
                    $newTime['date'] = $e_yymmdd;
                    $newTime['start_time'] = $e_startTime;
                    $newTime['end_time'] = $e_endTime;
                    $newTime['break_time'] = $e_breakTime;
                    $newTime['actual_time'] = $actualTime;
                    $newTime['over_time'] = $overTime;
                    $new_Time = UserLogic::newTime($newTime);
                    

                    // ページに反映
                    $workData = UserLogic::getWorkTable($login_user['email']);
                    
                
                }

            }


        }

    }


}


// コメント編集の実装
if(isset($_POST['e_comment'])) {
    // 空入力あり
    // 日付が入力されているか確認
    if($_POST['e_day'] == NULL) {
        $done = '日付を入力してください';
    }else {
        // 日付が入力されているとき
        $e_day = $_POST['e_day'];
        if($e_day > $day_count) {
            // 正しくない日付場合
            $done = '日付が正しくないです';
        }else {
            // 正しい日付が入力されているとき
            // 取得した日付の整理
            $e_yymm = $_POST['e_ym'];
            $e_ddFormat = "%02d";
            $e_yymmdd = $e_yymm."-".sprintf($e_ddFormat,$e_day);
            
            // コメントの取得
            $e_comment = $_POST['e_comment'];
            
            // 該当する日付のコメントを取得
            $ecDayData = UserLogic::ecData($login_user['email'],$e_yymmdd);
            
            
            //　選択した日付が既にあるのか判定
            if(isset($ecDayData['date'])) {
                // 既にあるとき
                $pdo = connect();
                // update実行
                $updateComment['email'] = $login_user['email'];
                $updateComment['date'] = $e_yymmdd;
                $updateComment['comment'] = $e_comment; 
                $update_comment = UserLogic::updateComment($updateComment);

                // ページに反映
                $workData = UserLogic::getWorkTable($login_user['email']);

            }else {
                // 新規で登録するとき
                $pdo = connect();
                // 新規登録の実行
                $newComment['email'] = $login_user['email'];
                $newComment['date'] = $e_yymmdd;
                $newComment['comment'] = $e_comment;
                $new_comment = UserLogic::newComment($newComment);
                

                // ページに反映
                $workData = UserLogic::getWorkTable($login_user['email']);
                
            } 

            
        }

    }    

}


?>




<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "stylesheet" href = "home.css">
    <title>worktime/ホーム画面</title>
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
                <li><a class = "hmList" href = "task.php">task</a></li>
                <li><a class = "hmList" href = "home.php">home</a></li>
                <li><a class = "hmList" href = "oparation.php">oparation</a></li>
            </ul>

        </nav>

    </header>

    <main>
        

        <div class = "home">

            <h2>月別勤怠時間</h2>           
            
            <div class = "edit">
                <form action = "home.php" method = "POST">

                    <h3>編集</h3>
                    <div class = "edit_day">
                        <p><?= $yymmToday;?></p>
                        <input type = "hidden" name = "e_ym" value = "<?=$yyyymm?>">
                        <input type = "tel" class = "e_day" name = "e_day" maxlength = "2" value = "">
                        <p>日</p>&nbsp;&nbsp;
                        <?php if (isset($done)) :?>
                            <p class = "alert"><?php echo $done;?></p>
                        <?php endif;?> 
                    </div>   
                    <div class = "edit_time">
                        <div class = "s_edit">
                            <p>出勤</p>
                            <input type = "tel" class = "s_hour" name = "s_hour" placeholder= "00" value = "">
                            <p>:</p>
                            <input type = "tel" class = "s_minute" name = "s_minute" placeholder= "00" value = "">
                            
                        </div>
                        <div class = "e_edit">
                            <p>退勤</p>
                            <input type = "tel" class = "e_hour" name = "e_hour" placeholder= "00" value = "">
                            <p>:</p>
                            <input type = "tel" class = "e_minute" name = "e_minute" placeholder= "00" value = "">
                        </div>
                        <div class = "b_edit">
                            <p>休憩</p>
                            <input type = "tel" class = "b_hour" name = "b_hour" placeholder= "00" value = "">
                            <p>:</p>
                            <input type = "tel" class = "b_minute" name = "b_minute" placeholder= "00" value = "">
                        </div>                           
                    </div>
                    <div class = "edit_comment" >
                        <p>備考</p>
                        <input type = "comment" class = "e_comment" name = "e_comment" maxlength = "30" placeholder="30文字以内で入力してください"  value = "">
                    </div>
                    <button type = "submit" name = "edit">edit</button>
                
                </form>

            </div>

        

            <form class = "border rounded bg-white form-time-table" action = "home.php">
                <select class = "form-control rounded-pill mb-3" name = "m" onchange = "submit(this.form)">
                    <option value = "<?= date('Y-m')?>"><?= date('Y/m')?></option>
                    <?php for($i = 1;$i < 12;$i++):?>
                        <?php $target_yyyymm = strtotime("-{$i}month");?> 
                    <option value = "<?= date('Y-m',$target_yyyymm)?>"<?php if($yyyymm == date('Y-m',$target_yyyymm)) echo 'selected'?>><?= date('Y/m',$target_yyyymm)?></option>
                    <?php endfor;?>
                </select>

                <table class="table table-bordered">
                    <thead>
                        <tr class = "bg-light">
                        <th scope="col" width = "5%">日</th>
                        <th scope="col" width = "5%">出勤</th>
                        <th scope="col" width = "5%">退勤</th>
                        <th scope="col" width = "5%">休憩</th>
                        <th scope="col" width = "5%">実働</th>
                        <th scope="col" width = "5%">残業</th>
                        <th scope="col" class = "comment" width = "70%">備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i = 1;$i <= $day_count;$i++): ?>
                            <?php 

                                // 記入されていない時は空で表示
                                $start_time = "";
                                $end_time = "";
                                $break_time = "";
                                $actual_time = "";
                                $over_time = "";
                                $comment = "";
                                if(isset($workData[date('Y-m-d',strtotime($yyyymm.'-'.$i))])) {
                                    $work = $workData[date('Y-m-d',strtotime($yyyymm.'-'.$i))];
                                    
                                    if($work['start_time']) {
                                        // 出勤時間が記入されていたら
                                        $start_time = date('H:i',strtotime($work['start_time']));
                                    }

                                    if($work['end_time']) {
                                        // 出勤時間が記入されていたら
                                        $end_time = date('H:i',strtotime($work['end_time']));
                                    }

                                    if($work['break_time']) {
                                        // 出勤時間が記入されていたら
                                        $break_time = date('H:i',strtotime($work['break_time']));
                                    }

                                    if($work['actual_time']) {
                                        // 出勤時間が記入されていたら
                                        $actual_time = date('H:i',strtotime($work['actual_time']));
                                    }

                                    if($work['over_time']) {
                                        // 出勤時間が記入されていたら
                                        $over_time = date('H:i',strtotime($work['over_time']));
                                    }

                                    if($work['comment']) {
                                        // 出勤時間が記入されていたら
                                        $comment = $work['comment'];
                                    }
                                }                       
                            ?>
                        <tr>
                        <th scope="row"><?= time_format_dw($yyyymm.'-'.$i)?></th>
                        <td><?= $start_time?></td>
                        <td><?= $end_time?></td>
                        <td><?= $break_time?></td>
                        <td><?= $actual_time?></td>
                        <td><?= $over_time?></td>
                        <td style = "word-break:break-all"><?= $comment ?></td>
                        </tr>
                        <?php endfor;?>
                    </tbody>
                </table>


            </form>

        
            <form action = "logout.php" method = "POST">
                <div class = "logout">
                    <input type = "submit" name = "logout" value = "logout">
                </div>
                
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