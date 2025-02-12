<?php
session_start();
require "php/config.php";
require_once "php/functions.php";
$user = new login_registration_class();
$fid = $_SESSION['f_id'];
$funame = $_SESSION['f_uname'];
$fname = $_SESSION['f_name'];

if (!$user->get_faculty_session()) {
    header('Location: facultylogin.php');
    exit();
}

// Function to calculate attendance percentage for a given student
function calculate_attendance_percentage($st_id) {
    global $conn;
    
    // Total days attended by the student
    $total_days_query = $conn->query("SELECT COUNT(DISTINCT at_date) as total_days FROM attn WHERE st_id='$st_id'");
    $total_days = $total_days_query->fetch_assoc()['total_days'];
    
    // Present days attended by the student
    $present_days_query = $conn->query("SELECT COUNT(*) as present_days FROM attn WHERE st_id='$st_id' AND atten='present'");
    $present_days = $present_days_query->fetch_assoc()['present_days'];
    
    // Calculate percentage
    if ($total_days > 0) {
        $percentage = ($present_days / $total_days) * 100;
    } else {
        $percentage = 0;
    }
    
    return $percentage;
}
?>

<?php 
$pageTitle = "All student details";
include "php/headertop.php";
?>

<div class="all_student fix">
    <h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">View Attendance</h3>
    <div class="fix" style="background:#ddd;padding:20px;">
        <span style="float:right;">
            <button style="background:#58A85D;border:none;color:#fff;padding:10px;">
                <a style="color:#fff;" href="class_att_fc.php">Take Attendance</a>
            </button>
        </span>
    </div>

    <table class="tab_one" style="text-align:center;">
        <tr>
            <th style="text-align:center;">SL</th>
            <th style="text-align:center;">Attendance Date</th>
            <th style="text-align:center;">Action</th>
            <th style="text-align:center;">Student Attendance Percentage</th> <!-- New Column -->
        </tr>
        <?php 
        $i = 0;
        $get_date = $user->get_attn_date();
        
        while ($rows = $get_date->fetch_assoc()) {
            $date = $rows['at_date'];
            $students = $user->get_students_by_date($date); // Assuming this function fetches students for the given date
            
            while ($student = $students->fetch_assoc()) {
                $i++;
                $percentage = calculate_attendance_percentage($student['st_id']);
        ?>
        <tr>
            <td><?php echo $i;?></td>
            <td><?php echo $date;?></td>
            <td><a href="att_single_view_fc.php?dt=<?php echo $date; ?>">View Attendance</a></td>
            <td><?php echo round($percentage, 2) . '%'; ?></td> <!-- Display Percentage -->
        </tr>
        <?php 
            }
        }
        ?>
    </table>
</div>

<?php include "php/footerbottom.php";?>
<?php ob_end_flush(); ?>
