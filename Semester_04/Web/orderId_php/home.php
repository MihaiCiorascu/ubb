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
    <title>Order</title>
</head>
<body>
    <h2>Welcome back, <?php echo $_SESSION["username"] ?> !</h2>

    <h2>Show Products</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        <input type="submit" name="seeAllProducts" value="Search">
    </form>
</body>
</html>

<?php 
    if(isset($_POST["seeAllProducts"]) || isset($_POST["select"])) {
        $totalPrice=0;
        $sqlProd = "SELECT * FROM product";
        $allProducts = mysqli_query($conn, $sqlProd);

        echo "<h4>Here are all the Products</h4>";
        echo "<form method='post'>";
        echo "<table border='1'>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Order It</th>
                    </tr>
            ";

        for ($i = 0; $i < mysqli_num_rows($allProducts); $i++) {
            $currentProduct = mysqli_fetch_assoc($allProducts);
            $currentProductId = $currentProduct["id"];

            echo "<tr>
                <td>" . $currentProduct["id"] . "</td>
                <td>" . $currentProduct["name"] . "</td>
                <td>" . $currentProduct["price"] . "</td>
                <td><input type='checkbox' name='check[]' value='". $currentProductId . "'></td>
                </tr>";
        }
        echo "</table>";
        echo "<input type='submit' name='select' value='Order selected items'>";
        echo "</form>";

        if (isset($_POST['select']) && isset($_POST['check'])) {
            $sqlOrderItem = "SELECT * FROM orderitem";
            $allOrderItems = mysqli_query($conn, $sqlOrderItem);

            $selectedProducts = $_POST['check'];
            $numberOfItems = count($selectedProducts);

            $ids = implode(',', array_map('intval', $selectedProducts));
            $sql = "SELECT * FROM product WHERE id IN ($ids)";
            $result = mysqli_query($conn, $sql);

            $totalPrice = 0;
            $categories = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $totalPrice += $row['price'];
                $nameParts = preg_split('/[\s-]/', $row['name'], 2);
                $category = $nameParts[0];
                if (!isset($categories[$category])) {
                    $categories[$category] = 0;
                }
                $categories[$category]++;
            }
            $discount = 0;

            if ($numberOfItems >= 3) {
                $discount += 0.10;
            }

            foreach ($categories as $catCount) {
                if ($catCount >= 2) {
                    $discount += 0.05;
                    break;
                }
            }

            $finalPrice = $totalPrice * (1 - $discount);

            echo "<h4>Order Summary</h4>";
            echo "Total price before discount: $totalPrice<br>";
            echo "Total discount: " . ($discount * 100) . "%<br>";
            echo "Final price: $finalPrice<br>";


            $userId = $_SESSION['userId'];
            $sqlInsertOrder = "INSERT INTO `order` (userId, totalPrice) VALUES ('$userId', '$finalPrice')";
            if (mysqli_query($conn, $sqlInsertOrder)) {
                $orderId = mysqli_insert_id($conn);

                foreach ($selectedProducts as $productId) {
                    $productId = intval($productId);
                    $sqlInsertOrderItem = "INSERT INTO orderitem (orderId, productId) VALUES ('$orderId', '$productId')";
                    mysqli_query($conn, $sqlInsertOrderItem);
                }

                $_SESSION['order_success'] = "Order placed successfully! Order ID: $orderId";
            } else {
                $_SESSION['order_error'] = "Error placing order: " . mysqli_error($conn);
            }



            echo "<h4>Selected Product IDs:</h4>";
            echo "<pre>";
            print_r($_POST['check']);
            echo "</pre>";
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