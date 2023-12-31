<?php
session_start();

if (!isset($_SESSION['name']) && !isset($_SESSION['reg_no'])) {
    header('Location: /');
    exit();
}
$role = $_SESSION['role'];
$eventName = $_GET['eventName'];

include('../routes/connect.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/houseDashboardStyles.css">


</head>

<body>
    <header class="site-header">
        <div class="header-bar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-10 col-lg-4">
                        <h1 class="site-branding flex">
                            <img src="../public/images/logos/waves-logo.png" alt="" class="" width="120">
                        </h1>
                    </div>
                    <div class="col-2 col-lg-8">
                        <nav class="site-navigation">
                            <div class="hamburger-menu d-lg-none">
                                <span style="background-color:black"></span>
                                <span style="background-color:black"></span>
                                <span style="background-color:black"></span>
                                <span style="background-color:black"></span>
                            </div>
                            <ul>
                                <li><a href="houseDashboard.php"><button type="button" class="btn btn-login btn-primary" data-toggle="modal" data-target="#loginModal">Dashboard</button></a></li>

                                <li><a href="../index.php"><button type="button" class="btn btn-login btn-primary" data-toggle="modal" data-target="#loginModal">Logout</button></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="card dark gradient-border" style="margin-top:140px">

        <?php
        $houseName = $_SESSION['house_name'];

        $eventsResult = mysqli_query($conn, "SELECT * FROM eventdb WHERE event_name = '$eventName'");
        $event = mysqli_fetch_assoc($eventsResult);

        $eventName = $event['event_name'];
        $houseName = mysqli_real_escape_string($conn, $houseName);
        $eventName = mysqli_real_escape_string($conn, $eventName);
        $eventCoordinatorResult = mysqli_query($conn, "SELECT name from admindb WHERE role = 'event coordinator' AND event_name = '$eventName'");
        $eventCoordinator = mysqli_fetch_assoc($eventCoordinatorResult);

        $SpecificEventRegStuCountResult = mysqli_query($conn, "SELECT COUNT(*) as row_count FROM registerationdb WHERE student_house = '$houseName' AND event_name = '$eventName'");
        if ($SpecificEventRegStuCountResult) {
            $registeredParticipants = mysqli_fetch_assoc($SpecificEventRegStuCountResult);
            $registeredParticipants = $registeredParticipants['row_count'];
        } else {
            $registeredParticipants = 0;
        }

        if ($event['is_group'] == 1) {
            $isGroup = 'Yes';
        } else {
            $isGroup = 'No';
        }

        ?>
        <img src="<?php echo '../' . $event['image'] ?>" class="card-img-top" alt="...">

        <div class="card-body">
            <div class="text-section">
                <h1 class='card-title'>
                    <?php echo $eventName ?>
                </h1>
                <h5 class="card-text">
                    Max Participants :
                    <?php echo $event['max_participants'] ?>
                </h5>
                <h5 class="card-text">
                    Registered Participants :
                    <?php echo $registeredParticipants ?>
                </h5>
                <h5 class="card-text">
                    Allowance :
                    <?php echo $event['max_participants'] - $registeredParticipants ?>
                </h5>
                <h5 class="card-text">
                    Group Event :
                    <?php echo $isGroup ?>
                </h5>
                <h5 class="card-text">
                    Group Count :
                    <?php echo $event['group_counts'] ?>
                </h5>
            </div>

        </div>
    </div>
    <?php if ($event['max_participants'] - $registeredParticipants > 0) { ?>
        <h2 style="color:black;">Add Participant</h2>

        <div class="container" style="display: flex;justify-content: center;">
            <form style="max-width: 320px;" action="../routes/studentReg/addStudent.php" class="form-control" method="post">
                <input style="width: 90%;margin: 12px;" type="text" name="house_name" value="<?php echo $houseName ?>" readonly>
                <input style="width: 90%;margin: 12px;" type="text" name="event_name" value="<?php echo $eventName ?>" readonly>

                <?php

                $studentResult = mysqli_query($conn, "SELECT reg_no FROM `studentdb` WHERE house = '$houseName'");

                echo "<div class='form-group'>
                <input style='width: 90%; margin: 12px;' type='text' list='listName' name='reg_number' id='reg_number' placeholder='Student Reg No' required class='form-control'>
                <datalist id='listName'>";
                while ($studentDetail = mysqli_fetch_array($studentResult)) {
                    echo "<option value='$studentDetail[0]'>$studentDetail[0]</option>";
                }

                echo "</datalist></div>";
                ?>

                <?php
                if ($event['is_group'] == 1) {
                    $groupCountResult = mysqli_query($conn, "SELECT group_counts FROM `eventdb` WHERE event_name = '$eventName'");
                    $groupCount = mysqli_fetch_array($groupCountResult);

                    if ($groupCount) {
                        $count = (int)$groupCount['group_counts'];

                        echo "<div class='form-group'>
                            <select style='width: 90%; margin: 12px;' type='text' list='listName' name='group' id='group' placeholder='Group Number' required class='form-control'>";
                        for ($i = 1; $i <= $count; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }

                        echo "</select></div>";
                    }
                } else {
                    echo '<input type="text" name="group" value="0" id="group" hidden>';
                }
                ?>


                <button style="width: 90%;margin: 12px;" class="btn btn-primary">Add</button>
            </form>
        </div> <br><br>
    <?php } else { ?>
        <?php } ?>

        <!-- table -->

        <div class="container">
            <div class="row">
                <div class="col-md-offset-1 col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <!-- <div class="col col-sm-3 col-xs-12">
                                <h4 class="title">Event <span>Details</span></h4>
                            </div> -->
                                <div class="col col-sm-3 col-xs-12">
                                    <h4 class="title">Event <span>Details</span></h4>
                                </div>
                                <div class="panel-body table-responsive">

                                    <?php
                                    $house_name = $_SESSION['house_name'];
                                    $event_list = mysqli_query($conn, "SELECT * FROM `eventdb` WHERE `event_name`= '$eventName'");
                                    $data = mysqli_fetch_array($event_list);
                                    $event = $data['is_group'];

                                    if ($event == '0') {
                                    ?>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th> </th>
                                                    <th>Register Number</th>
                                                    <th>Student Name</th>
                                                    <th>Student Department</th>
                                                    <th>Year</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $participantsList = mysqli_query($conn, "SELECT * FROM registerationdb WHERE event_name = '$eventName' && `student_house` = '$house_name'");
                                            $i = 1;
                                            while ($list = mysqli_fetch_array($participantsList)) {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $i++ ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $list['reg_no'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $list['student_name'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $list['student_dept']; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $sql = "SELECT * FROM studentdb WHERE reg_no = '$list[reg_no]'";

                                                        $result = $conn->query($sql);
                                                        if ($result->num_rows > 0) {
                                                            // Fetch the row as an associative array
                                                            $row = $result->fetch_assoc();

                                                            // Display the data from the selected row
                                                            echo $row["year"];
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href=<?php echo '../routes/studentReg/removeStudentRegisteration.php' . "?ID=" . urlencode($list['id']) . "&eventName=" . urlencode($eventName) ?> data-tip="trash"><i style="color: red;" class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </table>
                                </div>
                            <?php
                                    } else {
                            ?>
                                <?php
                                        $i = 1;
                                        $k = 1;
                                        while ($i <= $data['group_counts']) {
                                            $participantsList = mysqli_query($conn, "SELECT * FROM registerationdb WHERE event_name = '$eventName' && `student_house` = '$house_name' && `grouped` = $i");
                                ?>
                                    <div class="col col-sm-3 col-xs-12">
                                        <h4 class="title" style="margin-top: 24px;">Group <span><?php echo $i ?></span></h4>
                                    </div>
                                    <div class="panel-body table-responsive">

                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Register Number</th>
                                                <th>Student Name</th>
                                                <th>Department</th>
                                                <th>Year</th>

                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($list = mysqli_fetch_array($participantsList)) {
                                            ?>

                                                    <tr>
                                                        <td>
                                                            <?php echo $k++ ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $list['reg_no'] ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $list['student_name'] ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $list['student_dept'] ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $sql = "SELECT * FROM studentdb WHERE reg_no = '$list[reg_no]'";

                                                            $result = $conn->query($sql);
                                                            if ($result->num_rows > 0) {
                                                                // Fetch the row as an associative array
                                                                $row = $result->fetch_assoc();

                                                                // Display the data from the selected row
                                                                echo $row["year"];
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <a href=<?php echo '../routes/studentReg/removeStudentRegisteration.php' . "?ID=" . urlencode($list['id']) . "&eventName=" . urlencode($eventName) ?> data-tip="trash"><i style="color: red;" class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                </div>
                                            <?php
                                            }
                                            $i++;
                                            ?>
                                        </tbody>
                                    </table>
                            </div>
                    <?php
                                        }
                                    }
                    ?>

                        </div>
                    </div>



                </div>
            </div>
        </div>
        </div>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
        <script src="../public/js/coordinatordashboard.js"></script>
        <script type="text/javascript" src="../public/js/jquery.js"></script>
        <script type="text/javascript" src="../public/js/masonry.pkgd.min.js"></script>
        <script type="text/javascript" src="../public/js/jquery.collapsible.min.js"></script>
        <script type="text/javascript" src="../public/js/swiper.min.js"></script>
        <script type="text/javascript" src="../public/js/jquery.countdown.min.js"></script>
        <script type="text/javascript" src="../public/js/circle-progress.min.js"></script>
        <script type="text/javascript" src="../public/js/jquery.countTo.min.js"></script>
        <script type="text/javascript" src="../public/js/custom.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tsparticles-confetti@2.12.0/tsparticles.confetti.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/6a9b11d703.js" crossorigin="anonymous"></script>
</body>

</html>