<?php
require_once( 'config.php' );
require_once( 'member_list.php' );

    //行動指針の取得
    $act_guideline_id = trim( $_POST["act_num"] );

    //DBを読み込む
    try {
        // connect
        $db = new PDO( PDO_DSN, DB_USERNAME , DB_PASSWORD );
        $db->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );

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
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }

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

    //人数の入力ミスによるエラーの処理
    $speak_class->error_check( $checked_member_num , $count_speak_today );

    $speak_possible_member_num  = count( $speak_possible_member );

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
        for( $i = 0; $i < $count_speak_today ; $i++ ) {
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
        for( $i = 0; $i < $count_speak_today; $i++ ) {
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
