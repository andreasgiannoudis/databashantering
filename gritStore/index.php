<?php
require_once "database.php";
require_once "Products.php";
require_once "Customer.php";
require_once "Order.php";
require_once "Order_item.php";

$connection = getDatabaseConnection();

$query = "SELECT p.*, m.photo_path FROM products p LEFT JOIN media m ON p.photo = m.id";

$result = $connection->query($query);
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$shippingMethodsQuery = "SELECT * FROM shipping";
$result = $connection->query($shippingMethodsQuery);

$shippingMethods = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shippingMethods[] = $row;
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grit store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .product-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            margin: 10px;
            padding: 10px;
            width: 200px;
            text-align: center;
            display: inline-block;
        }

        img {
            width: 80px;
            height: 80px;
            height: auto;
        }

        h3 {
            margin: 10px 0;
        }

        input[type="checkbox"] {
            margin: 10px 0;
        }

        form {
            text-align: center;
        }
    </style>
</head>

<body>



    <form action="checkout.php" method="post">

        <!-- rabattkod -->
        <label for="discount_code">Skriv rabattkod:</label>
        <input type="text" id="discount_code" name="discount_code" placeholder="Ange rabattkod för aktivering">
        <input type="hidden" name="discount_id" id="discount_id" value="">
        <button type="submit" name="apply_discount">Aktivera rabatten</button>

        <!-- fraktalternativ -->
        <label for="shipping_method">Välj fraktalternativ:</label>
        <select id="shipping_method" name="shipping_method" required>
            <?php foreach ($shippingMethods as $method) : ?>
                <option value="<?php echo $method['id']; ?>"><?php echo $method['shipping_method']; ?></option>
            <?php endforeach; ?>
        </select>




        <!-- visar produkter -->
        <h2>Produkter</h2>
        <?php


        if (isset($_GET['error'])) {
            $errorMessages = $_GET['error'];
            foreach ($errorMessages as $errorMessage) {
                echo "<p style='color: red;'>$errorMessage</p>";
            }
        }

        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo "<p style='color: green;'>Grattis! Din beställning har genomfört.</p>";
        } ?>

        <?php foreach ($products as $product) : ?>
    <div class="product-card">
        <h3><?php echo $product['product_name']; ?></h3>
        <img src="<?php echo $product['photo_path']; ?>" alt="<?php echo $product['product_name']; ?>">
        <p><?php echo $product['product_description']; ?></p>
        <p>Pris: <?php echo $product['price']; ?>kr</p>
        <label for="quantity_<?php echo $product['id']; ?>">Antal:</label>
        <input type="number" name="product_quantity[<?php echo $product['id']; ?>]" id="quantity_<?php echo $product['id']; ?>" value="1" min="1">
        <input type="checkbox" name="selected_products[]" value="<?php echo $product['id']; ?>">
    </div>
<?php endforeach; ?>


        <h2>Skriv dina uppgifter</h2>
        <label for="name">Förnamn:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="lastname">Efternamn:</label>
        <input type="text" id="lastname" name="lastname" required><br>

        <label for="personnumber">Person nummer:</label>
        <input type="text" id="personnumber" name="personnumber" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="tel">Tel:</label>
        <input type="tel" id="tel" name="tel" required><br>

        <label for="address">Adress:</label>
        <input type="text" id="address" name="address" required><br>

        <label for="city">Stad:</label>
        <input type="text" id="city" name="city" required><br>

        <label for="postcode">Post kod:</label>
        <input type="text" id="postcode" name="postcode" required><br>


        <input type="submit" value="Lägg beställning">
    </form>



</body>

</html>