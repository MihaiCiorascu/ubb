<?php
session_start();
include("database.php");

if (!isset($_SESSION["username"])) {
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        <label>Secret Question: <?php echo $_SESSION['secretQuestion'] ?></label><br>
        <input type="text" name="secretAnswer"><br>
        <input type="submit" name = "submit" value="login">
    </form>
</body>
</html>

<?php 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $secretAnswer = $_POST["secretAnswer"];
        if ($secretAnswer == $_SESSION["secretAnswer"]){
            header("location:home.php");
        }
        else{
            header("location:index.php");
        }
    }
?>

<?php
if (isset($_POST["logout"])) {
    session_destroy();
    header("location:index.php");
}
mysqli_close($conn);
?>