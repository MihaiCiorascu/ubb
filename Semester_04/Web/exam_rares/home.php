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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Home</title>
</head>
<body>
<h2>Welcome back, <?php echo $_SESSION["username"] ?> !</h2>

<h2>Search available rooms</h2>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Enter CheckIn Date:<br>
    <input type="date" name="checkInDate"><br>
    Enter CheckOut Date:<br>
    <input type="date" name="checkOutDate"><br>
    <input type="submit" name="seeAvailableRooms" value="Search">
</form>

<?php 
    if(isset($_POST["seeAvailableRooms"])) {
        $currentCheckInDate = $_POST["checkInDate"];
        $currentCheckOutDate = $_POST["checkOutDate"];

        $sqlRooms = "SELECT * FROM HotelRoom";
        $allRooms = mysqli_query($conn, $sqlRooms);

        $availableRooms = array();

        echo "<h4>Here are all the available rooms in the specified time period:</h4>";
        echo "<table border='1'>
                    <tr>
                        <th>Room ID</th>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Base Price</th>
                    </tr>
            ";

        for ($i = 0; $i < mysqli_num_rows($allRooms); $i++) {
            $currentRoom = mysqli_fetch_assoc($allRooms);
            $available = true;
            $sqlReservations = "SELECT * FROM Reservation";
            $allReservations = mysqli_query($conn, $sqlReservations);

            for ($j = 0; $j < mysqli_num_rows($allReservations); $j++) {
                $currentReservation = mysqli_fetch_assoc($allReservations);
                if($currentReservation["roomId"] == $currentRoom["id"] &&
                    $currentCheckInDate < $currentReservation["checkOutDate"] &&
                    $currentCheckOutDate > $currentReservation["checkInDate"])
                {
                    $available = false;
                }    
            }

            if ($available) {
                $availableRooms[] = $currentRoom["id"];
                echo "<tr>
                    <td>" . $currentRoom["id"] . "</td>
                    <td>" . $currentRoom["roomNumber"] . "</td>
                    <td>" . $currentRoom["capacity"] . "</td>
                    <td>" . $currentRoom["basePrice"] . "</td>
                  </tr>";
            }
        }
        echo "</table>";
        $_SESSION["availableRooms"] = $availableRooms;
    }
?>


<h2>Book a room (see before available rooms)</h2>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Enter CheckIn Date:<br>
    <input type="date" name="firstDate"><br>
    Enter CheckOut Date:<br>
    <input type="date" name="secondDate"><br>
    Enter Room Number:<br>
    <input type="text" name="toReserveRoom"><br>
    Enter Number of guests:<br>
    <input type="text" name="enterNumberOfGuests"><br>
    <input type="submit" name="bookRoom" value="book">
</form>

<?php 
    if(isset($_POST["bookRoom"])){
        $roomToReserve = $_POST["toReserveRoom"];
        if (!in_array($roomToReserve, $_SESSION["availableRooms"])){
            echo 'The room you selected is not available in that period of time!';
        }
        else{
            $firstDateBook = $_POST["firstDate"];
            $secondDateBook = $_POST["secondDate"];
            $numberOfGuests = $_POST["enterNumberOfGuests"];

            //if user has already a reservation in that time
            $hasReservation = false;
            $totalPrice = 0;
            $sqlReservations = "SELECT * FROM Reservation";
            $allReservations = mysqli_query($conn, $sqlReservations);
            for ($j = 0; $j < mysqli_num_rows($allReservations); $j++) {
                $currentReservation = mysqli_fetch_assoc($allReservations);
                if($currentReservation["userId"] == $_SESSION["userId"] &&
                    $firstDateBook < $currentReservation["checkOutDate"] &&
                    $secondDateBook > $currentReservation["checkInDate"])
                {
                    $hasReservation = true;
                }
            }
            if ($hasReservation)
                echo 'Already have a reservation at that dates';
            else{   
                $toReserveRoomSql = "SELECT * FROM HotelRoom WHERE id = '$roomToReserve'";
                $toReserveRoomResult = mysqli_query($conn, $toReserveRoomSql);
                $toReserveRoom = mysqli_fetch_assoc($toReserveRoomResult);

                $freeRooms = count($_SESSION["availableRooms"]);
                $sqlRooms = "SELECT * FROM HotelRoom";
                $allRooms = mysqli_query($conn, $sqlRooms);
                $totalRooms = mysqli_num_rows($allRooms);

                $numberOfReservations = $totalRooms - $freeRooms;

                if($numberOfReservations < 0.5 * $totalRooms)
                {
                    $totalPrice = $toReserveRoom["basePrice"];
                }
                elseif($numberOfReservations > 0.5 * $totalRooms && $numberOfReservations < 0.8 * $totalRooms){
                    $totalPrice = $toReserveRoom["basePrice"] + $toReserveRoom["basePrice"] * 0.2;
                }
                elseif($numberOfReservations > 0.8 * $totalRooms){
                    $totalPrice = $toReserveRoom["basePrice"] + $toReserveRoom["basePrice"] * 0.5;
                }

                $userId = $_SESSION["userId"];
                $reservationQuery = "INSERT INTO Reservation (userId, roomId, checkInDate, checkOutDate, numberOfGuests, totalPrice)
                                VALUES ($userId, $roomToReserve, '$firstDateBook', '$secondDateBook', '$numberOfGuests', $totalPrice)";

                if (mysqli_query($conn, $reservationQuery)) {
                    echo 'Your reservation was created succesfully!';
                } else {
                    echo "Error creating reservation: " . mysqli_error($conn) . "<br>";
                }
            }
            
        }
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    }
?>



<h2>The number of guests at a given date</h2>
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
    Enter Date:<br>
    <input type="date" name="guestsInThatDay"><br>
    <input type="submit" name="seeGuestsInDay" value="Submit">
</form>

<?php 
    if(isset($_POST["seeGuestsInDay"])) {
        $dateToSee = $_POST["guestsInThatDay"];
        $totalGuests = 0;
        $sqlReservations = "SELECT * FROM Reservation";
        $allReservations = mysqli_query($conn, $sqlReservations);
        for ($j = 0; $j < mysqli_num_rows($allReservations); $j++) {
            $currentReservation = mysqli_fetch_assoc($allReservations);
            if($dateToSee >= $currentReservation["checkInDate"] && $dateToSee <= $currentReservation["checkOutDate"]){
                $totalGuests++;
            }
        }
        echo $totalGuests;
    }
?>


<h2>All your current reservations: </h2>

<?php
    echo "<table border='1'>
                <tr>
                    <th>Reservation ID</th>
                    <th>Room ID</th>
                    <th>Check In Date</th>
                    <th>Check Out Date</th>
                    <th>Number of Guests</th>
                    <th>Total Price</th>
                </tr>
        ";
    $sqlReservations = "SELECT * FROM Reservation";
    $allReservations = mysqli_query($conn, $sqlReservations);
    for ($j = 0; $j < mysqli_num_rows($allReservations); $j++) {
        $currentReservation = mysqli_fetch_assoc($allReservations);
        if ($_SESSION["userId"] == $currentReservation["userId"]){
            echo "<tr>
                    <td>" . $currentReservation["id"] . "</td>
                    <td>" . $currentReservation["roomId"] . "</td>
                    <td>" . $currentReservation["checkInDate"] . "</td>
                    <td>" . $currentReservation["checkOutDate"] . "</td>
                    <td>" . $currentReservation["numberOfGuests"] . "</td>
                    <td>" . $currentReservation["totalPrice"] . "</td>
                  </tr>";
        }
    }
    echo "</table>";
?>

<form action="home.php" method="post">
    <input type="submit" name="logout" value="logout">
</form>

<?php
if (isset($_POST["logout"])) {
    session_destroy();
    header("location:index.php");
}
mysqli_close($conn);
?>


</body>
</html>