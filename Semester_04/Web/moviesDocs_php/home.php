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
    <title>Movies & Docs</title>
</head>
<body>
    <h2>Welcome back, <?php echo $_SESSION["username"] ?> !</h2>

    <h2>Add new Doc</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        Document Name:<br>
        <input type="text" name="docName"><br>
        Document Content:<br>
        <input type="text" name="docContent"><br>
        <input type="submit" name="addNewDoc" value="Add">
    </form>
</body>
</html>

<?php 
    if(isset($_POST["addNewDoc"]) && isset($_POST["docName"]) && isset($_POST["docContent"])){
        $docName = $_POST["docName"];
        $docContent = $_POST["docContent"];

        $sqlAuthor = "SELECT * FROM authors";
        $allAuthors = mysqli_query($conn, $sqlAuthor);

        while($currentAuthor = mysqli_fetch_assoc($allAuthors)){
            if($currentAuthor["id"] == $_SESSION["userId"]){
                $docList = $currentAuthor["documentList"];
                $docArr = explode(',', $docList);
                array_push($docArr, $docName);
                $docStr = implode(',', $docArr);

                $userId = $_SESSION['userId'];
                $sqlUpdateDoc = "UPDATE authors SET documentList='$docStr' WHERE id='$userId'";
                mysqli_query($conn, $sqlUpdateDoc);
            }
        }

        $sqlInsertDoc = "INSERT INTO documents (name, contents) VALUES ('$docName', '$docContent')";
        if (mysqli_query($conn, $sqlInsertDoc)){
            echo"Doc inserted!";
        }
        else{
            echo"Doc NOT inserted!";
        }
    }
?>





<h2>Display Movies & Docs Interleaved</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    <input type="submit" name="seeAllMoviesDocs" value="Display">
</form>

<?php 
    if(isset($_POST["seeAllMoviesDocs"])){
        $userId = $_SESSION["userId"];
        $sqlAuthor2 = "SELECT * FROM authors WHERE id='$userId'";
        $allAuthors2 = mysqli_query($conn, $sqlAuthor2);

        $currentAuthor2 = mysqli_fetch_assoc($allAuthors2);
        $currentMovieList = $currentAuthor2["movieList"];
         $currMovieArr = explode(',', $currentMovieList);
        $currentDocList = $currentAuthor2["documentList"];
         $currDocArr = explode(',', $currentDocList);

        $numberOfDocs = count($currDocArr);
        $numberOfMovies = count($currMovieArr);
        $i=0;
        $j=0;
        while($numberOfDocs > $i && $numberOfMovies > $j){
            echo $currDocArr[$i] . "<br>" . $currMovieArr[$j] . "<br>"; 
            $i++;
            $j++;
        }

        while($numberOfDocs > $i){
             echo $currDocArr[$i] . "<br>";
             $i++;
        }

        while($numberOfMovies > $j){
             echo $currMovieArr[$j] . "<br>";
             $j++;
        }
    }
?>



<h2>Display Doc with largest amount of authors</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    <input type="submit" name="seeLargestDoc" value="Display">
</form>

<?php 
    if(isset($_POST["seeLargestDoc"])){
        $userId = $_SESSION["userId"];
        $sqlAuthor3 = "SELECT * FROM authors";
        $allAuthors3 = mysqli_query($conn, $sqlAuthor3);

        $docAuthors = [];

        while($currentAuthor3 = mysqli_fetch_assoc($allAuthors3)){
            $currentDocListL = $currentAuthor3["documentList"];
            $currDocArrL = explode(',', $currentDocListL);

            foreach($currDocArrL as $sampleDoc){
                if(!isset($docAuthors[$sampleDoc])){
                    $docAuthors[$sampleDoc] = 0;
                }
                else{
                    $docAuthors[$sampleDoc]++;
                }
            }
        }

        $maxKey = null;
        $maxVal = -1;
        foreach($docAuthors as $key => $value){
            if($value > $maxVal){
                $maxVal = $value;
                $maxKey = $key;
            }
        }

        echo $maxKey . "<br>";
    }
?>



<h2>Delete Movie</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        Movie title:<br>
        <input type="text" name="movieName"><br>
        <input type="submit" name="deleteMovie" value="Delete">
</form>

<?php 
    if(isset($_POST["deleteMovie"]) && isset($_POST["movieName"])){
        $movieName = $_POST["movieName"];
        
        $sqlAuthor1 = "SELECT * FROM authors WHERE movieList LIKE '%$movieName%'";
        $allAuthors1 = mysqli_query($conn, $sqlAuthor1);

        while($currentAuthor1 = mysqli_fetch_assoc($allAuthors1)){
            if($currentAuthor1["id"] == $_SESSION["userId"]){
                $movieList = $currentAuthor1["movieList"];
                $movieList = str_replace("$movieName", "", $movieList); 

                $userId = $_SESSION['userId'];
                $sqlUpdateMovie = "UPDATE authors SET movieList='$movieList' WHERE id='$userId'";
                mysqli_query($conn, $sqlUpdateMovie);
            }
        }
    }
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