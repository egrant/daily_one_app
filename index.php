<?php
    //メンバー設定
    $member = array('藤岡さん','崇さん','堀江さん','齋藤さん','田村さん','南さん','片岡さん','野中');
    $i = 0;
 ?>

<!DOCTYPE html>
<htmllang = "ja">
    <head>
        <meta charset = "UTF-8">
        <title>行動指針の振り返り</title>
    </head>
    <body>

        <h1>ランダム指名</h1>
        <h3>メンバーチェックリスト</h3>
        <form method="post" action="result.php">
            <?php  foreach($member as $data):?>
                  <input type="checkbox" name="list<?= $i; ?>" value="<?= $i; ?>" checked>
                  <!--メンバーを表示するチェックボックス -->
                  <?php $i++?>
                <?= $data;?>
                <br>
            <?php  endforeach;?>
            <br>
            <select name="num">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
                <option>7</option>
                <option>8</option>
            </select>発表人数
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
            <br>
            <input type="submit" name="rand" value="更新" />          <!--実行-->
        <br>
        <!-- <button type = "button" name = "start" value="start">スタート</button> -->
        </form>
    </body>
</html>
