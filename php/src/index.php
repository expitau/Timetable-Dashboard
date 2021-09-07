<?php
    session_start();

    #redirect user if they have not logged in
    if (!isset($_SESSION['user_id'])) {
        header("location:login.php");
        die();
    }

    #handle file import if file has been selected
    if (isset($_FILES['TTFile']['tmp_name'])) {
        require("includes/importFile.php");
    }

    #view switcher
    if (isset($_GET['view'])) {
        $_SESSION['view'] = $_GET['view'];
    }

    #handle grid form
    if (isset($_POST['xAxis'])) {
        $_SESSION['xAxis'] = $_POST['xAxis'];
    }
    if (isset($_POST['yAxis'])) {
        $_SESSION['yAxis'] = $_POST['yAxis'];
    }

    if (isset($_POST['export'])) {
        require("includes/exportFile.php");
    }
    if (isset($_POST['save'])) {
        $TTData = [];
        foreach ($_SESSION['classList'] as $row) {
            $child = [];
            array_push($child, $row[0] . "." .  $row[1]);
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
        require("includes/pushtodb.php");
        if ($_SESSION['view'] == 'Master') {
            unset($_SESSION['classList']);
        }
    }
    if (isset($_POST['revert'])) {
        unset($_SESSION['classList']);
        $_SESSION['view'] = 'Master';
    }
    if (isset($_POST['createNew'])) {
        if (preg_match("/[A-Z0-9]{6}\.[A-Z0-9]{1,3}/",$_POST['code'])) {
            if (preg_match("/[a-zA-Z],\s[A-Z]/",$_POST['tname'])) {
                if (empty($_POST['stu'])) {
                    $_POST['stu'] = 0;
                }
                if (empty($_POST['max'])) {
                    $_POST['max'] = 30;
                }
                array_push($_SESSION['classList'], array(
                    explode(".", $_POST['code'])[0],
                    explode(".", $_POST['code'])[1],
                    $_POST['cname'],
                    $_POST['exp'],
                    $_POST['term'],
                    explode(",", $_POST['tname'])[1],
                    explode(",", $_POST['tname'])[0],
                    $_POST['dept'],
                    $_POST['room'],
                    $_POST['stu'],
                    $_POST['max'],
                    count($_SESSION['classList'])
                )); 
            } else {
                $importMsg = "Please check that teacher name is formatted correctly";
                $_POST['new'] = 1;
            }
        } else {
            $_POST['new'] = 1;
            $importMsg = "Please check that course code is formatted correctly";
        }
    }
    if (isset($_POST['gridVals'])) {
        $_POST['gridVals'] = ltrim($_POST['gridVals'],"p[]=");
        $cells = explode("&p[]=", $_POST['gridVals']);
        
        $ids = array_column($_SESSION['classList'], 11);
        switch ($_SESSION['view']) {
            case 'Grid':
                $xAx = 3;
                $yAx = 8;
                break;
            case 'Section':
                $xAx = 7;
                $yAx = 6;
        }

        foreach ($cells as $cellData) {
            $dataArr = explode("_", $cellData);
            $x = $_SESSION['xvals'][$dataArr[2] - 1];
            $y = $_SESSION['yvals'][$dataArr[1] - 1];
            $id = $dataArr[0];

            $pos = array_search($id,$ids);
            $_SESSION['classList'][$pos][$xAx] = $x;
            if ($_SESSION['view'] == 'Grid') {
                $_SESSION['classList'][$pos][$yAx] = $y;
            } else {
                $y = str_replace("&#44",",",$y);
                $tnames = explode(",", $y);
                $_SESSION['classList'][$pos][$yAx] = $tnames[0];
                $_SESSION['classList'][$pos][$yAx - 1] = $tnames[1];
            }
            
        }

        if (!empty($_POST['holdVals'])) {
            $_POST['holdVals'] = ltrim($_POST['holdVals'],"p[]=");
            $holdcells = explode("&p[]=", $_POST['holdVals']);
            $c = 0;

            foreach ($holdcells as $holdcellData) {
                $holddataArr = explode("_", $holdcellData);
                $id = $holddataArr[0];

                $pos = array_search($id,$ids);
                $_SESSION['classList'][$pos][3] = "";
                $_SESSION['classList'][$pos][8] = "";

                $c++;
                echo $c;
            }
        }
    }
    if (isset($_POST['sem'])) {
        $_SESSION['sem'] = $_POST['sem'];
    }
?>

<html lang="en" >
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Timetabling project" />
        <meta name="author" content="Graeme McDougall, Nathan D'Silva" />
        <title>Timetable UI</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
        
        <!-- table drag and drop -->
        <script type="text/javascript" src="js/redips-drag-source.js"></script>
        <link href="css/redipsStyle.css" rel="stylesheet" />

        <!-- AJAX -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        
    </head>

    <!-- top navbar -->
    <body class="sb-nav-fixed" onload="REDIPS.drag.init()" >

        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand" href="index.php">Timetable UI</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

            <!-- icon drop-down -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="editAcc.php">Edit Account</a>
                        <a class="dropdown-item" href="includes/logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- main body -->
        <div id="layoutSidenav">

            <!-- collapsible sidenav -->
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Options</div>
                            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Layouts
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="?view=Master">Master View</a>
                                    <a class="nav-link" href="?view=Grid">Grid view</a>
                                    <a class="nav-link" href="?view=Section">Section View</a>
                                </nav>
                            </div>
                            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Help
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                                    <a class="nav-link" href="settings.php">Settings</a>
                                    <a class="nav-link" href="password.php">Forgot Password</a>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <!-- footer -->
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?php echo $_SESSION['fname'] . " " .  $_SESSION['sname']; ?>
                    </div>
                </nav>
            </div>

            <!-- body content -->
            <div id="layoutSidenav_content" >
                <main >
                    <div class="container-fluid" >
                        <h1 class="mt-4">Dashboard</h1>

                        <!-- buttons along top -->
                        <div class="form-group">
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" style="display:inline">
                                <!--input type="submit" class="btn btn-primary" value="Import"></input-->
                                <span title="Import a powerscheduler html file or .csv"><input id="ImportButton" class="btn btn-primary" type="button" onclick="document.getElementById('file').click();" value="Import"></input>
                                <input type="file" name="TTFile" id="file" hidden/>
                                <input type="submit" class="btn btn-primary" name="import" id="import" hidden /></span>
                            </form>
                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" style="display:inline" >
                                <span title="Export to a .csv file"><input type="submit" class="btn btn-primary" name="export" id="export" value="Export" /></span>
                                <span title="Save changes to internal database"><input type="submit" class="btn btn-primary" name="save" id="save" value="Save" /></span>
                                <span title="Revert to previous save"><input type="submit" class="btn btn-primary" name="revert" id="revert" value="Revert Changes" /></span>
                                <?php if ($_SESSION['view'] == 'Master') { ?>
                                    <span title="Create new class"><input type="submit" class="btn btn-primary" name="new" id="new" value="New Class" /></span>
                                <?php } ?>
                            </form>

                        <!-- display results of input to user -->
                        <div style="color:red;"><?php if (isset($importMsg)) {echo $importMsg;} ?></div>

                        <div class="card mb-4" >
                            <div class="card-header">
                                <i class="fas fa-table mr-1"></i>
                                <?php 
                                    echo $_SESSION['view'] . " View";

                                    switch ($_SESSION['view']) {
                                        case 'Master':
                                            $usrMsg = "Double click any cell to edit. Hit return to save changes locally.";
                                            break;
                                        case 'Grid':
                                            $usrMsg = "Period vs. Room - drag and drop to rearrange";

                                            #form to select term
                                            echo '<form method="POST" action="' .  $_SERVER['PHP_SELF'] . '" >';
                                            echo '<div class="form-group" style="display:inline-block;width:85%;margin-right:5px;">';
                                            echo '<label class="small mb-1" for="sem">Term</label>';
                                            echo '<select class="custom-select custom-select-sm form-control form-control-sm" id="sem" name="sem" required />';
                                            echo '<option value="" disabled';
                                            if (!isset($_SESSION['sem'])) {
                                                echo "selected"; 
                                            }
                                            echo '>Select a term</option>';
                                            $_SESSION['terms'] = array_unique(array_column($_SESSION['classList'],4));
                                            foreach ($_SESSION['terms'] as $k => $sem) {
                                                echo "<option value={$k} ";
                                                if (isset($_SESSION['sem']) && $_SESSION['sem'] == $k) {
                                                    echo "selected";
                                                }
                                                echo ">{$sem}</option>";
                                            }
                                            echo '</select></div>';
                                            echo '<div class="form-group" style="display:inline-block;">';
                                            echo '<input type="submit" class="btn btn-primary" value="Generate Grid" name="gridGen" />';
                                            echo '</div></form>';
                                                
                                        case 'Section':
                                            $usrMsg = "Department & Teacher Totals - drag and drop to rearrange";
                                        echo '<form style="float:right" method="POST" action="index.php"><span title="Fetch grid from database"><input id="gridSave" type="button" value="Fetch grid content" class="btn btn-secondary" onclick="redips.save()"><input id="gridVals" type="hidden" name="gridVals" /><input id="holdVals" type="hidden" name="holdVals" /></span><span title="Save - Will not persist between sessions"><input id="gridPush" class="btn btn-secondary" type="submit" value="Save" name="gridPush" disabled /></span></form>';  
                                    }

                                    echo "<div style='margin:auto; color:dodgerblue;'>{$usrMsg}</div>";

                                    if (isset($_POST['new'])) {  ?>
                                        <br>
                                        <h5>New Class Info</h5>
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?> " >
                                            <div class="form-group" style="display:inline-block;width:20%">
                                                <label class="small mb-1" for="code">Course Code<span style="color:red;">*</span></label>
                                                <input class="form-control" id="code" type="text" placeholder="NUMBER.SECTION" name="code" <?php if (isset($_POST['code'])) { echo "value='" . $_POST['code'] . "'"; } ?> required/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:25%">
                                                <label class="small mb-1" for="cname">Course Name<span style="color:red;">*</span></label>
                                                <input class="form-control" id="cname" type="text" placeholder="Course Name" name="cname" <?php if (isset($_POST['cname'])) { echo "value='" . $_POST['cname'] . "'"; } ?> required/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:15%">
                                                <label class="small mb-1" for="exp">Period<span style="color:red;">*</span></label>
                                                <input class="form-control" id="exp" type="text" placeholder="#(A) #(B)" name="exp" <?php if (isset($_POST['exp'])) { echo "value='" . $_POST['exp'] . "'"; } ?> required/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:10%">
                                                <label class="small mb-1" for="term">Term<span style="color:red;">*</span></label>
                                                <input class="form-control" id="term" type="text" placeholder="S#" name="term" <?php if (isset($_POST['term'])) { echo "value='" . $_POST['term'] . "'"; } ?> required/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:25%">
                                                <label class="small mb-1" for="tname">Teacher Name<span style="color:red;">*</span></label>
                                                <input class="form-control" id="tname" type="text" placeholder="Surname, First name" name="tname" <?php if (isset($_POST['tname'])) { echo "value='" . $_POST['tname'] . "'"; } ?> required/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:15%">
                                                <label class="small mb-1" for="dept">Department</label>
                                                <input class="form-control" id="dept" type="text" placeholder="DEPT" name="dept" <?php if (isset($_POST['dept'])) { echo "value='" . $_POST['dept'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:15%">
                                                <label class="small mb-1" for="room">Room</label>
                                                <input class="form-control" id="room" type="text" placeholder="#" name="room" <?php if (isset($_POST['room'])) { echo "value='" . $_POST['room'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:10%">
                                                <label class="small mb-1" for="stu">Students</label>
                                                <input class="form-control" id="stu" type="text" placeholder="#" name="stu" <?php if (isset($_POST['stu'])) { echo "value='" . $_POST['stu'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group" style="display:inline-block;width:10%">
                                                <label class="small mb-1" for="max">Max Students</label>
                                                <input class="form-control" id="max" type="text" placeholder="#" name="max" <?php if (isset($_POST['max'])) { echo "value='" . $_POST['max'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group">
                                                <input style="display:inline-block;" class="btn btn-primary" id="createNew" type="submit" name="createNew" value="Create Class"/>
                                                <a class="btn btn-secondary" href="index.php" style="display:inline-block;">Cancel</a>
                                            </div>
                                        </form>
                                    <?php }
                                ?>
                            </div>
                            <div class="card-body"  >
                                <div >
                                    <div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4" >
                                        <div class="row" >
                                            <div class="col-sm-12" <?php if ($_SESSION['view'] == 'Grid' or $_SESSION['view'] == 'Section') { echo 'id="redips-drag"'; } ?> >
                                                <?php if ($_SESSION['view'] == 'Grid') { ?>
                                                    <style>
                                                        .dataTables_scrollHeadInner, .table{ 
                                                            width:100%!important; 
                                                        }
                                                    </style>
                                                    <div style="float: right; position: flex;">
                                                        <table id="holdingTank">
                                                            <tr><th>Holding Tank
                                                                <?php 
                                                                    foreach ($_SESSION['classList'] as $row) {
                                                                        if (empty($row[3]) && $row[4] == $_SESSION['terms'][$_SESSION['sem']]) {
                                                                            echo "<div class='redips-drag' style='width:100px' id='{$row[11]}'>{$row[0]}.{$row[1]}<br>{$row[5]} {$row[6]}</div>{$row[3]}";
                                                                        }
                                                                    }
                                                                ?>
                                                            </th></tr>
                                                        </table>
                                                    </div>
                                                <?php } ?>
                                                <table class="table table-bordered dataTable" <?php if ($_SESSION['view'] == 'Master') {echo 'id="dataTable"';} else {echo 'id="gridTable"';} ?> width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info">
                                                    
                                                    <!-- main table contents -->  
                                                    <?php
                                                        require_once("includes/dbvars.php");
                                                        require_once("includes/functions.php");

                                                        #display results in master table
                                                            #should occur on initial load as well
                                                        if ($_SESSION['view'] == 'Master') {
                                                            
                                                            echo "<tbody>";

                                                            #generate session array if first load
                                                            if (!isset($_SESSION['classList'])) {

                                                                #connect to db
                                                                $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("error connecting to db");
                                                                
                                                                #create query to select required info from multiple tables
                                                                $displayQ = "SELECT `courses`.`code`, `courses`.`section`,`courses`.`name`,`classes`.`expression`,`classes`.`term`,`teachers`.`fname`,`teachers`.`sname`,`teachers`.`dept`,classes.`room`,classes.`numstudents`,`classes`.`maxseats`,`classes`.`class_id`
                                                                    FROM `classes`
                                                                        INNER JOIN `courses` 
                                                                        ON `classes`.`course_id` = `courses`.`course_id` 
                                                                        INNER JOIN `teachers` 
                                                                        ON `classes`.`teacher_id` = `teachers`.`teacher_id` 
                                                                    ORDER BY `courses`.`code` ASC";

                                                                #execute query 
                                                                $result = mysqli_query($conn,$displayQ) or die("error retrieving records");
                                                                
                                                                #set empty vars
                                                                $n = 0; //counter
                                                                $_SESSION['classList'] = [];
                                                                
                                                                #fetch rows of information from result object
                                                                while ($row = mysqli_fetch_array($result)) {

                                                                    #add this information to vlasslist session var
                                                                    array_push($_SESSION['classList'],$row);

                                                                    #display rows as odd or even depending on counter
                                                                    echo "<tr role ='row'";
                                                                    if ($n%2 == 0) {
                                                                        echo " class='even'>";
                                                                    } else {
                                                                        echo " class='odd'>";
                                                                    }

                                                                    #display table
                                                                    $Editable = "ondblclick=\"
                                                                    javascript:this.childNodes[1].childNodes[0].setAttribute('type','text');
                                                                    javascript:this.childNodes[0].setAttribute('style','display:none');
                                                                    \"";
                                                                    $Form = "<form action='includes/saveTableData.php' method='post'>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[0]}.{$row[1]}</div>{$Form}<input name='{$n}-0' type='hidden' value='{$row[0]}.{$row[1]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[2]}</div>{$Form}<input name='{$n}-1' type='hidden' value='{$row[2]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[3]}</div>{$Form}<input name='{$n}-2' type='hidden' value='{$row[3]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[4]}</div>{$Form}<input name='{$n}-3' type='hidden' value='{$row[4]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[6]}, {$row[5]}</div>{$Form}<input name='{$n}-4' type='hidden' value='{$row[6]}, {$row[5]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[7]}</div>{$Form}<input name='{$n}-5' type='hidden' value='{$row[7]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[8]}</div>{$Form}<input name='{$n}-6' type='hidden' value='{$row[8]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[9]}</div>{$Form}<input name='{$n}-7' type='hidden' value='{$row[9]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$row[10]}</div>{$Form}<input name='{$n}-8' type='hidden' value='{$row[10]}'></input></form></td>";
                                                                    echo "<td><button class='btn btn-secondary' style='font-size:14px' onclick=\"if(confirm('Are you sure you want to delete this class?')){window.location='includes/deleteClass.php?row={$n}'}\">Delete</button></td>";
                                                                    echo "</tr>";

                                                                    $n++;
                                                                }

                                                                #close connection to db
                                                                mysqli_close($conn);

                                                            #else there is already a session classlist var, no need to query db
                                                                #rest is same as above, just looping throughh classlist intead of db result
                                                            } else {
                                                                #set counter
                                                                $n = 0;

                                                                #loop through classList as child arrays of rows
                                                                foreach ($_SESSION['classList'] as $child) {

                                                                    #display rows as odd or even depending on counter
                                                                    echo "<tr role ='row'";
                                                                    if ($n%2 == 0) {
                                                                        echo " class='even'>";
                                                                    } else {
                                                                        echo " class='odd'>";
                                                                    }

                                                                    #output table data
                                                                    $Editable = "ondblclick=\"
                                                                    javascript:this.childNodes[1].childNodes[0].setAttribute('type','text');
                                                                    javascript:this.childNodes[0].setAttribute('style','display:none');
                                                                    \"";

                                                                    $classList = print_r($_SESSION['classList'],true);
                                                                    $Form = "<form action='includes/saveTableData.php' method='post'>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[0]}.{$child[1]}</div>{$Form}<input name='{$n}-0' type='hidden' value='{$child[0]}.{$child[1]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[2]}</div>{$Form}<input name='{$n}-1' type='hidden' value='{$child[2]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[3]}</div>{$Form}<input name='{$n}-2' type='hidden' value='{$child[3]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[4]}</div>{$Form}<input name='{$n}-3' type='hidden' value='{$child[4]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[6]}, {$child[5]}</div>{$Form}<input name='{$n}-4' type='hidden' value='{$child[6]}, {$child[5]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[7]}</div>{$Form}<input name='{$n}-5' type='hidden' value='{$child[7]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[8]}</div>{$Form}<input name='{$n}-6' type='hidden' value='{$child[8]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[9]}</div>{$Form}<input name='{$n}-7' type='hidden' value='{$child[9]}'></input></form></td>";
                                                                    echo "<td {$Editable}><div style='display:block'>{$child[10]}</div>{$Form}<input name='{$n}-8' type='hidden' value='{$child[10]}'></input></form></td>";
                                                                    echo "<td><button class='btn btn-secondary' style='font-size:14px' onclick=\"if(confirm('Are you sure you want to delete ths class?')){window.location='includes/deleteClass.php?row={$n}'}\">Delete</button></td>";
                                                                    echo "</tr>";

                                                                    $n++;
                                                                }
                                                                echo "</tbody>";
                                                            } ?> 

                                                            <!-- table header for master view -->
                                                            <thead>
                                                                <tr role="row">
                                                                    <th class="sorting_asc" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Number.section: activate to sort column descending" >Number.section</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Course Name: activate to sort column ascending">Course Name</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Period: activate to sort column ascending">Period</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Term: activate to sort column ascending">Term</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Teacher Name: activate to sort column ascending">Teacher Name</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Teacher Dept.: activate to sort column ascending">Teacher Dept.</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Room: activate to sort column ascending">Room</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Students: activate to sort column ascending">Students</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Max Seats: activate to sort column ascending">Max Seats</th>
                                                                    <th class="sorting" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="">Delete</th>
                                                                </tr>
                                                            </thead>

                                                            <!-- table footer for master view -->
                                                            <tfoot>
                                                                <tr>
                                                                    <th rowspan="1" colspan="1">Number.Section</th>
                                                                    <th rowspan="1" colspan="1">Course Name</th>
                                                                    <th rowspan="1" colspan="1">Period</th>
                                                                    <th rowspan="1" colspan="1">Term</th>
                                                                    <th rowspan="1" colspan="1">Teacher Name</th>
                                                                    <th rowspan="1" colspan="1">Teacher Dept.</th>
                                                                    <th rowspan="1" colspan="1">Room</th>
                                                                    <th rowspan="1" colspan="1">Students</th>
                                                                    <th rowspan="1" colspan="1">Max Seats</th>
                                                                    <th rowspan="1" colspan="1">Delete</th>
                                                                </tr>
                                                            </tfoot>

                                                        <?php 
                                                        #otherwise they must be in some incarnation of grid view
                                                        } else {

                                                            #check if they have selected term for grid if needed
                                                            if (($_SESSION['view'] == 'Grid' && isset($_SESSION['sem'])) || $_SESSION['view'] == 'Section') {

                                                                #check what data to generate
                                                                if ($_SESSION['view'] == 'Grid') {
                                                                    $gridArr = GenerateGrid(3,8,$_SESSION['classList']);
                                                                } else if ($_SESSION['view'] == 'Section') {
                                                                    $gridArr = GenerateGrid(7,6,$_SESSION['classList']);
                                                                    $teacherNums = getTeacherClassNums();
                                                                    $deptNums = getDepartmentClassNums();
                                                                }
                                                                $_SESSION['xvals'] = $gridArr[1];
                                                                $_SESSION['yvals'] = $gridArr[2];
                                                                
                                                                #generate header & footer columns
                                                                for ($c = 0; $c < 2; $c++) {
                                                                    if ($c) {
                                                                        echo "<tfoot>";
                                                                    } else {
                                                                        echo "<thead>";
                                                                    }
                                                                    echo "<th class='redips-mark'></th>";
                                                                    foreach ($gridArr[1] as $xv) {
                                                                        if ($_SESSION['view'] == 'Section' && $xv == "") {
                                                                            $xv = "MISC";
                                                                        }
                                                                        if (!empty($xv)) {
                                                                            echo "<th class='redips-mark'>{$xv}";

                                                                            if ($_SESSION['view'] == 'Section') {
                                                                                echo "<div style='color:chartreuse;'>" . $deptNums[$xv] . " sections</div>";
                                                                            }
                                                                            
                                                                            echo "</th>";
                                                                        }
                                                                    }
                                                                    if ($c) {
                                                                        echo "</tfoot>";
                                                                    } else {
                                                                        echo "</thead>";
                                                                    }
                                                                }

                                                                #grid content
                                                                echo "<tbody>";
                                                                $len = count($gridArr[0]);
                                                                foreach ($gridArr[0] as $k => $row) {
                                                                    echo "<tr role ='row'";
                                                                    if ($k%2 == 0) {
                                                                        echo " class='even'>";
                                                                    } else {
                                                                        echo " class='odd'>";
                                                                    }
                                                                    
                                                                    echo "<th scope='row' class='redips-mark'>{$gridArr[2][$k]}";
                                                                    if ($_SESSION['view'] == 'Section') {
                                                                        echo "<div style='color:chartreuse;'>" . $teacherNums[$gridArr[2][$k]] . " sections</div>";
                                                                    }
                                                                    echo "</th>";
                                                                    foreach ($row as $k => $cell) {
                                                                        if (!empty($gridArr[1][$k]) || $_SESSION['view'] == 'Section') {
                                                                            echo "<td>";
                                                                            if (!empty($cell)) {
                                                                                foreach ($cell as $pos => $arr) {
                                                                                    if ($_SESSION['view'] == 'Section' || (!empty($arr[3]) && $arr[4] == $_SESSION['terms'][$_SESSION['sem']])) {
                                                                                        echo "<div class='redips-drag' style='width:100px' id='{$arr[11]}'>{$arr[0]}.{$arr[1]}<br>{$arr[5]} {$arr[6]}</div>"; 
                                                                                    }
                                                                                }
                                                                            }
                                                                            echo "</td>";
                                                                        }
                                                                    }
                                                                    echo "</tr>";
                                                                }
                                                                echo "</tbody>";
                                                            }
                                                        }
                                                    ?>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <pre> 
                    <?php #print_r($_POST); ?>
                    </pre>
                </main>

                <!-- footer info -->
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2019</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

        <!-- bootstrap -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        
        <!-- local scripts -->
        <script src="js/scripts.js"></script>
        <script src="js/datatables.js"></script>
        
    </body>
</html>
