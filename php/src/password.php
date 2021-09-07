<?php
    #remove all existing session data (log them out) so no discrepancies occur
    session_start();
    session_destroy();

    #initialize empty message to user
    $msg = "";

    #Import PHPMailer classes into the global namespace
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    #check if user has submitted data
    if (isset($_POST['sendButton'])) {
        if (!empty($_POST['email'])) {

            #extract from post
            $email = $_POST['email'];

            #connect to db
            require_once('includes/dbvars.php');
            $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die('error connecting to database');

            #define and execute query for account information
            $emailQ = "SELECT COUNT(`user_id`), `pass`, `fname`, `sname` FROM `users` WHERE `email` = '{$email}'";
            $result = mysqli_query($conn,$emailQ) or die ('error querying database');

            #extract data from result
            $data = mysqli_fetch_array($result);
            $count = $data[0]; //should return 1 or 0
            $hash = $data[1];
            $name = $data[2] . " " . $data[3];

            #if an account exists, send email
            if ($count) {

                #create vars for email
                $subject = "Reset Password";
                $link = "<a href='localhost/timetable/reset.php?hash={$hash}'>Reset Password</a>";
                $emailBody = "Click on the link below to reset your password:<br>" . $link . "<br><br>or go back to <a href='localhost/timetable'>login</a>";

                #send email
                require 'PHPMailer/src/Exception.php';
                require 'PHPMailer/src/PHPMailer.php';
                require 'PHPMailer/src/SMTP.php';

                //Create a new PHPMailer instance
                $mail = new PHPMailer;

                //Tell PHPMailer to use SMTP
                $mail->isSMTP();

                //Enable SMTP debugging
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;

                //Set the hostname of the mail server
                $mail->Host = 'smtp.gmail.com';

                //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
                $mail->Port = 587;

                //Set the encryption mechanism to use - STARTTLS or SMTPS
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

                //Whether to use SMTP authentication
                $mail->SMTPAuth = false;
                /*
                //Username to use for SMTP authentication - use full email address for gmail
                $mail->Username = 'username@gmail.com';

                //Password to use for SMTP authentication
                $mail->Password = 'yourpassword';*/

                //Set who the message is to be sent from
                $mail->setFrom('from@example.com', 'First Last');

                //Set who the message is to be sent to
                $mail->addAddress($email, $name);

                //Set the subject line
                $mail->Subject = $subject;

                //Replace the plain text body with one created manually
                $mail->AltBody = $emailBody;

                //send the message, check for errors
                if (!$mail->send()) {
                    echo 'Mailer Error: '. $mail->ErrorInfo;
                } else {
                    echo 'Message sent!';
                }
                
                
#else set an error message for the user
            } else {
                $msg = "No account found with {$email}";
            }

        } else {
            $msg = "Please enter an email address";
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
        <title>Forgot Password</title>
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
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Password Recovery</h3></div>
                                    <div class="card-body">
                                        <div class="small mb-3 text-muted">Enter your email address and we will send you a link to reset your password.</div>
                                        <!-- If there is a message to the user, display it in a div -->
										<?php if (!empty($msg)) { echo "<div style='color:red;'>" . $msg . "</div>"; } ?>
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputEmailAddress" name="email" require>Email</label>
                                                <input name="email" class="form-control py-4" id="inputEmailAddress" type="email" aria-describedby="emailHelp" placeholder="Enter email address" />
                                            </div>
                                            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="login.php">Return to login</a>
                                                <input type="submit" class="btn btn-primary" value="Reset Password" name="sendButton" />
                                            </div>
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
