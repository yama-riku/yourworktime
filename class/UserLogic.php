<?php

require_once '../dbconnect1.php';

class UserLogic
{
    /**
     * ユーザーを登録する
     * @param array $userData
     * @return bool $result
     */
    public static function createUser($userData)
    {
        $result = false;

        $sql = 'INSERT INTO users (name, email, password) VALUES (?, ?, ?)';

        // ユーザーデータを配列に入れる
        $arr = [];
        $arr[] = $userData['username'];
        $arr[] = $userData['email'];
        $arr[] = password_hash($userData['password'],PASSWORD_DEFAULT);

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            return $result;
        }catch(\Exception $e) {
            // なぜか登録失敗となり、この下のコードを書くことで解決文が出てきてエラー解消出来た
            // echo $e;
            // error_log($e, 3, '..error.log');
            return $result;
        }

        
    }

    /**
     * ログイン処理
     * @param string $email
     * @param string $password 
     * @return bool $result
     */
    public static function login($email,$password) 
    {
        // 結果
        $result = false;
        // ユーザーをemailから検索して取得
        $user = self::getUserByEmail($email);

        if(!$user) {
            $_SESSION['msg'] = '社員IDが一致しません。';
            return $result;
        }

        // パスワードの照会
        if(password_verify($password,$user['password'])) {
            // ログイン成功
            session_regenerate_id(true);
            $_SESSION['login_user'] = $user;
            $result = true;
            return $result;
        }

        $_SESSION['msg'] = 'パスワードが一致しません。';
            return $result;


    }

    /**
     * emailからユーザーを取得
     * @param string $email
     * @param string $password 
     * @return array|bool $user|false
     */
    public static function getUserByEmail($email) 
    {
        // SQLの準備
        // SQLの実行
        // SQLの結果を返す
        $sql = 'SELECT * FROM users WHERE email = ?';

        // emailを配列に入れる
        $err = [];
        $arr[] = $email;

        try {
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            // SQLの結果を返す
            $user = $stmt->fetch();
            return $user;
        }catch(\Exception $e) {
            return false;
        }



    }

    /**
     * ログインチェック
     * @param void
     * @param bool $result
     * 
     */
    public static function checkLogin() 
    {
        $result = false;

        // セッションにログインユーザーが入っていなければfalse
        if (isset($_SESSION['login_user']) && $_SESSION['login_user']['id'] > 0) {

            return $result = true;
        }
    
    }

    /**
     * worksテーブルのデータを一括取得
     * @param array $todayData
     * @return bool $result
     */
    public static function todayData($todayData)
    {
        // 当日の日付取得
        $today = '';
        $today = date('Y-m-d');

        $sql = 'SELECT works.date
                      ,works.start_time
                      ,works.end_time
                      ,works.break_time
                      ,works.actual_time
                      ,works.over_time
                  FROM works
                 WHERE works.email = ? AND works.date = ?';

        // データを配列に加える
        $arr = [];
        $arr[] = $todayData;
        $arr[] = $today;

        try {
            $stmt = connect() -> prepare($sql);
            $stmt -> execute($arr);
            // sqlの結果を返す
            $user = $stmt -> fetch();
            return $user;
        }catch(\Exception $e) {
            return false;
        }
        

    }

    /**
     * 出勤時間の登録
     * @param array $newDayTime
     * @return bool $result
     */
    public static function newDayTime($newDayTime)
    {
        // 当日の時間取得
        $today = '';
        $today = date('Y-m-d');

        $result = false;

        $sql = 'INSERT INTO works(email,date,start_time) VALUES (?,?,?)';

        // データを配列に加える
        $arr = [];
        $arr[] = $newDayTime['email'];
        $arr[] = $today;
        $arr[] = $newDayTime['start_time'];

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            return $result;
        }catch(\Exception $e) {
            return $result;
        }

    }
    
    /**
     * 退勤ボタンを押したときの退勤時間、休憩時間、実働時間、残業時間の記入
     * @param array $endDayTime
     * @return bool $result
     */
    public static function endDayTime($endDayTime)
    {
        $result = false;
        
        $sql = 'UPDATE works
                   SET works.end_time = ?
                      ,works.break_time = ?
                      ,works.actual_time = ?
                      ,works.over_time = ?
                 WHERE works.email = ? AND works.date = ?
                ';

        //　データを配列に加える
        $arr = [];
        $arr[] = $endDayTime['end_time'];
        $arr[] = $endDayTime['break_time'];
        $arr[] = $endDayTime['actual_time'];
        $arr[] = $endDayTime['over_time'];
        $arr[] = $endDayTime['email'];
        $arr[] = $endDayTime['date'];


        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);        
            return $result;
        }catch(\Exception $e){
            return $result;
        }
          
    } 

    // home.phpの実装---------------------------------------------------

    /**
     * worksテーブルのデータを取得
     * @param array $workData
     * @return bool $result
     */

    public static function getWorkTable($workData)
    {
        $sql = 'SELECT works.date
                      ,works.start_time
                      ,works.end_time
                      ,works.break_time
                      ,works.actual_time
                      ,works.over_time
                      ,works.comment
                  FROM works
                 WHERE works.email = ?
                ';
        
        // 配列に入れる
        $arr = [];
        $arr[] = $workData;

        try{
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            //   SQLの結果を返す
            $user = $stmt->fetchall(PDO::FETCH_UNIQUE);
            return $user;
        } catch(\Exception $e){
            return false;
        }

    
    }

    

    /**
     * worksテーブルのデータを取得(時間)
     * @param array $etData
     * @return bool $user
     */

    public static function etData($etData,$etDate)
    {
        $sql = 'SELECT works.date
                      ,works.start_time
                      ,works.end_time
                      ,works.break_time
                  FROM works
                 WHERE works.email = ? AND works.date = ?  
                ';
        
        // 配列に入れる
        $arr = [];
        $arr[] = $etData;
        $arr[] = $etDate;

        try {
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            //sqlの結果を返す
            $user = $stmt->fetch();
            return $user;
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     *編集した新しい時間入力(アップデート登録)
     * @param array $updateTime
     * @return bool $result
     */
    public static function updateTime($updateTime)
    {
        $result = false;
            

        $sql = 'UPDATE works
                    SET works.start_time = ?
                       ,works.end_time = ?
                       ,works.break_time = ?
                       ,works.actual_time = ?
                       ,works.over_time = ?
                WHERE works.email = ? AND works.date = ?
            ';

        //　配列に加える
        $arr = [];
        $arr[] = $updateTime['start_time'];
        $arr[] = $updateTime['end_time'];
        $arr[] = $updateTime['break_time'];
        $arr[] = $updateTime['actual_time'];
        $arr[] = $updateTime['over_time'];
        $arr[] = $updateTime['email'];
        $arr[] = $updateTime['date'];


        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);        
            return $result;
        }catch(\Exception $e){
            echo $e;        
            return $result;
        }



    }

    /**
     *新規の時間を入力する
     * @param array $newTime
     * @return bool $result
     */
    public static function newTime($newTime)
    {
        $result = false;

        $sql = 'INSERT INTO works(email,date,start_time,end_time,break_time,actual_time,over_time) VALUES (?,?,?,?,?,?,?)';

        // 配列に加える
        $arr = [];
        $arr[] = $newTime['email'];
        $arr[] = $newTime['date'];
        $arr[] = $newTime['start_time'];
        $arr[] = $newTime['end_time'];
        $arr[] = $newTime['break_time'];
        $arr[] = $newTime['actual_time'];
        $arr[] = $newTime['over_time'];

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
        return $result;
        }catch(\Exception $e){
            // echo $e;
            return $result;
        }

    
    }




    /**
     * worksテーブルのデータを取得(コメント)
     * @param array $ecData
     * @return bool $user
     */

    public static function ecData($ecData,$ecDate)
    {
        $sql = 'SELECT works.date
                      ,works.comment
                  FROM works
                 WHERE works.email = ? AND works.date = ?  
                ';
        
        // 配列に入れる
        $arr = [];
        $arr[] = $ecData;
        $arr[] = $ecDate;

        try {
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            //sqlの結果を返す
            $user = $stmt->fetch();
            return $user;
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     *編集した新しいコメント入力(アップデート登録)
     * @param array $updateComment
     * @return bool $result
     */
    public static function updateComment($updateComment)
    {
        $result = false;
            

        $sql = 'UPDATE works
                    SET works.comment = ?
                WHERE works.email = ? AND works.date = ?
            ';

        //　配列に加える
        $arr = [];
        $arr[] = $updateComment['comment'];
        $arr[] = $updateComment['email'];
        $arr[] = $updateComment['date'];


        try {
        $stmt = connect()->prepare($sql);
        $result = $stmt->execute($arr);        
        return $result;
        }catch(\Exception $e){
        return $result;
        }



    }

    /**
     *新規のコメントを入力する
     * @param array $newComment
     * @return bool $result
     */
    public static function newComment($newComment)
    {
        $result = false;

        $sql = 'INSERT INTO works(email,date,comment) VALUES (?,?,?)';

        // 配列に加える
        $arr = [];
        $arr[] = $newComment['email'];
        $arr[] = $newComment['date'];
        $arr[] = $newComment['comment'];

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
        return $result;
        }catch(\Exception $e){
            
            return $result;
        }

    
    }

    // oparation.phpの実装-------------------------------------------
    /**
     * worksテーブルのデータを取得(月別ごと)
     * @param array $monthData,$monthDate
     * @return bool $result
     */

    public static function getMonthData($monthData,$monthDate)
    {
        $sql = "SELECT works.date
                      ,works.start_time
                      ,works.end_time
                      ,works.break_time
                      ,works.actual_time
                      ,works.over_time
                      ,works.comment
                  FROM works
                 WHERE works.email = ? AND DATE_FORMAT(date,'%Y-%m') = ?
                ";
        
        // 配列に入れる
        $arr = [];
        $arr[] = $monthData;
        $arr[] = $monthDate;

        try{
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            //   SQLの結果を返す
            $user = $stmt->fetchall(PDO::FETCH_UNIQUE);
            return $user;
        } catch(\Exception $e){
            echo $e;
            return false;
        }

    
    }

    








    /**
     * ログアウト処理
     */

    public static function logout()
    {
        $_SESSION = array();
        session_destroy();
    }





}

?>