<?php
    session_start();
    if (ISSET($_SESSION['user_id'])) { //if user has already logged in, send them to the game
        header("location:index.php");
        die();
    } else if (ISSET($_POST['login'])) {
        
        //declare vars
        $email = $_POST['email'];
        $pwd = $_POST['pass'];
        
        if (!empty($email) and !empty($pwd)) { //check if both email and password have been entered
            
            //connect to database and select all data from active accounts under entered email
            require_once('includes/dbvars.php');
            $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("error connecting to database");
            $query = "SELECT * FROM `users` WHERE `email` = '{$email}'";
            $userData = mysqli_query($conn, $query) or DIE('Bad Query<br>' . mysqli_error($conn));
            $userArr = mysqli_fetch_array($userData); //create an array of the user's information --> will return null if no account found
            
            if ($userArr) { //if there is an active account that exists with given email
            
                //create and check boolean using password_verify() and the hash that is in the database
                $verifyBool = password_verify($pwd, $userArr['pass']);
                if ($verifyBool) {
        
                    //assign $_SESSION['user_id'] to keep track of user
                    $_SESSION['user_id'] = $userArr['user_id'];
                    $_SESSION['fname'] = $userArr['fname'];
                    $_SESSION['sname'] = $userArr['sname'];
                    $_SESSION['view'] = "Master";
                    
                    //send user to the gui
                    header("location:index.php");
                    die();
                    
    #else create message to user telling them why they could not log in
                } else {
                    $msg = "Password is incorrect.";
                }
            } else {
                $msg = "No account found with '{$email}'";
            }
        } else {
            $msg = "Please enter email and password";
        }
    }
?>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Login</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Login</h3></div>
                                    <div class="card-body">
										<!-- If there is a message to the user, display it in a div -->
										<?php if (!empty($msg)) { echo "<div style='color:red;'>" . $msg . "</div>"; } ?>
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?> " >
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputEmailAddress">Email</label>
                                                <input class="form-control py-4" id="inputEmailAddress" type="email" placeholder="Enter email address" name="email" <?php if (isset($_POST['email'])) { echo "value='" . $_POST['email'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputPassword">Password</label>
                                                <input class="form-control py-4" id="inputPassword" type="password" placeholder="Enter password" name="pass" />
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" id="rememberPasswordCheck" type="checkbox" />
                                                    <label class="custom-control-label" for="rememberPasswordCheck">Remember password</label>
                                                </div>
                                            </div>
                                            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0"><a class="small" href="password.php">Forgot Password?</a><input type="submit" class="btn btn-primary" name="login" value="Login" /></div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center">
                                        <div class="small"><a href="register.php">Need an account? Sign up!</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
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
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
