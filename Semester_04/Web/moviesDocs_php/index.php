<?php
include ("database.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login Document Movies</title>
</head>

<body>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    <h2>Welcome to Document Movies!</h2>
    Author: <br>
    <input type="text" name="username"><br>
    Document name: <br>
    <input type="text" name="document"><br>
    Movie name: <br>
    <input type="text" name="movie"><br>
    <input type="submit" name = "submit" value="login">
</form>
</body>
</html>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $document = filter_input(INPUT_POST, "document", FILTER_SANITIZE_SPECIAL_CHARS);
    $movie = filter_input(INPUT_POST, "movie", FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($username) || empty($document) || empty($movie)) {
        echo "Username is required!";
    }
    else {
        $sql = "SELECT * FROM authors WHERE name = '$username' AND (documentList LIKE '%$document%' OR movieList LIKE '%$movie%')";
        try {
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $currentUser = mysqli_fetch_assoc($result);
                echo "Welcome back, $username!";
                session_start();
                $_SESSION["userId"] = $currentUser["id"];
                $_SESSION["username"] = $username;
                header("location:home.php");
            } else {
                echo "Incorrect credentials!";
            }
        } catch (mysqli_sql_exception $e) {
            echo "Databse error: " . $e->getMessage();
        }
    }
}

mysqli_close($conn);
?>