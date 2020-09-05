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

        mysqli_set_charset($conn,"utf8");

        return $conn;
    }

    function get_saysound_old_list($type, $startpos, $count){

        $conn = connect_db();

        $stmt = $conn->prepare("SELECT date, number, name, url
                                FROM ss_list
                                WHERE type IN(?, 'both') AND date !=?
                                ORDER BY rand()
                                LIMIT ?");


        $maxdate = get_maxdate();

        $stmt->bind_param('sii', $type, $maxdate, $count);

        $result = array();

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($date, $number, $name, $url);
        
            while($stmt->fetch()) {
                if($startpos == 1){
                    $index_string = "\t\t\""."file"."\"";
                    $name_string = "\t\t\""."name"."\"";
                    $url_string = "\t\t\""."url"."\"";
                }else{
                    $index_string = "\t\t\""."file".$startpos."\"";
                    $index_name_string = "\t\t\""."name".$startpos."\"";
                    $index_url_string = "\t\t\""."url".$startpos."\"";
                }

                if(strlen($number) == 1){
                    $number = "00".$number;
                }
                else if(strlen($number) == 2)
                {
                    $number = "0".$number;
                }

                $path_string = "\""."ashita"."/"."320"."/".$date."/".$number.".mp3"."\"";

                if(empty($name)){
                    $name_string = "\"".$type.$startpos."\"";
                }else{
                    $name_string = "\"".$name."\"";
                }

                if(empty($url)){
                    $url_string = "\"".$type.$startpos."\"";
                }else{
                    $url_string = "\"".$url."\"";
                }
                
                $string = $index_string." ".$path_string.PHP_EOL;
                array_push($result, $string);

                $string1 = $index_name_string." ".$name_string.PHP_EOL;
                array_push($result, $string1);

                $string2 = $index_url_string." ".$url_string.PHP_EOL;
                array_push($result, $string2);

                $startpos++;
                
            }
            $stmt->close();
        }
        
        $conn->close();

        return $result;

    }

    function get_saysound_new_list($type){

        $conn = connect_db();

        $stmt = $conn->prepare("SELECT date, number, name, url
                                FROM ss_list
                                WHERE type IN(?, 'both') AND date = ?
                                ORDER BY rand()");

        $maxdate = get_maxdate();

        $stmt->bind_param('si', $type, $maxdate);

        $result = array();
        $index = 1;

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($date, $number, $name, $url);
        
            while($stmt->fetch()) {
                if($index == 1){
                    $index_string = "\t\t\""."file"."\"";
                    $index_name_string = "\t\t\""."name"."\"";
                    $index_url_string = "\t\t\""."url"."\"";
                }else{
                    $index_string = "\t\t\""."file".$index."\"";
                    $index_name_string = "\t\t\""."name".$index."\"";
                    $index_url_string = "\t\t\""."url".$index."\"";
                }

                if(strlen($number) == 1){
                    $number = "00".$number;
                }
                else if(strlen($number) == 2)
                {
                    $number = "0".$number;
                }

                $path_string = "\""."ashita"."/"."320"."/".$date."/".$number.".mp3"."\"";

                if(empty($name)){
                    $name_string = "\"".$type.$index."\"";
                }else{
                    $name_string = "\"".$name."\"";
                }

                if(empty($url)){
                    $url_string = "\"".$type.$index."\"";
                }else{
                    $url_string = "\"".$name."\"";
                }

                $string = $index_string." ".$path_string.PHP_EOL;
                array_push($result, $string);

                $string1 = $index_name_string." ".$name_string.PHP_EOL;
                array_push($result, $string1);

                $string2 = $index_url_string." ".$url_string.PHP_EOL;
                array_push($result, $string2);

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
    $song = 50;

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
    $saysound_string .= "\t\t\"action\""." "."\"round\"".PHP_EOL;
    $saysound_string .= "\t\t\"param\""." "."\"lost\"".PHP_EOL;
    $saysound_string .= "\t\t\"volume\""." "."\"1.0\"".PHP_EOL;
    $saysound_string .= "\t\t\"admin\""." "."\"0\"".PHP_EOL;

    $saysound_string .= "\t}".PHP_EOL;
    $saysound_string .= "}".PHP_EOL;

    echo $saysound_string;

    $myfile = fopen("saysound.cfg", "w") or die("Unable to open file!");
    fwrite($myfile, $saysound_string);
    fclose($myfile);

?>