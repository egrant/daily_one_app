<?php
    require_once( 'config.php' );

    try {
        // connect
        $db = new PDO( PDO_DSN, DB_USERNAME , DB_PASSWORD );
        $db->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );

        //メンバーを取得する
        $stmt = $db->query("select name from members");
        $member = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        echo $e->getMessage();
        exit;
    }
 ?>

<html>
<!DOCTYPE html>
<htmllang = "ja">
    <head>
        <meta charset = "UTF-8">
        <title>edit</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <form method="get">
            <h3>設定</h3>
            <div>
                <p>メンバーを追加</p>
                <br>

                <input type="text" name="name" size "30">
                <input type="submit" name="add" value="追加" />
                <br>

                <?php
                    //追加ボタンが押された
                    if(isset($_GET["add"])) {

                        //追加された名前を取得する
                        $name = trim($_GET["name"]);
                        echo $name . "を追加しました";

                        //DBを読み込む
                        try {
                            // connect
                            $db = new PDO( PDO_DSN, DB_USERNAME , DB_PASSWORD );
                            $db->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );

                            //メンバーのidの最大値を取得する
                            $stmt = $db->query("select max(id) as id from members");
                            $max_id = $stmt->fetch(PDO::FETCH_ASSOC);

                            //追加するメンバーのidにするため1をたす
                            $max_id[id]++;
                            //新しいメンバーをmembersテーブルに追加する
                            $stmt = $db->prepare("insert into members (id,name) values(?,?)");
                            $stmt->execute([$max_id[id],$name]);

                        } catch (PDOException $e) {
                            echo $e->getMessage();
                            exit;
                        }
                    }
                ?>
            </div>

            <div>
                <p>メンバーを削除</p>
                <br>
                <?php  foreach($member as $key => $data):?>
                    <input type="checkbox" name="delete_list<?= $key; ?>" value="<?= $key; ?>">
                    <!--メンバーを表示するチェックボックス -->
                    <?= $data[name];?>
                    <br>
            <?php  endforeach;?>

            <br>
            <input type="submit" name="delete" value="削除" />
            <br>

            <?php
                //追加ボタンが押された
                if(isset($_GET["delete"])) {

                    $member_count = count($member);
                    //削除された人数を取得する
                    $delete_count = 0;
                    for( $i = 0; $i < $member_count ; $i++ ) {
                        $num = trim($_GET["delete_list$i"]);
                        if(!$num == "") {
                            //
                            echo  $num . "を削除しました";
                            //削除するメンバーのid
                            $delete_num[] = $num + 1;
                            $delete_count++;
                        }
                    }

                    //DBを読み込む
                    try {
                        $db = new PDO( PDO_DSN, DB_USERNAME , DB_PASSWORD );
                        $db->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );

                        for($i = 0; $i < $delete_count; $i++) {
                            //複数人削除の為マイナスしていく
                            $delete_num[$i] = $delete_num[$i] - $i;
                            //メンバーを削除する
                            $stmt = $db->prepare("delete from members where id = ?");
                            $stmt->execute([$delete_num[$i]]);

                            //メンバーのidの最大値を取得する
                            $stmt = $db->query("select max(id) as id from members");
                            $max_id = $stmt->fetch(PDO::FETCH_ASSOC);

                            $minus_num = $max_id[id] - $delete_num[$i];
                            for($j = 0; $j < $minus_num; $j++) {
                                //削除したメンバー以降のidを-1にして調整する
                                $stmt = $db->prepare("update members set id = :id where id = :check");
                                $stmt->execute([
                                    ':id' => $delete_num[$i] + $j,
                                    ':check' =>  $delete_num[$i] + 1 + $j
                                ]);
                            }
                        }

                    } catch (PDOException $e) {
                        echo $e->getMessage();
                        exit;
                    }
                }
            ?>

            </div>

            <div id="bottom">
                <a href="index.php">入力画面に戻る</a>
            </div>
        </form>
   </body>
</html>


<?php



?>
