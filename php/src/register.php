<?php
	session_start();
	
	//redirect if already logged in
	if (ISSET($_SESSION['user_id'])) {
		header("location:index.php");
		die();
	}
	
	//set msg to empty by default
	$msg = "";
	
	//check if form is submitted and set vars
	if (ISSET($_POST['sub'])) {
		$first = $_POST['first'];
		$last = $_POST['last'];
		$email = $_POST['email'];
		$pass = $_POST['pass'];
		$confirm = $_POST['confirm'];
		
		//select fields from users table at the provided email address
		$select = "SELECT * FROM `users` WHERE `users`.`email` = '{$email}'";
        require_once('includes/dbvars.php');
        $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$emailCheck = mysqli_query($conn, $select);
		
		//check if data meets requirements:
		if (!empty($first) and !empty($last) and !empty($email) and !empty($pass) and !empty($confirm)) { //all fields filled
			if (!preg_match("/[0-9]/", $first) or !preg_match("/[0-9]/", $last)) { //names do not contain numbers
                if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) { //email follows valid format
                    $data = @mysqli_fetch_array($emailCheck);
                    if (!$data) { //if data retured NULL (i.e. no account exists)
                        if ($pass == $confirm) { //check that passwords match
                            #if (strlen($pass) >= 8) { //check that password is at least 8 characters long
                                //create hash of password to protect data in database
                                $hash = password_hash($pass, PASSWORD_DEFAULT);
                                
                                //create row in users table for the user, with first name, last name, email, date of birth and hashed hashed password (as well as default location and health)
                                $query = "INSERT INTO `users` (`user_id`, `fname`, `sname`, `email`, `pass`) VALUES (NULL, '{$first}', '{$last}', '{$email}', '{$hash}')";
                                mysqli_query($conn, $query) or DIE ('Bad Query<br>' . mysqli_error($conn));
                                header("location:login.php");
								die();
#notify user why their data did not meet requirements
                            #} else {
                                $msg = "Password must be 8 characters or longer.";
                            #}
                        } else {
                            $msg = "passwords do not match.";
                        }
                    } else { //if data was found
                        $msg = "Oops, looks like there is already an account under that email!";
                    }
				} else {
					$msg = "Email is invalid.";
				}
			} else {
				$msg = "First and/or last names must not contain numbers.";
			}
		} else {
			$msg = "All fields must be filled.";
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
        <title>Page Title - SB Admin</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Create Account</h3></div>
                                    <div class="card-body">
                                        <!-- If there is a message to the user, display it in a div -->
										<?php if (!empty($msg)) { echo "<div style='color:red;'>" . $msg . "</div>"; } ?>
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?> " >
                                            <div class="form-row">
                                                <div class="col-md-6">
                                                    <div class="form-group"><label class="small mb-1" for="inputFirstName">First Name</label><input class="form-control py-4" id="inputFirstName" type="text" placeholder="Enter first name" name="first" require <?php if (isset($_POST['first'])) { echo "value='" . $_POST['first'] . "'"; } ?> /></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group"><label class="small mb-1" for="inputLastName">Last Name</label><input class="form-control py-4" id="inputLastName" type="text" placeholder="Enter last name" name="last" require <?php if (isset($_POST['last'])) { echo "value='" . $_POST['last'] . "'"; } ?> /></div>
                                                </div>
                                            </div>
                                            <div class="form-group"><label class="small mb-1" for="inputEmailAddress">Email</label><input class="form-control py-4" id="inputEmailAddress" type="email" aria-describedby="emailHelp" placeholder="Enter email address" name="email" require <?php if (isset($_POST['email'])) { echo "value='" . $_POST['email'] . "'"; } ?> /></div>
                                            <div class="form-row">
                                                <div class="col-md-6">
                                                    <div class="form-group"><label class="small mb-1" for="inputPassword">Password</label><input class="form-control py-4" id="inputPassword" type="password" placeholder="Enter password" name="pass" require <?php if (isset($_POST['pass'])) { echo "value='" . $_POST['pass'] . "'"; } ?> /></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group"><label class="small mb-1" for="inputConfirmPassword">Confirm Password</label><input class="form-control py-4" id="inputConfirmPassword" type="password" placeholder="Confirm password" name="confirm" require /></div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-4 mb-0"><input class="btn btn-primary btn-block" name="sub" type="submit" value="Create Account" /></div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center">
                                        <div class="small"><a href="login.php">Have an account? Go to login</a></div>
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
