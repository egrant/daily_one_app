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

        <h1>ランダム指名</h1>

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
