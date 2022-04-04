<?php


/**
 * XSS対策：エスケープ処理
 * 
 * @param string $str 対象の文字列
 * @return string 処理された文字列
 *  
 * */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF対策
 * @param void 
 * @return string $csrf_token
 * 
 *  */
function setToken() {
    // トークンの生成
    // フォームからそのトークンを送信
    // 送信後の画面でそのトークンを照会
    // トークンの削除

    
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;

    return $csrf_token;

}


// 時間の差分を出すための関数
function time_diff($startTime,$endTime){
    // 初期化
    $diffTime = array();

    // タイムスタンプ
    $timestamp1 = strtotime($startTime);
    $timestamp2 = strtotime($endTime);
    

    // タイムスタンプの差を計算
    $difseconds = $timestamp2 - $timestamp1;
    
    // 秒の差を取得
    $difftime['seconds'] = $difseconds % 60;
    // 分の差を取得
    $difminutes = ($difseconds - ($difseconds % 60)) /60;
    $difftime['minutes'] = $difminutes % 60;
    // 時間の差を取得
    $difhours = ($difminutes - ($difminutes % 60)) / 60;
    $difftime['hours'] = $difhours;

    // 結果を返す
    return $difftime;

}    

// 日付を日(曜日)の形式に変換する
function time_format_dw($date) {
    $format_date = NULL;
    $week = array('日','月','火','水','木','金','土');

    if($date) {
        $format_date = date('j('.$week[date('w',strtotime($date))].')',strtotime($date));
    }

    return $format_date;

}





?>