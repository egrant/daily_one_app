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

        //前回話した人数を取得する
        $stmt = $db->query(
            "select count from speak_histories where id = (
                select max(id) from speak_histories)");
        $yesterday_speak_num = $stmt->fetch(PDO::FETCH_ASSOC);

        $temp = $yesterday_speak_num[count];
        //前回話した人のidと名前を取得
        $stmt = $db->query(
            "select id,name from members order by last_speak desc limit $temp");
        $yesterday_speak_member_id = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //membersテーブルから話した回数を取得する
        $stmt = $db->query("select sum_speak from  members");
        $sum_speak = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }

    //メンバー全員の人数
    $all_member_num = count($all_member_name);

    //インスタンス化
    $speak_class = new member_list;

    //出席しているメンバーを取得
    $checked_member = $speak_class->member_election($all_member_num,$all_member_name);

    //チェックリストでチェックされえている(出席している)人数を取得
    // $checked_member_num = $speak_class->get_checked_member_num();
    $checked_member_num = count($checked_member);

    //本日話す人数を取得
    $count_speak_today = trim( $_POST["num"] );

    //前回話したメンバーの人数を取得する
    $last_time_speak_num = count($yesterday_speak_member_id);

    //前回話したメンバーで今回出席している人数
    $member_count_attendance = $speak_class->member_count_attendance_Checked(
        $last_time_speak_num,$yesterday_speak_member_id,$checked_member);
    //今回出席しているメンバーが前回話した人しかいないとき前回話した人も話すことにする
    if($member_count_attendance == $count_speak_today) {
        $speak_possible_member = $checked_member;
    }

    //総人数から前回話した人数を引いた数が今回話す予定の人数より少ないときは前回話した人も話すことにする
    if($all_member_num - $last_time_speak_num < $count_speak_today) {
        $speak_possible_member = $checked_member;
    } else if ($checked_member_num - $member_count_attendance < $count_speak_today) {
        //今回出席している人数から前回話して今回も出席している人数を引いた数が
        //今回話す人数より少ないとき前回話した人も話すことにする
        $speak_possible_member = $checked_member;
    }
    else {
        //今回話すことができるメンバーを取得する
        $speak_possible_member = $speak_class->person_speak_possible(
        $last_time_speak_num,$yesterday_speak_member_id,$checked_member);
    }

    //今回話すことができるメンバーの人数
    $speak_possible_member_num  = count($speak_possible_member);

    //人数の入力ミスによるエラーの処理
    $speak_class->error_check( $checked_member_num , $count_speak_today );

    for($i = 0; $i < $speak_possible_member_num ; $i++) {
        //話すことができるメンバーの人数分数値を配列に入れる
        $random_num[] = $i;
    }

    $speak_class->mt_shuffle($random_num);
    //shuffle($random_num);
    //ランダムで指名する
    for($i = 0; $i < $count_speak_today; $i++) {
        // echo $speak_possible_member[$random_num[$i]];
        $display_member[$i] = $speak_possible_member[$random_num[$i]];

        //発表者の名前を取得する
        $today_speak_name[$i] = $speak_possible_member[$random_num[$i]];

        //発表者のidを取得する
        //$today_speak_member_id[$i] = $random_num[$i] + 2;
    }

    //DB、membersテーブルのsum_speakを増やす
    $sum_speak_update = $sum_speak[$rand_presenter_num]["sum_speak"] + 1;

    $today = date("Y/m/d/H/i/s");

    //DBに書き込む
    try {
        // connect
        $db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //今回話したメンバーのidを取得する
        for($i = 0 ; $i < $count_speak_today ; $i++) {
            $stmt = $db->prepare("select id from members where name = ?");
            $stmt->execute([$today_speak_name[$i]]);
            $speak_id[$i] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        //membersテーブルを編集する、最後に話した日にち、話した総数、話した行動指針内容
        for( $i = 0; $i < $count_speak_today ; $i++ ) {
            $stmt = $db->prepare(
                "update members set last_speak = :last_speak,act_guideline_id = :act where id = :id");
            $stmt->execute([
                ':last_speak' => $today,
                ':act' => $act_guideline_id,
                ':id' => $speak_id[$i][id]
            ]);
        }

        //行動指針を取得する
        $stmt = $db->query(
            "select content from act_guidelines where id = $act_guideline_id");
        $act_guideline_content = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //今回話した人数と行動指針のidをspeak_historiesテーブルに追加する
        $stmt = $db->prepare(
             "insert into speak_histories(member_id,act_guideline_id,count) values(?,?,?)");
        $stmt->execute([
            $speak_id[0][id],
            $act_guideline_id,
            $count_speak_today
        ]);

        // $stmt = $db->prepare(
        //     "insert into speak_histories(count) values(?)");
        // $stmt->execute(
        //     [$count_speak_today]
        // );

         //次回の行動指針の内容をspeak_historiesテーブルにに記載する

        //データベースの接続を切る
        $db = null;

    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }
 ?>

<html>
    <!DOCTYPE html>
    <htmllang = "ja">
     <head>
         <meta charset = "UTF-8">
         <link rel="stylesheet" href="style.css">
         <title>行動指針の振り返り</title>
     </head>
     <body>
         <h1 id="center1"><?= $act_guideline_content[0][content];?></h1>

         <h2 id="center">発表者</h2>
         <?php foreach($display_member as $member): ?>

             <h3 id="member"><?= $member?></h3>

         <?php endforeach; ?>
         <br>
            <a href="index.php">戻る</a>
        </body>
</html>
