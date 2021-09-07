<?php
    #Will make a grid with $x being the x-axis and $y being the y axis
    function GenerateGrid($x,$y,$classes) {
        #passable x & y vals:
            #0 course code          #3 expression               #6 teacher surname           
            #1 section              #4 term                     #7 department
            #2 course name          #5 teacher full name        #8 room

        $xVals = [];
        $yVals = [];

        #create a set of axes from all possible values of given parameters
        foreach ($classes as $k => $child) {
            array_push($xVals, $child[$x]);
            
            #handle teacher: surname, given name
            if ($y == 6) {
                array_push($yVals, $child[$y] . ", " . $child[$y - 1]);
            } else {
                array_push($yVals, $child[$y]);
            }
            
        }

        #remove duplicates and sort
            #(axes only. does not remove duplicates from actual grid)
        $xVals = array_unique($xVals);
        $yVals = array_unique($yVals);
        sort($xVals);
        sort($yVals);

        #initialize a grid full of empty arrays
        $grid = array_fill(0,count($yVals),array_fill(0,count($xVals),array()));

        #loop through master class array and push values to appropriate positions in grid
        foreach ($classes as $k => $child) {
            $xPos = array_search($child[$x],$xVals);
            
            #handle teacher: surname, given name
            if ($y == 6) {
                $yPos = array_search($child[$y] . ", " . $child[$y - 1],$yVals);
            } else {
                $yPos = array_search($child[$y],$yVals);
            }

            array_push($grid[$yPos][$xPos],$child);
        }

        #return an array of three arrays
            #grid is 4d --> grid(rows(cells(classData()))
            #xvals and yvals are 1d
        return array($grid, $xVals, $yVals);
    }

    function checkPeriodConflicts($class1, $class2){
        $classes = ['arr1' => $class1, 'arr2' => $class2];

        foreach ($classes as $k => $period) {
            switch ($period) {
                case '1(A) 2(B)':
                    $$k = [1];
                    break;
                case '1(B) 2(A)':
                    $$k = [2];
                    break;
                case '1-2(A-B)':
                    $$k = [1,2];
                    break;
                case '1-4(A-B)':
                    $$k = [1,2,3,4];
                    break;
                case '3(A) 4(B)':
                    $$k = [3];
                    break;
                case '3(B) 4(A)':
                    $$k = [4];
                    break;
                case '1-2(A-B)':
                    $$k = [3,4];
                    break;
                case '5(A-B)':
                    $$k = 5;
            }
        }

        $confs = array_intersect($arr1, $arr2);
        if (!empty($confs)) {
            return 1;
        } else {
            return 0;
        }
    }

    function getErrors($classes){
        #Test if teacher is in two different rooms in same period
        $Errors = [];
        foreach($classes as $key1 => $class1){
            foreach(array_slice($classes,$key1+1) as $key2 => $class2){
                #If in same period and same term and same fname and same sname and different rooms
                if (checkPeriodConflicts($class1,$class2) && $class1[4] == $class2[4] && $class1[5] == $class2[5] && $class1[6] == $class2[6] && $class1[8] != $class2[8] && $class1[8] != "" && $class2[8] != ""){
                    array_push($Errors, [count($Errors), $class1[11], $class2[11], "SPLIT_TEACHER", 0]);
                }
            }
        }
        return $Errors;
    }

    function getTeacherClassNums(){
        $TeacherClassNums = [];
        foreach($_SESSION['classList'] as $class){
            $name = $class[6] . ", " . $class[5];
            if(isset($TeacherClassNums[$name])){
                $TeacherClassNums[$name]++;
            } else {
                $TeacherClassNums[$name]=1;
            }
        }
        return $TeacherClassNums;
    }

    function getDepartmentClassNums(){
        $DepartmentClassNums = [];
        foreach($_SESSION['classList'] as $class){
            $name = $class[7];
            if($name == ""){
                $name = "MISC";
            }
            if(isset($DepartmentClassNums[$name])){
                $DepartmentClassNums[$name]++;
            } else {
                $DepartmentClassNums[$name]=1;
            }
        }
        return $DepartmentClassNums;
    }
    #print_r(getTeacherClassNums());
?>
