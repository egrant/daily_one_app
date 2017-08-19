<?php

class member_list {
    //出席人数を数える
    public $checked_member_num = 0;
    public $member;
    //チェックリストからメンバーを取得
    public function member_election($all_member_num , $all_member_name) {
        $absence_num = 0;
        for( $i = 0; $i < $all_member_num ; $i++ ) {
            //出欠席チェックリストを代入
            $member_list = trim( $_POST["list$i"] );

            if($member_list == "") {
                //欠席メンバーを$memberから削除、ただし欠席人数分をマイナスしないとずれていくのので　-$absence_numとしている
                array_splice($all_member_name , $i - $absence_num , 1);
                $absence_num++;
            }
        }
        $this->checked_member_num = $all_member_num - $absence_num;
        return $all_member_name;
    }
    //チェックリストでチェックされた人数を保存
    public function get_checked_member_num () {
        return $this->checked_member_num;
    }
    //フォームから送られた発表人数を取得
    public function speak_num(){
        return trim( $_POST["num"] );
    }

    //今回話すことができるメンバー
    public function person_speak_possible(
        $last_time_speak_num,$yesterday_speak_member_id, $checked_member) {

        for( $i = 0 ; $i < $last_time_speak_num ; $i++ ) {
            //今回出席しているメンバーの中に前回話した人がいたら配列から削除する
            if(in_array($yesterday_speak_member_id[$i]["name"], $checked_member)) {
                $erase_num = array_search($yesterday_speak_member_id[$i]["name"], $checked_member);
                array_splice($checked_member, $erase_num, 1);
            }
        }
        //print_r($yesterday_speak_member_id);
        //return $
        return $checked_member;
    }

    //今回出席しているメンバーが前回話したメンバーしかいないとき
    public function member_count_attendance_Checked(
        $last_time_speak_num,$yesterday_speak_member_id,$checked_member) {
            //
            $count = 0;
            for($i = 0; $i < $last_time_speak_num; $i++) {
                if(in_array($yesterday_speak_member_id[$i]["name"], $checked_member)) {
                    $count++;
                }
            }
            return $count;
    }

    //エラー処理
    public function  error_check($checked_member_num, $count_speak_today) {
        if( $checked_member_num < $count_speak_today ) {
            header("Location:error.php");
            exit;
        }
    }
    //発表者を選出
    public function designation( $count_speak_today , $speak_possible_member_num ) {
        for( $i = 0; $i < $count_speak_today ; $i++ ) {
            $rand_presenter_num = mt_rand( 0, $speak_possible_member_num -$i );
        }
        return $rand_presenter_num;
    }

    public function mt_shuffle(array &$array) {

        $array = array_values($array);
        $cnt = count($array);

        //配列はcntより1少ないので-1をする
        for($i = $cnt -1; $i > 0; $i--) {

            $j = mt_rand(0 , $i);
            if($i !== $j) {
                //配列の中身を入れ替える
                list($array[$i], $array[$j]) = [$array[$j], $array[$i]];
            }
        }
    }
}
