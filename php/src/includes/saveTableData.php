<?php
    session_start();

    //Each entity in the table is its own form, which is sent to $_POST when submitted
    foreach($_POST as $key => $value){
        //The key is the coordinates of the form that was submitted (ie. 1-2)
        $key = explode("-",$key);

        //If the x coordinate is 0, then set the course code and period of the class (in classList) at the y coordinate specified
        if ($key[1] == 0){
            $value = explode(".", $value);
            $_SESSION["classList"][$key[0]][0] = $value[0];
            $_SESSION["classList"][$key[0]][1] = $value[1];
        //If the x coordinate is 4, set the teacher fname and sname of the class (in classList) at the y coordinate specified
        } else if ($key[1] == 4){
            $value = explode(", ", $value);
            $_SESSION["classList"][$key[0]][5] = $value[1];
            $_SESSION["classList"][$key[0]][6] = $value[0];

        //Otherwise, set the column of the class at the y coordinate specified
        } else if ($key[1] < 4){
            $_SESSION["classList"][$key[0]][$key[1]+1] = $value;
        } else {
            $_SESSION["classList"][$key[0]][$key[1]+2] = $value;
        }
    }

    //Return to index.php
    header("Location: ../index.php");

?>
