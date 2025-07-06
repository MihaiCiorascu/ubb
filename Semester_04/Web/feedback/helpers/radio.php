<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="radio.php" method="post">
        <input type="radio" name="food" value="Pizza">
        Pizza<br>
        <input type="radio" name="food" value="Hamburger">
        Hamburger<br>
        <input type="radio" name="food" value="Hotdog">
        Hotdog<br>
        <input type="radio" name="food" value="Taco">
        Taco<br>
        <input type="submit" name="submit">
    </form>
</body>
</html>

<?php 
    if(isset($_POST["submit"]) && isset($_POST["food"])){
        $food = $_POST["food"];
        echo "You selected '$food'";
    }
?>