<?php
    require_once("includes/dbvars.php");
    
    $FileName = "export";
    $File = fopen($FileName . ".csv", "w") or die('unable to open file');

    $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("error connecting to db");
    $displayQ = "SELECT `courses`.`code`, `courses`.`section`,`courses`.`name`,`classes`.`expression`,`classes`.`term`,`teachers`.`fname`,`teachers`.`sname`,`teachers`.`dept`,classes.`room`,classes.`numstudents`,`classes`.`maxseats`
       FROM `classes`
       INNER JOIN `courses` 
       ON `classes`.`course_id` = `courses`.`course_id` 
       INNER JOIN `teachers` 
       ON `classes`.`teacher_id` = `teachers`.`teacher_id` 
       ORDER BY `courses`.`course_id`";
    $result = mysqli_query($conn,$displayQ) or die("Query error");
    
    $TTData = [];
    while ($row = mysqli_fetch_array($result)) {
        $child = [];
        array_push($child, $row[0] . "." . $row[1]);
        array_push($child, $row[0] . " - " . $row[2]);
        array_push($child, $row[3]);
        array_push($child, $row[4]);
        array_push($child, $row[6] . "&#44 " . $row[5]);
        array_push($child, $row[7]);
        array_push($child, $row[8]);
        array_push($child, $row[9]);
        array_push($child, $row[10]);
        array_push($TTData, $child);
    }

    foreach($TTData as $key => $row){
        $TTData[$key] = implode(", ",$TTData[$key]);
    }

    fwrite($File, implode("\n",$TTData));
    
    $newFile = "{$FileName}.csv";

    if (file_exists($newFile)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($newFile).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($newFile));
        readfile($newFile);
        exit;
    }

    mysqli_close($conn);
    fclose($File);
?>
