<?php
session_start();

if (!isset($_SESSION["search_counts"])) {
    $_SESSION["search_counts"] = [];
}

include("database.php");

if (!isset($_SESSION["username"])) {
    header("location: index.php");
    exit();
}

// Get current user ID once
$currentUserId = $_SESSION["userId"];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Welcome back, <?php echo $_SESSION["username"] ?> !</h2>

    <h2>Search Properties by part of description</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        <input type="text" name="partOfDescr"><br>
        <input type="submit" name="seePropertiesByDescr" value="Search">
    </form>
</body>
</html>

<?php 
    if(isset($_POST["seePropertiesByDescr"])) {
        $inputText = $_POST["partOfDescr"];

        $sqlProp = "SELECT * FROM property WHERE description LIKE '$inputText%'";
        $allProperties = mysqli_query($conn, $sqlProp);

        echo "<h4>Here are all the Properties that match your input descr:</h4>";
        echo "<table border='1'>
                    <tr>
                        <th>Property ID</th>
                        <th>Adress</th>
                        <th>Description</th>
                    </tr>
            ";

        for ($i = 0; $i < mysqli_num_rows($allProperties); $i++) {
            $currentProperty = mysqli_fetch_assoc($allProperties);
            $currentPropertyId0 = $currentProperty["id"];

            if (!isset($_SESSION["search_counts"][$currentPropertyId0])) {
                $_SESSION["search_counts"][$currentPropertyId0] = 1;
            } else {
                $_SESSION["search_counts"][$currentPropertyId0]++;
            }
                echo "<tr>
                    <td>" . $currentProperty["id"] . "</td>
                    <td>" . $currentProperty["address"] . "</td>
                    <td>" . $currentProperty["description"] . "</td>
                  </tr>";
        }
        echo "</table>";
    }
?>


<h2>Add Property</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Enter address:<br>
    <input type="text" name="addAddress"><br>
    Enter description:<br>
    <input type="text" name="addDescription"><br>
    <input type="submit" name="addProperty" value="Add">
</form>

<?php 
    if(isset($_POST["addProperty"]) && isset($_POST["addAddress"]) && isset($_POST["addDescription"])){
        $addressToAdd = $_POST["addAddress"];
        $descriptionToAdd = $_POST["addDescription"];

        $sqlProp = "SELECT * FROM property WHERE description = '$descriptionToAdd' AND address = '$addressToAdd'";
        $allProperties = mysqli_query($conn, $sqlProp);

        $numberOfMatches = mysqli_num_rows($allProperties);

        if($numberOfMatches > 0){
            for ($i = 0; $i < mysqli_num_rows($allProperties); $i++) {
                $currentProperty = mysqli_fetch_assoc($allProperties);
                $currentPropertyId = $currentProperty["id"];
                $sqlUserToPropQuery = "INSERT INTO usertoproperties (idUser, idProperty) VALUES ($currentUserId, $currentPropertyId)";
                
                if (mysqli_query($conn, $sqlUserToPropQuery)) {
                    echo 'New propoerty added for current user!';
                } else {
                    echo "Error adding property: " . mysqli_error($conn) . "<br>";
                }

            }
        }
        else{
            $sqlPropQuery = "INSERT INTO property (address, description) VALUES ('$addressToAdd', '$descriptionToAdd')";

            if (mysqli_query($conn, $sqlPropQuery)) {
                    echo "New property added!<br>";
                } else {
                    echo "Error adding property: " . mysqli_error($conn) . "<br>";
                }

            $sqlProp1 = "SELECT * FROM property WHERE description = '$descriptionToAdd' AND address = '$addressToAdd'";
            $allProperties1 = mysqli_query($conn, $sqlProp1);

            for ($i = 0; $i < mysqli_num_rows($allProperties1); $i++) {
                $currentProperty1 = mysqli_fetch_assoc($allProperties1);
                $currentPropertyId1 = $currentProperty1["id"];
                $sqlUserToPropQuery1 = "INSERT INTO usertoproperties (idUser, idProperty) VALUES ('$currentUserId', '$currentPropertyId1')";

                if (mysqli_query($conn, $sqlUserToPropQuery1)) {
                    echo 'New property added for current user!<br>';
                } else {
                    echo "Error adding property: " . mysqli_error($conn) . "<br>";
                }
            }

        }
    }
?>

<h2>Delete Property</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Enter property Id:<br>
    <input type="text" name="deleteId"><br>
    <input type="submit" name="deleteProperty" value="Delete">
</form>

<?php 
    if(isset($_POST["deleteProperty"]) && isset($_POST["deleteId"])){
        $idToDelete = $_POST["deleteId"];

        $sqlPropDelete = "SELECT * FROM property WHERE id = '$idToDelete'";
        $allPropertiesDelete = mysqli_query($conn, $sqlPropDelete);

        $numberOfMatches = mysqli_num_rows($allPropertiesDelete);

        if($numberOfMatches > 0){
            $sqlUserPropDelete = "SELECT * FROM usertoproperties WHERE idProperty = '$idToDelete'";
            $allUserPropertiesDelete = mysqli_query($conn, $sqlUserPropDelete);

            $numberOfMatches1 = mysqli_num_rows($allUserPropertiesDelete);

            if($numberOfMatches1 > 1){
                    $sqlUserToPropQuery = "DELETE FROM usertoproperties WHERE idUser = '$currentUserId' AND idProperty = '$idToDelete'";
                    
                    if (mysqli_query($conn, $sqlUserToPropQuery)) {
                        echo "Relation for current user deleted!<br>";
                    } else {
                        echo "Error adding property: " . mysqli_error($conn) . "<br>";
                    }
            }
            else{
                $sqlUserToPropQuery2 = "DELETE FROM usertoproperties WHERE idProperty = '$idToDelete'";
                    
                if (mysqli_query($conn, $sqlUserToPropQuery2)) {
                    echo "Relation for current user deleted!<br>";
                } else {
                    echo "Error adding property: " . mysqli_error($conn) . "<br>";
                }

                $sqlPropQueryD = "DELETE FROM property WHERE id = '$idToDelete'";
                    
                    if (mysqli_query($conn, $sqlPropQueryD)) {
                        echo "Property deleted!<br>";
                    } else {
                        echo "Error adding property: " . mysqli_error($conn) . "<br>";
                    }
                    
            }
        }
        else{
            echo "No existing id!";
        }
    }
?>

<h2>View properties with more than 1 owner: </h2>

<?php
echo "<table border='1'>
        <tr>
            <th>Property ID</th>
            <th>Address</th>
            <th>Description</th>
        </tr>";

$sqlProperties = "SELECT * FROM property";
$allPropertiesView = mysqli_query($conn, $sqlProperties);

while ($currentPropertyV = mysqli_fetch_assoc($allPropertiesView)) {
    $currentPropertyVId = $currentPropertyV["id"];

    $sqlUserToProperties = "SELECT COUNT(*) as count FROM usertoproperties WHERE idProperty = $currentPropertyVId";
    $result = mysqli_query($conn, $sqlUserToProperties);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] > 1) {
        echo "<tr>
                <td>" . $currentPropertyV["id"] . "</td>
                <td>" . $currentPropertyV["address"] . "</td>
                <td>" . $currentPropertyV["description"] . "</td>
              </tr>";
    }
}

echo "</table>";
?>

<?php 
    if (!empty($_SESSION["search_counts"])) {
        $ids = array_keys($_SESSION["search_counts"], max($_SESSION["search_counts"]));
        $mostSearchedId = (int)$ids[0];
        $sql = "SELECT * FROM property WHERE id = $mostSearchedId";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            echo "<h2>Most searched property (this session):</h2>";
            echo "<p>ID: " . $row["id"] . "<br>";
            echo "Address: " . $row["address"] . "<br>";
            echo "Description: " . $row["description"] . "<br>";
            echo "Search count: " . $_SESSION["search_counts"][$mostSearchedId] . "</p>";
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