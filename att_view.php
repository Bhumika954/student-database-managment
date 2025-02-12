<?php
session_start();
require "php/config.php";
require_once "php/functions.php";
$user = new login_registration_class();
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
if(!$user->get_admin_session()){
    header('Location: index.php');
    exit();
}

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
        <span style="float:left;">
            <button style="background:#58A85D;border:none;color:#fff;padding:10px;">
                <a style="color:#fff;" href="att_add.php">Add student</a>
            </button>
        </span>
        <span style="float:right;">
            <button style="background:#58A85D;border:none;color:#fff;padding:10px;">
                <a style="color:#fff;" href="class_att.php">Take Attendance</a>
            </button>
        </span>
    </div>

    <table class="tab_one" style="text-align:center;">
        <tr>
            <th style="text-align:center;">SL</th>
            <th style="text-align:center;">Attendance Date</th>
            <th style="text-align:center;">Action</th>
            <th style="text-align:center;">Attendance Percentage</th> <!-- New Column -->
        </tr>
        <?php 
        $i = 0;
        $get_date = $user->get_attn_date();
        
        while ($rows = $get_date->fetch_assoc()) {
            $date = $rows['at_date'];
            $percentage = calculate_attendance_percentage($date); // Calculate percentage for the date
            $i++;
        ?>
        <tr>
            <td><?php echo $i;?></td>
            <td><?php echo $date;?></td>
            <td><a href="att_single_view.php?dt=<?php echo $date; ?>">View Attendance</a></td>
            <td><?php echo round($percentage, 2) . '%'; ?></td> <!-- Display Percentage -->
        </tr>
        <?php } ?>
    </table>
</div>

<?php include "php/footerbottom.php";?>
<?php ob_end_flush(); ?>
