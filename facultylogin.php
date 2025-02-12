<?php
ob_start();
session_start();
require "php/config.php"; // This should include the database connection class
require_once "php/functions.php"; // This should include the login_registration_class

// Initialize the database connection
$db = new databaseConnection(); 
$conn = $db->getConnection(); // Get the connection object

// Pass the connection to the login_registration_class
$user = new login_registration_class($conn);

if ($user->get_faculty_session()) {
    header('Location: class_att_fc.php');
    exit();
}
?>

<?php 
$pageTitle = "Faculty login";
include "header.php";
?>
<div class="loginform fix">
    <div class="msg"><h3><i class="fa fa-user" aria-hidden="true"></i> Faculty login</h3></div>
    <div class="access">
        <?php
        // PHP for faculty login
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $username = $_POST['user'];
            $psw = $_POST['psw'];

            if (empty($username) || empty($psw)) {
                echo "<p style='color:red;text-align:center;'>Field must not be empty.</p>";
            } else {
                $psw = md5($psw);
                $login = $user->fct_login($username, $psw);
                if ($login) {
                    header('Location: class_att_fc.php');
                } else {
                    echo "<p style='color:red;text-align:center'>Incorrect Username or password</p>";
                }
            }
        }
        ?>
        <form action="" method="post">
            <input type="text" name="user" placeholder="Username" />
            <input type="password" name="psw" placeholder="Password" />
            <input style="color:#ddd;background:#3498db" type="submit" value="Login" />
        </form>
    </div>
    <p>Not Registered? <a href="fct_reg.php">Create an account</a></p>
</div>

<?php
include "footer.php";
ob_end_flush();
?>
