<?php
    function connect_db(){

        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db = 'saysound';

        $conn = mysqli_connect($host, $user, $pass , $db);

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

    function get_saysound_list($type){

        $conn = connect_db();

        $stmt = $conn->prepare("SELECT date, number
                                FROM ss_list
                                WHERE type in(?, 'both')
                                ORDER BY rand()
                                LIMIT 30");

        $stmt->bind_param('s', $type);

        $result = array();
        $index = 1;

        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($date,$number);
        
            while($stmt->fetch()) {
                if($index == 1){
                    $index_string = "\""."file"."\"";
                }else{
                    $index_string = "\""."file".$index."\"";
                }
               
                $path_string = "\""."ashita"."/"."320"."/".$date."/".$number.".mp3"."\"";

                $string = $index_string." ".$path_string;

                array_push($result,$string);
                $index++;
                
            }
            $stmt->close();
        }
        
        $conn->close();

        return $result;

    }

    var_dump(get_saysound_list('win'));

?>