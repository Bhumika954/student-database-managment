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

if (isset($_REQUEST['dt'])) {
    $date = $_REQUEST['dt'];
}
?>

<?php 
$pageTitle = "Attendance details";
include "php/headertop.php";
?>

<div class="all_student fix">
    <h3 style="text-align:center;color:#fff;margin:0;padding:5px;background:#1abc9c">View Attendance Details</h3>
    <div class="fix" style="background:#ddd;padding:20px;">
        <span style="float:left;">
            <a style="color:#fff;" href="class_att_fc.php">
                <button style="background:#58A85D;border:none;color:#fff;padding:10px;">Take Attendance</button>
            </a>
        </span>
        <span style="float:right;">
            <a style="color:#fff;" href="att_view_fc.php">
                <button style="background:#58A85D;border:none;color:#fff;padding:10px;">View Attendance</button>
            </a>
        </span>
    </div>
    <p style="text-align:center;color:#34495e;margin:0;padding-top:8px;color:red;font-size:22px;">
        <?php echo "Attendance of: ".$date;?>
    </p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $atten = $_POST['attn'];
        $res = $user->update_attn($date, $atten);
        if ($res) {
            echo "<h3 style='color:green;margin:0;padding:0;text-align:center'>Attendance Updated successfully!</h3>";
        } else {
            echo  "<p style='color:red;text-align:center'>Failed to update data</p>";
        }
    }
    
    // Method to calculate attendance percentage
    function calculate_attendance_percentage($st_id) {
        global $conn;
        
        // Total days
        $total_days_query = $conn->query("SELECT COUNT(DISTINCT at_date) as total_days FROM attn");
        $total_days = $total_days_query->fetch_assoc()['total_days'];
        
        // Present days
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
    
    <form action="" method="post">
        <table class="tab_one" style="text-align:center;">
            <tr>
                <th style="text-align:center;">SL</th>
                <th style="text-align:center;">Name</th>
                <th style="text-align:center;">ID</th>
                <th style="text-align:center;">Attendance</th>
                <th style="text-align:center;">Percentage</th>
            </tr>
            <?php 
            $i = 0;
            $std = $user->attn_all_student($date);
            if ($std) {
                while ($rows = $std->fetch_assoc()) {
                    $i++;
                    $percentage = calculate_attendance_percentage($rows['st_id']);
            ?>
            <tr>
                <td><?php echo $i;?></td>
                <td><?php echo $rows['name'];?></td>
                <td><?php echo $rows['st_id'];?></td>
                <td>
                    <label style="color:red;font-size:20px">
                        <input type="radio" name="attn[<?php echo $rows['st_id'];?>]" value="absent" <?php if($rows['atten'] == "absent") echo "checked";?>/>Absent
                    </label>
                    <label style="color:green;font-size:20px">
                        <input type="radio" name="attn[<?php echo $rows['st_id'];?>]" value="present" <?php if($rows['atten'] == "present") echo "checked";?>/>Present
                    </label>
                </td>
                <td><?php echo round($percentage, 2) . '%'; ?></td>
            </tr>
            <?php 
                } 
            } else {
                echo "Failed to retrieve data.";
            }
            ?>
        </table>
        <span style="margin-left:360px;">
            <input style="background:#58A85D;border:none;color:#fff;padding:8px 100px;" type="submit" name="submit" value="Update" />
        </span>
    </form>
</div>

<?php include "php/footerbottom.php";?>
<?php ob_end_flush(); ?>
