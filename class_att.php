<?php
session_start();
require "php/config.php";  // Ensure this includes database connection setup
require_once "php/functions.php"; // Ensure this includes the class definition and methods

// Ensure you have a database connection object
$db = new databaseConnection(); // Initialize database connection
$conn = $db->getConnection(); // Get the database connection

// Initialize the login_registration_class with the connection
$user = new login_registration_class($conn);

// Check if admin session is valid
if (!$user->get_admin_session()) {
    header('Location: index.php');
    exit();
}

// Get session variables safely
$admin_id = $_SESSION['admin_id'] ?? ''; // Use null coalescing operator to handle unset keys
$admin_name = $_SESSION['admin_name'] ?? ''; // Use null coalescing operator to handle unset keys

// Function to calculate attendance percentage for a given date
function calculate_attendance_percentage($date) {
    global $conn;
    
    // Total number of students
    $total_students_query = $conn->query("SELECT COUNT(DISTINCT st_id) as total_students FROM at_student");
    $total_students = $total_students_query->fetch_assoc()['total_students'];
    
    // Total number of present students for the given date
    $present_students_query = $conn->query("SELECT COUNT(DISTINCT st_id) as present_students FROM attn WHERE at_date='$date' AND atten='present'");
    $present_students = $present_students_query->fetch_assoc()['present_students'];
    
    // Calculate percentage
    if ($total_students > 0) {
        $percentage = ($present_students / $total_students) * 100;
    } else {
        $percentage = 0;
    }
    
    return $percentage;
}
?>  
<?php 
$pageTitle = "All student details";
include "php/headertop_admin.php";
?>
<div class="all_student fix">
    <h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">Attendance Management</h3>
    <div class="fix" style="background:#ddd;padding:20px;">
        <span style="float:left;"><a style="color:#fff;" href="att_add.php"><button style="background:#58A85D;border:none;color:#fff;padding:10px;">Add student</button></a></span>
        <span style="float:right;"><a style="color:#fff;" href="att_view.php"> <button style="background:#58A85D;border:none;color:#fff;padding:10px;">View Attendance</button></a></span>
    </div>
    <?php
    if(isset($_REQUEST['res'])){
        echo "<h3 style='color:green;margin:0;padding:0;text-align:center'>Data deleted successfully!</h3>";
    }
    ?>
    <?php
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $cur_date = $_POST['attndate'];
        $atten = $_POST['attn'];
        $res = $user->insertattn($cur_date, $atten);
        if($res){
            echo "<h3 style='color:green;margin:0;padding:0;text-align:center'>Attendance data successfully inserted!</h3>";
        } else {
            echo  "<p style='color:red;text-align:center'>Failed to insert data</p>";
        }
    }
    ?>
    
    <form action="" method="post">
        <p style="text-align:center;color:#34495e;">
            <mark>Select date: <input type="date" name="attndate" required/></mark>
        </p>
        <table class="tab_one" style="text-align:center;">
            <tr>
                <th style="text-align:center;">SL</th>
                <th style="text-align:center;">Name</th>
                <th style="text-align:center;">ID</th>
                <th style="text-align:center;">Attendance</th>
                <th style="text-align:center;">Delete student</th>
            </tr>
            <?php 
            $i = 0;
            $alluser = $user->attn_student();
            
            while($rows = $alluser->fetch_assoc()){
                $i++;
            ?>
            <tr>
                <td><?php echo $i;?></td>
                <td><?php echo htmlspecialchars($rows['name']); ?></td>
                <td><?php echo htmlspecialchars($rows['st_id']); ?></td>
                <td>
                    <label style="color:red;font-size:20px"><input type="radio" name="attn[<?php echo htmlspecialchars($rows['st_id']); ?>]" value="absent" checked/>Absent</label>
                    <label style="color:green;font-size:20px"> <input type="radio" name="attn[<?php echo htmlspecialchars($rows['st_id']); ?>]" value="present" />Present</label>
                </td>
                <td><a href="att_del.php?dl=<?php echo htmlspecialchars($rows['id']); ?>">Delete</a></td>
            </tr>
            <?php } ?>
        </table>
        <center>
            <span><input style="text-align:right;background:#58A85D;border:none;color:#fff;padding:8px 100px;" type="submit" name="submit" value="Submit" /></span> <br>
        </center>
    </form>

    <!-- Display attendance percentages -->
    <h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">Attendance Percentage</h3>
    <table class="tab_one" style="text-align:center;">
        <tr>
            <th style="text-align:center;">SL</th>
            <th style="text-align:center;">Attendance Date</th>
            <th style="text-align:center;">Attendance Percentage</th>
        </tr>
        <?php 
        $i = 0;
        $get_date = $user->get_attn_date();
        
        while ($rows = $get_date->fetch_assoc()) {
            $date = htmlspecialchars($rows['at_date']);
            $percentage = calculate_attendance_percentage($date); // Calculate percentage for the date
            $i++;
        ?>
        <tr>
            <td><?php echo $i;?></td>
            <td><?php echo $date;?></td>
            <td><?php echo round($percentage, 2) . '%'; ?></td> <!-- Display Percentage -->
        </tr>
        <?php } ?>
    </table>
</div>

<?php include "php/footerbottom.php";?>
<?php ob_end_flush(); ?>
