<?php
    define('DB_DATABASE','speak_app');
    define('DB_USERNAME','nonaka');
    define('DB_PASSWORD','egact');
    define('PDO_DSN','mysql:dbhost=localhost;dbname=' . DB_DATABASE);

    //社員数分ループを回して
    class member_list {
        //出席人数を数える
        public $checked_member_num = 0;
        public $member;
        //チェックリストからメンバーを取得
        public function member_election($all_member_num,$checked_member){
            $absence_num = 0;
            for($i = 0; $i < $all_member_num;$i++){
                //出欠席チェックリストを代入
                $member_list = trim($_POST["list$i"]);

                if($member_list == ""){
                    //欠席メンバーを$memberから削除、ただし欠席人数分をマイナスしないとずれていくのので　-$absence_numとしている
                    array_splice($checked_member, $i - $absence_num, 1);
                    $absence_num++;
                }
            }
            $this->checked_member_num = $all_member_num - $absence_num;
            return $checked_member;
        }
        //チェックリストでチェックされた人数を保存
        public function get_checked_member_num () {
            return $this->checked_member_num;
        }
        //フォームから送られた発表人数を取得
        public function speak_num(){
            return trim($_POST["num"]);
        }
        //今回話すことができるメンバー
        public function person_speak_possible($last_time_speak_num,$yesterday_speak_member_id,$checked_member){
            for($i = 0; $i < $last_time_speak_num;$i++) {
                $erase_num = $yesterday_speak_member_id[$i]["id"] -1 -$i;
                array_splice($checked_member, $erase_num, 1);
            }
            //print_r($yesterday_speak_member_id);
            return $checked_member;
        }

        //エラー処理
        public function  error_check($checked_member_num,$count_speak_today){
            if($checked_member_num < $count_speak_today){
                header("Location:error.php");
                exit;
            }
        }
        //発表者を選出
        public function designation($count_speak_today,$speak_possible_member_num){
            for($i = 0; $i < $count_speak_today ; $i++){
                $rand_presenter_num = mt_rand(0, $speak_possible_member_num -$i);
            }
            return $rand_presenter_num;
        }
    }

    //行動指針の取得
    $act_guideline_id = trim($_POST["act_num"]);

    //DBを読み込む
    try {
        // connect
        $db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //memversテーブルからメンバー名を取得する
        $stmt = $db->query("select * from members");
        $acquisition_member = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($acquisition_member as $data) {
            $all_member_name[] = $data["name"];
        }

        //membersテーブルから最後に話したメンバーを取得する
        $stmt = $db->query("select id,name from members where last_speak =(select max(last_speak) from members)");
        $yesterday_speak_member_id = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //membersテーブルから最後に話したメンバーを取得する
        $stmt = $db->query("select sum_speak from  members");
        $sum_speak = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //$yesterday_speak_member_id = $acquisition_member;

        //データベースの接続を切る
        //$db = null;

    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }
    //echo $all_member_name[0];
    //var_dump($yesterday_speak_member_id);

    $all_member_num = count($all_member_name);
    //インスタンス化
    $speak_class = new member_list;

    //出席しているメンバーを取得
    $checked_member = $speak_class->member_election($all_member_num,$all_member_name);
    //チェックリストでチェックされえている人数を取得
    $checked_member_num = $speak_class->get_checked_member_num();

    //本日話す人数を取得
    $count_speak_today = $speak_class->speak_num();

    //最後に話したメンバーのidを取得する
    $last_time_speak_num = count($yesterday_speak_member_id);
    //前回話していないメンバーを取得する
    $speak_possible_member = $speak_class->person_speak_possible($last_time_speak_num,$yesterday_speak_member_id,$checked_member);
    //print_r($speak_possible_member);
    //今回話すことができるメンバー 前回話していないメンバー
    //$speak_possible_member = array_diff($all_member_name,$last_time_speak_naem);

    //本日話すことが可能な人数（昨日話した人は除く）

    //人数の入力ミスによるエラーの処理
    $speak_class->error_check($checked_member_num,$count_speak_today);

    $speak_possible_member_num  = count($speak_possible_member);
    //発表者の選出
    //$rand_presenter_num = $speak_class->designation($count_speak_today,$speak_possible_member_num);

    // for($i = 0; $i < $count_speak_today; $i++){
    //     echo $speak_possible_member[$rand_presenter_num];
    // }
    for($i = 0; $i < $speak_possible_member_num ; $i++) {
        $random_speak_num[] = $i;
    }
    shuffle($random_speak_num);
    //ランダムで指名する
    for($i = 0; $i < $count_speak_today; $i++) {
        echo $speak_possible_member[$random_speak_num[$i]];
        //発表者のidを取得する
        $today_speak_member_id[] = $random_speak_num[$i] + 2;
    }

    //DB、membersテーブルのsum_speakを増やす
    $sum_speak_update = $sum_speak[$rand_presenter_num]["sum_speak"] + 1;
    //現在の年月日を取得する
    $today = date("Y/m/d");

    //DBに書き込む
    try {
        // connect
        $db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //update
        for($i = 0; $i < $count_speak_today; $i++) {
            //membersテーブルを編集
            $stmt = $db->prepare("update members set sum_speak = :sum_speak,last_speak = :last_speak,act_guideline_id = :act where id = :id");
            $stmt->execute([
                ':sum_speak' => $sum_speak_update,
                ':last_speak' => $today,
                ':act' => $act_guideline_id,
                ':id' => $today_speak_member_id[$i]
            ]);
        }
        //発表者のidをきろくする
        $rand_presenter_num = $rand_presenter_num + 1;
        for($i = 0; $i < $count_speak_today; $i++) {
            //speak_hitotorysテーブルに追加
            $stmt = $db->prepare("insert into speak_historys(date,member_id,act_guideline_id) values(?,?,?)");
            $stmt->execute([$today,$today_speak_member_id[$i],$act_guideline_id]);
        }
        //データベースの接続を切る
        $db = null;

    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }

//     $a = new Robot;
//     $a->setName('ロボ太郎');
//     $b = new Robot;
//     $b->setName('ロボ次郎');
//
//     echo $a->getName(); // ロボ太郎
//     echo $b->getName(); // ロボ次郎
//     $tom = new User("Tom");
//     $bob = new User("Bob");
//     echo $tom->name; // Tom
// $bob->sayHi(); // hi, i am Bob!]

 ?>

 <!DOCTYPE html>
 <htmllang = "ja">
     <head>
         <meta charset = "UTF-8">
         <title>行動指針の振り返り</title>
     </head>
     <body>
         <br>
            <a href="index.php">戻る</a>
        </body>
    </html>
