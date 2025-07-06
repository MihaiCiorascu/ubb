<?php
session_start();

include("database.php");

if (!isset($_SESSION["username"])) {
    header("location: index.php");
    exit();
}

if (!isset(($_SESSION["tooltip"]))){
    $_SESSION["tooltip"] = false;
}

if (!isset(($_SESSION["lastUsername"]))){
    $_SESSION["lastUsername"] = $_SESSION["username"];
}

if (!isset($_SESSION["countMoves"])) {
    $_SESSION["countMoves"] = 0;
}

if (!isset($_SESSION["updatedTaskId"])) {
    $_SESSION["updatedTaskId"] = 0; 
}

$username = $_SESSION["username"];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskLog</title>
</head>
<body>
    <h2>Welcome back, <?php echo $_SESSION["username"] ?> !</h2>
    <div>
        Status moves in this session: <?php echo $_SESSION["countMoves"]; ?>
    </div>

    <h2>Change status</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        TaskId:<br>
        <input type="text" name="taskId"><br><br>
        New status(choose between: todo, in_progress, done):<br>
        <input type="text" name="newStatus"><br>
        <input type="submit" name="changeStatus" value="Update">
    </form>
</body>
</html>

<?php
    if(isset($_POST["changeStatus"]) && isset($_POST["taskId"]) && isset($_POST["newStatus"])){
        $taskId = $_POST["taskId"];
        $newStatus = $_POST["newStatus"];

        $sqlTask1 = "SELECT * FROM task WHERE id='$taskId'";
        $allTask1 = mysqli_query($conn, $sqlTask1);
        while($currentTask1 = mysqli_fetch_assoc($allTask1)){
            $currentUserId = $_SESSION["userId"];
            $oldTaskStatus = $currentTask1["status"];

            $sqlUpdateStatus = "INSERT INTO tasklog (taskId, userId, oldStatus, newStatus, timestamp) VALUES ('$taskId', '$currentUserId', '$oldTaskStatus', '$newStatus',NOW())";
            mysqli_query($conn, $sqlUpdateStatus);
        }

        $sqlUpdateStatus = "UPDATE task SET status='$newStatus' WHERE id='$taskId'";
        if(mysqli_query($conn, $sqlUpdateStatus)){
            echo "Status updated";
            $_SESSION["tooltip"] = true;
            $_SESSION["updatedTaskId"] = $taskId;
            $_SESSION["countMoves"]++;
        }
        else{
            echo "Error status update";
        }
    }

    unset($_POST["changeStatus"]);
    unset($_POST["taskId"]);
    unset($_POST["newStatus"]);
?>


<?php 

    $sqlTaskTodo = "SELECT * FROM task";
    $allTasks = mysqli_query($conn, $sqlTaskTodo);

    echo "<h4>Task board</h4>";
        echo "<form method='post'>";
        echo "<table border='1'>
                    <tr>
                        <th>To Do</th>
                        <th>In Progress</th>
                        <th>Done</th>
                        <th>Tooltip</th>
                    </tr>
            ";

    while($currentTask = mysqli_fetch_assoc($allTasks)){
        $currentTaskStatus = $currentTask["status"];
        $currentTaskId = $currentTask["id"];
        $currentTitle = $currentTask["title"];
        $currentTooltip = $_SESSION["tooltip"];
        $lastUpdatedId = $_SESSION["updatedTaskId"];

        // $sqlTasklog = "SELECT * FROM tasklog WHERE taskId='$currentTaskId'";
        // $allTasklogs = mysqli_query($conn, $sqlTasklog);
        // while($currentTasklog = mysqli_fetch_assoc($allTasklogs)){
        //     $currentTasklogTaskId = $currentTasklog["taskId"];
        //     $currentTasklogUserId = $currentTasklog["userId"];

        //     $sqlUser = "SELECT * FROM user WHERE id='$currentTasklogUserId'";
        //     $allUsers = mysqli_query($conn, $sqlUser);
        //     while($currentUser = mysqli_fetch_assoc($allUsers)){
        //         if($currentUser["id"] == $currentTasklogUserId){
        //             $lastUpdatedName = $currentUser["username"];
                            echo "<tr>
                            <td>" . ($currentTaskStatus == "todo" ? $currentTask["title"] : "") . "</td>
                            <td>" . ($currentTaskStatus == "in_progress" ? $currentTask["title"] : "") . "</td>
                            <td>" . ($currentTaskStatus == "done" ? $currentTask["title"] : "") . "</td>
                            <td>" . ($currentTooltip == true && $currentTaskId == $lastUpdatedId ? ("Last updated by " . $username) : ("Last updated by ")) . "</td>
                            </tr>";
                
    }
    echo "</table>";
    echo "</form>";        

?>




<form method="post">
    <input type="submit" name="logout" value="Logout">
</form>

<?php
if (isset($_POST["logout"])) {
    session_destroy();
    header("location:index.php");
}
mysqli_close($conn);
?>