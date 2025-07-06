<?php
session_start();

include("database.php");

if (!isset($_SESSION["username"])) {
    header("location: index.php");
    exit();
}

// Initialize session variables if not set
if (!isset($_SESSION["flagged_count"])) {
    $_SESSION["flagged_count"] = 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
</head>
<body>
    <h2>Welcome back, <?php echo $_SESSION["username"] ?> !</h2>
    
    <!-- Display flagged feedback count -->
    <div>
        Number of flagged feedbacks in this session: <?php echo $_SESSION["flagged_count"]; ?>
    </div>

    <?php 
    if ($_SESSION["flagged_count"] >= 2) {
        echo '<div style="color: red;">
            Warning: You have submitted multiple feedbacks with blocked words.
        </div>';
    }
    ?>

</body>
</html>

<h2>Add feedback</h2>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Message:<br>
    <input type="text" name="addMessage" value="<?php echo isset($_POST['addMessage']) ? $_POST['addMessage'] : ''; ?>"><br>
    <input type="submit" name="addFeedback" value="Submit">
    <input type="submit" name="removeWords" value="Remove Words">
</form>

<?php 
    if(isset($_POST["addFeedback"]) && isset($_POST["addMessage"])){
        $userId = $_SESSION["userId"];
        $countBad = 0;
        $currMessage = $_POST["addMessage"]; 
        $matches = array();
        
        echo "<h4>Feedback Preview:</h4>";
        
        // Check message against regex patterns
        $sqlBadWords = "SELECT * FROM blockedwords";
        $allBadWords = mysqli_query($conn, $sqlBadWords);
        
        mysqli_data_seek($allBadWords, 0);
        while($badWordRow = mysqli_fetch_assoc($allBadWords)){
            $pattern = $badWordRow["pattern"];
            if(preg_match("/$pattern/i", $currMessage, $match)) {
                $countBad++;
                $matches[] = $match[0];
            }
        }

        // Display warning if more than 3 blocked terms found
        if($countBad > 3){
            echo "<div style='color: red;'>";
            echo "Warning: Your message contains too many blocked words: " . implode(", ", $matches);
            echo "</div>";
            
            // Track flagged message in session
            $_SESSION["flagged_count"]++;
            
            // Highlight blocked words in uppercase
            $highlightedMessage = $currMessage;
            foreach($matches as $m) {
                $highlightedMessage = str_ireplace($m, strtoupper($m), $highlightedMessage);
            }
            
            $sqlInsertMessage = "INSERT INTO feedback (customerId, message, timestamp) VALUES ('$userId', '$highlightedMessage', NOW())";
        } else {
            $sqlInsertMessage = "INSERT INTO feedback (customerId, message, timestamp) VALUES ('$userId', '$currMessage', NOW())";
        }
        
        mysqli_query($conn, $sqlInsertMessage);
        
        echo "<div>Your message: " . ($countBad > 3 ? $highlightedMessage : $currMessage) . "</div>";
    } elseif(isset($_POST["removeWords"]) && isset($_POST["addMessage"])){
        $currMessage = $_POST["addMessage"];
        $fixedMessage = $currMessage;
        
        $sqlBadWords = "SELECT * FROM blockedwords";
        $allBadWords = mysqli_query($conn, $sqlBadWords);
        
        // Remove all matches of blocked words
        mysqli_data_seek($allBadWords, 0);
        while($badWordRow = mysqli_fetch_assoc($allBadWords)){
            $pattern = $badWordRow['pattern'];
            $fixedMessage = preg_replace("/$pattern/i", "", $fixedMessage);
        }
        
        // Clean up multiple spaces that might be left after word removal
        $fixedMessage = preg_replace('/\s+/', ' ', trim($fixedMessage));
        
        echo "<h4>Message after removing blocked words:</h4>";
        echo $fixedMessage;
        
        // Add a hidden form to submit the cleaned message
        echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
        echo '<input type="hidden" name="addMessage" value="' . htmlspecialchars($fixedMessage) . '">';
        echo '<input type="submit" name="addFeedback" value="Submit this cleaned message">';
        echo '</form>';
    }
?>

<h2>Update feedback</h2>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Feedback Id:<br>
    <input type="text" name="updateId"><br>
    Feedback message:<br>
    <input type="text" name="updateMessage"><br>
    <input type="submit" name="updateFeedback" value="Update">
</form>

<?php 
    if(isset($_POST["updateFeedback"])){
        $idToSwap = $_POST["updateId"];
        $messageToSwap = $_POST["updateMessage"];
        $sqlUpdateFeedback = "UPDATE feedback SET message='$messageToSwap' WHERE id='$idToSwap'";
        mysqli_query($conn, $sqlUpdateFeedback);
    }
?>


<?php 
        $sqlFeedback = "SELECT * FROM feedback";
        $allFeedback = mysqli_query($conn, $sqlFeedback);

        echo "<h4>Here are all users' feedback:</h4>";
            echo "<table border='1'>
                        <tr>
                            <th>Feedback ID</th>
                            <th>Customer ID</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                        </tr>
                ";

        $userId = $_SESSION["userId"];
        while($currentFeedback = mysqli_fetch_assoc($allFeedback)){
            
            if($currentFeedback["customerId"] == $userId){
                echo "
                        <tr bgcolor='#151A7B'  style='color:white;'>
                        <td>" . $currentFeedback["id"] . "</td>
                        <td>" . $currentFeedback["customerId"] . "</td>
                        <td>" . $currentFeedback["message"] . "</td>
                        <td>" . $currentFeedback["timestamp"] . "</td>
                    </tr>";
            }else{
            
                    echo "<tr>
                                <td>" . $currentFeedback["id"] . "</td>
                                <td>" . $currentFeedback["customerId"] . "</td>
                                <td>" . $currentFeedback["message"] . "</td>
                                <td>" . $currentFeedback["timestamp"] . "</td>
                            </tr>";
            }
        }
        echo "</table>";

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