<?php
    function connect_db(){

        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db = 'saysound';

        $conn = mysqli_connect($host, $user, $pass, $db);

        if (!$conn) {
            //echo "Error: Unable to connect to MySQL." . PHP_EOL;
            //echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
            //echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
            exit;
        }

        //echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
        //echo "Host information: " . mysqli_get_host_info($conn) . PHP_EOL;

        return $conn;
    }

    function get_saysound_old_list($type, $startpos, $count){

        $conn = connect_db();

        $stmt = $conn->prepare("SELECT date, number
                                FROM ss_list
                                WHERE type IN(?, 'both')
                                ORDER BY rand()
                                LIMIT ?");

        $stmt->bind_param('si', $type, $count);

        $result = array();

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($date, $number);
        
            while($stmt->fetch()) {
                if($startpos == 1){
                    $index_string = "\t\t\""."file"."\"";
                }else{
                    $index_string = "\t\t\""."file".$startpos."\"";
                }

                if(strlen($number) == 1){
                    $number = "00".$number;
                }
                else if(strlen($number) == 2)
                {
                    $number = "0".$number;
                }

                $path_string = "\""."ashita"."/"."320"."/".$date."/".$number.".mp3"."\"";

                $string = $index_string." ".$path_string.PHP_EOL;

                array_push($result, $string);
                $startpos++;
                
            }
            $stmt->close();
        }
        
        $conn->close();

        return $result;

    }

    function get_saysound_new_list($type){

        $conn = connect_db();

        $stmt = $conn->prepare("SELECT date, number
                                FROM ss_list
                                WHERE type IN(?, 'both') AND date = ?
                                ORDER BY rand()");

        $maxdate = get_maxdate();

        $stmt->bind_param('si', $type, $maxdate);

        $result = array();
        $index = 1;

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($date, $number);
        
            while($stmt->fetch()) {
                if($index == 1){
                    $index_string = "\t\t\""."file"."\"";
                }else{
                    $index_string = "\t\t\""."file".$index."\"";
                }

                if(strlen($number) == 1){
                    $number = "00".$number;
                }
                else if(strlen($number) == 2)
                {
                    $number = "0".$number;
                }

                $path_string = "\""."ashita"."/"."320"."/".$date."/".$number.".mp3"."\"";

                $string = $index_string." ".$path_string.PHP_EOL;

                array_push($result, $string);
                $index++;
                
            }
            $stmt->close();
        }
        
        $conn->close();

        return $result;

    }

    function get_maxdate(){

        $conn = connect_db();
        $stmt = $conn->prepare("SELECT MAX(date)
                                FROM ss_list
                                WHERE 1=1");

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($maxdate);
            while($stmt->fetch()) {
               $result = $maxdate;
            }
            $stmt->close();
        }
        
        $conn->close();

        return $result;

    }

    function get_new_count($type){

        $conn = connect_db();

        $maxdate = get_maxdate();

        $stmt = $conn->prepare("SELECT count(date)
                                FROM ss_list
                                WHERE type IN(?, 'both') AND date = ?");

        $stmt->bind_param('si', $type, $maxdate);

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($count);
            while($stmt->fetch()) {
               $result = $count;
            }
            $stmt->close();
        }
        
        $conn->close();

        return $result;

    }

    //echo var_dump(get_maxdate());
    $song = 40;

    $new_win_song = get_new_count('win');
    $new_lose_song = get_new_count('lose');
    
    $left_win_song = $song - $new_win_song;
    $left_lose_song = $song - $new_lose_song;
    
    $new_win_list = get_saysound_new_list('win');
    $old_win_list = get_saysound_old_list('win', $new_win_song + 1, $left_win_song);

    $win_list = array_merge($new_win_list,$old_win_list);

    //var_dump($win_list);

    $new_lose_list = get_saysound_new_list('lose');
    $old_lose_list = get_saysound_old_list('lose', $new_lose_song + 1, $left_lose_song);

    $lose_list = array_merge($new_lose_list,$old_lose_list);

    //var_dump($lose_list);

    $saysound_string = "\"Sound Combinations\"".PHP_EOL;
    $saysound_string .= "{".PHP_EOL;
    $saysound_string .= "\t\"Win\"".PHP_EOL;
    $saysound_string .= "\t{".PHP_EOL;
    $saysound_string .= PHP_EOL;

    foreach ($win_list as $value) {
        $saysound_string .= $value;
    }

    $saysound_string .= PHP_EOL;
    $saysound_string .= "\t\t\"count\""." "."\"".$song."\"".PHP_EOL;
    $saysound_string .= "\t\t\"actiononly\""." "."\"1\"".PHP_EOL;
    $saysound_string .= "\t\t\"action\""." "."\"round\"".PHP_EOL;
    $saysound_string .= "\t\t\"param\""." "."\"won\"".PHP_EOL;
    $saysound_string .= "\t\t\"volume\""." "."\"1.0\"".PHP_EOL;
    $saysound_string .= "\t\t\"admin\""." "."\"0\"".PHP_EOL;
    $saysound_string .= "\t}".PHP_EOL;
    $saysound_string .= "\t\"Lose\"".PHP_EOL;
    $saysound_string .= "\t{".PHP_EOL;
    $saysound_string .= PHP_EOL;

    foreach($lose_list as $value) {
        $saysound_string .= $value;
    }

    $saysound_string .= PHP_EOL;
    $saysound_string .= "\t\t\"count\""." "."\"".$song."\"".PHP_EOL;
    $saysound_string .= "\t\t\"actiononly\""." "."\"1\"".PHP_EOL;
    $saysound_string .= "\t\t\"action\""." "."\"lost\"".PHP_EOL;
    $saysound_string .= "\t\t\"param\""." "."\"won\"".PHP_EOL;
    $saysound_string .= "\t\t\"volume\""." "."\"1.0\"".PHP_EOL;
    $saysound_string .= "\t\t\"admin\""." "."\"0\"".PHP_EOL;

    $saysound_string .= "\t}".PHP_EOL;
    $saysound_string .= "}".PHP_EOL;

    echo $saysound_string;

    
?>