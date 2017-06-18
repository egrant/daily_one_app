<?php
    require_once( 'config.php' );

    try {
        // connect
        $db = new PDO( PDO_DSN, DB_USERNAME , DB_PASSWORD );
        $db->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );

        //メンバーを取得する
        $stmt = $db->query("select name from members");
        $member = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //前回決めた行動指針idを取得する
        $stmt = $db->query(
            "select act_guideline_id from speak_histories order by id desc limit 1");
        $act_guideline_id = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $act_id = $act_guideline_id[0][act_guideline_id];

        //前回決めた行動指針の内容を取得する
        $stmt = $db->query(
            "select content from act_guidelines where id = $act_id");
        $act_guideline_content = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //前回決めたメンバーの人数を取得する
        $stmt = $db->query(
            "select count from speak_histories order by id desc limit 1");
        $temp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $speak_member_count = $temp[0][count];

        //前回決めたメンバーを取得する
        $stmt = $db->query(
            "select name from members order by last_speak desc limit $speak_member_count");
        $speak_member_name = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        echo $e->getMessage();
        exit;
    }
    
    $member_count = count($member);

    $i = 0;
 ?>
<html>
    <!DOCTYPE html>
    <htmllang = "ja">
    <head>
        <link rel="stylesheet" href="style.css">
        <meta charset = "UTF-8">
        <title>行動指針の振り返り</title>
    </head>

    <body>
        <form method="post" action="result.php">

        <h1 id="center">デイリーワン振り返り</h1>

        <h2 id="center1">行動指針と発表者<h2>

        <h3 id="center1"><?= $act_guideline_content[0][content]?><h3>

        <?php foreach($speak_member_name as $name):;?>
        <h3 id="center1"><?=$name[name];?><h3>

        <?php endforeach;?>

        <div id="check">
            <h3>メンバーチェックリスト</h3>
                <?php  foreach($member as $data):?>
                    <input type="checkbox" name="list<?= $i; ?>" value="<?= $i; ?>" checked>
                    <!--メンバーを表示するチェックボックス -->
                    <?php $i++?>
                    <?= $data[name];?>
                    <br>
            <?php  endforeach;?>
        </div>

        <div id="num">
            <br>
            <select name="num">
                <?php  foreach($member as $key => $data):?>
                <?php if($key == 0) {continue;}?>
                <option><?=$key?></option>
                <?php  endforeach;?>
            </select>発表人数
        </div>

        <div id="act">
            <br>
            <select name="act_num">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
                <option>7</option>
                <option>8</option>
                <option>9</option>
                <option>10</option>
            </select>行動指針
        </div>

        <div>
            <br>
            <input type="submit" name="rand" value="GO!" />
        </div>

        <div>
            <br>
            <a href="edit.php">設定</a>
        </div>

        </form>
    </body>
</html>
