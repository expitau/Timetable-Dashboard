<?php
	session_start();
	
	//set message empty by default
	$msg = "";
	
	//check if user has logged in
	if (!ISSET($_SESSION['user_id'])) {
		header("location:login.php"); //if not, send to login
		die();
	}
	
    require_once("includes/dbvars.php"); //connect to db
    $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die("Error connecting to database");
	
	//First name
	if (!empty($_POST['fname']) and $_POST['fname'] != $_SESSION['fname']) { //check if field is filled and if any changes have been made
		if (!preg_match("/[0-9]/", $_POST['fname'])) { //make sure there are no numbers in the name
		
			//create and execute query to update first name
			$firstQ = "UPDATE `users` SET `fname` = '{$_POST['fname']}' WHERE `users`.`user_id` = {$_SESSION['user_id']}";
			mysqli_query($conn, $firstQ) or DIE('Bad Query --> First');
			
			//notify user about update and set array value to new value so that it shows up in placeholder
			$msg .= "First Name Updated.<br>";
			$_SESSION['fname'] = $_POST['fname'];
			
		} else {
			$msg .= "First name must not contain numbers.<br>"; //if name was invalid, notify user
		}
	}
	//Last name
	if (!empty($_POST['sname'])and $_POST['sname'] != $_SESSION['sname']) { //check if field is filled and if any changes have been made
		if (!preg_match("/[0-9]/", $_POST['sname'])) { //make sure there are no numbers in the name
		
			//create and execute query to update last name
			$lastQ = "UPDATE `users` SET `last` = '{$_POST['sname']}' WHERE `users`.`user_id` = {$_SESSION['user_id']}";
			mysqli_query($conn, $lastQ) or DIE('Bad Query --> Last');
			
			//notify user about update and set array value to new value so that it shows up in placeholder
			$msg .= "Last Name Updated.<br>";
			$_SESSION['sname'] = $_POST['sname'];
			
		} else {
			$msg .= "Last name must not contain numbers.<br>"; //if name was invalid, notify user
		}
	}
	//Email
	if (!empty($_POST['email'])and $_POST['email'] != $_SESSION['email']) { //check if field is filled and if any changes have been made
		if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $_POST['email'])) { //make sure email follows valid format
			
			//create and execute query, get result for other emails that match email entered
			$otherEmails = "SELECT * FROM `users` WHERE `email` = '{$_POST['email']}'";
			$emailData = mysqli_query($conn, $otherEmails) or DIE('Bad Query --> Other Emails');
			$emailArr = mysqli_fetch_array($emailData); //should return null if no other accounts have same email
			
			if (!$emailArr) { //i.e. if no other accounts were found with entered email
				
				//create and execute query to update email
				$emailQ = "UPDATE `users` SET `email` = '{$_POST['email']}' WHERE `users`.`user_id` = {$_SESSION['user_id']}";
				mysqli_query($conn, $emailQ) or DIE('Bad Query --> Email');
				
				//notify user about update and set array value to new value so that it shows up in placeholder
				$msg .= "Email Updated.<br>";
				$_SESSION['email'] = $_POST['email'];
				
			} else { //i.e. there is already an account under desired email
				$msg .= "There is already an account with '{$_POST['email']}'"; //notify user
			}
		} else {
			$msg .= "Invalid email.<br>"; //if email was invalid, notify user
		}
    }
    
	//Password
	if (!empty ($_POST['pass'])) { //check if field is filled
		if (!empty ($_POST['confirm'])) { //check if password was confirmed
			if ($_POST['pass'] == $_POST['confirm']) { //check if passwords match
					
					//create hash, create query with hash and execute query
					$hash = password_hash($_POST['pass'], PASSWORD_DEFAULT);
					$passQ = "UPDATE `users` SET `pass` = '{$hash}' WHERE `users`.`user_id` = {$_SESSION['user_id']}";
					mysqli_query($conn, $passQ) or DIE('Bad Query --> Pass');
					
					$msg .= "Password Updated.<br>"; //notify user
					
//else notify user why password was not acceptable
			} else {
				$msg .= "Paswords do not match.<br>";
			}
		} else {
			$msg .= "Please Confirm Password.<br>";
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
        <title>Edit Account</title>
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
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Edit your Account</h3></div>
                                    <div class="card-body">
										<!-- If there is a message to the user, display it in a div -->
										<?php if (!empty($msg)) { echo "<div style='color:red;'>" . $msg . "</div>"; } ?>
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?> " >
                                            <div class="form-group">
                                                <label class="small mb-1" for="fname">Name</label>
                                                <input class="form-control py-4" id="fname" type="text" placeholder="First name" name="fname" onfocus="(value = '<?php echo $_SESSION['fname'] ?>')" <?php if (isset($_POST['fname'])) { echo "value='" . $_POST['fname'] . "'"; } ?>/>
                                                <input class="form-control py-4" id="sname" type="text" placeholder="Surname" name="sname" onfocus="(value = '<?php echo $_SESSION['sname'] ?>')" <?php if (isset($_POST['sname'])) { echo "value='" . $_POST['sname'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputEmailAddress">Email</label>
                                                <input class="form-control py-4" id="inputEmailAddress" type="email" placeholder="Enter new email address" name="email" onfocus="(value = '<?php echo $_SESSION['email'] ?>')" <?php if (isset($_POST['email'])) { echo "value='" . $_POST['email'] . "'"; } ?>/>
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputPassword">Password</label>
                                                <input class="form-control py-4" id="inputPassword" type="password" placeholder="Enter new password" name="pass" />
                                                <input class="form-control py-4" id="confirmPassword" type="password" placeholder="Confirm password" name="confirm" />
                                            </div>
                                            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0"><input type="submit" class="btn btn-primary" name="submit" value="Update" /></div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center">
                                        <div class="small"><a href = "index.php">Back to dashboard</a></div>
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