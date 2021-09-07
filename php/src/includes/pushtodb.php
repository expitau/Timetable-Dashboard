<?php

    #establish a connection to database
    require_once("dbvars.php");
    $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("failed to connect to database");
    
    #truncate queries
    $fkc0 = "SET FOREIGN_KEY_CHECKS = 0";
    $truncTe = "TRUNCATE `teachers`";
    $truncCl = "TRUNCATE `classes`";
    $truncCo = "TRUNCATE `courses`";
    $fkc1 = "SET FOREIGN_KEY_CHECKS = 1";
    
    #execute
    mysqli_query($conn,$fkc0) or die("failed fkc0");
    mysqli_query($conn,$truncCo) or die("failed truncate courses");
    mysqli_query($conn,$truncTe) or die("failed truncate teachers");
    mysqli_query($conn,$truncCl) or die("failed truncate classes");
    mysqli_query($conn,$fkc1) or die("failed fkc1");

    #initialize insert queries for each table
    $courseQ = "INSERT INTO `courses`(`course_id`, `code`, `section`, `name`, `teacher_id`) VALUES ";
    $teacherQ = "INSERT INTO `teachers`(`teacher_id`, `fname`, `sname`, `dept`) VALUES ";
    $classQ = "INSERT INTO `classes`(`class_id`, `course_id`, `teacher_id`, `room`, `term`, `numstudents`, `maxseats`, `expression`) VALUES ";

    #to avoid duplicates of teachers
    $t_ids = array();

    #loop through scraped data as child arrays
    foreach ($TTData as $k => $child) {
        if(count($child)>8){
            #course info
            $course_id = $k + 1;
            $code = substr($child[0],0,6);
            $sect = substr($child[0],7);
            $course = trim(substr($child[1],8));
            
            #teacher info
            $teacher_id = array_search($child[4], array_column($TTData,4)) + 1;
            if (array_search($teacher_id, $t_ids) === FALSE) {
                array_push($t_ids, $teacher_id);
                $newTeacher = true;
            } else {
                $newTeacher = false;
            }
            $child[4] = str_replace("&#44", ",", $child[4]);
            $tNames = explode(", ",$child[4]);
            $sname = $tNames[0];
            $fname = trim($tNames[1]);
            $dept = $child[5];

            #class info
            $room = $child[6];
            $term = $child[3];
            $stu = $child[7];
            $max = $child[8];
            $expn = $child[2];

            #concatenate values to existing queries
            $courseQ .= "({$course_id},'{$code}','{$sect}','{$course}',{$teacher_id})";
            if ($newTeacher) {
                $teacherQ .= '(' . $teacher_id . ',"' . $fname . '","' . $sname . '","' . $dept. '")';
            }
            $classQ .= "(NULL,{$course_id},{$teacher_id},'{$room}','{$term}',{$stu},{$max},'{$expn}')";

            #add commas to continue insertion, if necessary
            if ($k + 1 < count($TTData)) {
                $courseQ .= ", ";
                $classQ .= ", ";
            }
            if ($newTeacher) {
                $teacherQ .= ", ";
            }
        } else {
        }
    }

    #remove comma from teachers query (there will be one after last new teacher)
    $teacherQ = rtrim($teacherQ,", ");
    #execute all 3 queries or die
    mysqli_query($conn,$courseQ) or die("failed insert into courses");
    mysqli_query($conn,$teacherQ) or die("failed insert into teachers");
    mysqli_query($conn,$classQ) or die("failed insert into classes");

    #close connection to database
    mysqli_close($conn);
?>
