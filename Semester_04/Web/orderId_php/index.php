<?php
session_start();
include("database.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Orders</title>
</head>

<body>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <h2>Welcome to Order!</h2>
    Username: <br>
    <input type="text" name="username"><br>
    <input type="submit" name="submit" value="login">
</form>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($username)) {
        echo "Username is required!";
    } else {
        $sql = "SELECT * FROM User WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);
        $currentUser = mysqli_fetch_assoc($result);

        if ($currentUser) {
            $_SESSION["userId"] = $currentUser["id"];
            $_SESSION["username"] = $username;
            header("location:home.php");
            exit();
        } else {
            echo "User not found!";
        }
    }
}

if (isset($_POST["logout"])) {
    session_destroy();
    header("location:index.php");
}
mysqli_close($conn);
?>