<?php

namespace gritStore;

require_once "database.php";
require_once "Customer.php";
require_once "Order.php";
require_once "Order_item.php";
require_once "Products.php";

$connection = getDatabaseConnection();
createTablesIfNotExist($connection);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['status'])) {
        $orderIdToUpdate = $_POST['update_status_order'];
        $newStatus = $_POST['status'];
        updateOrderStatus($connection, $orderIdToUpdate, $newStatus);
    } elseif (isset($_POST['delete_order'])) {
        $orderIdToDelete = $_POST['delete_order'];
        deleteOrder($connection, $orderIdToDelete);
    }
}

if (isset($_POST['set_shipping'])) {
    $shippingMethod = $_POST['shipping_method'];
    $shippingCost = $_POST['shipping_cost'];
    setShipping($connection, $shippingMethod, $shippingCost);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_discount'])) {
    $discountCode = $_POST['discount_code'];
    $discountValue = $_POST['discount_value'];
    setDiscountCode($connection, $discountCode, $discountValue);
}


function setShipping($connection, $shippingMethod, $shippingCost)
{
    $insertShippingQuery = "INSERT INTO shipping (shipping_method, shipping_cost) VALUES (?, ?)";
    $stmtInsertShipping = $connection->prepare($insertShippingQuery);
    $stmtInsertShipping->bind_param("sd", $shippingMethod, $shippingCost);
    $stmtInsertShipping->execute();

    if ($stmtInsertShipping->affected_rows > 0) {
        echo "Shipping method set successfully.";
    } else {
        echo "Error setting shipping method.";
    }
}

function setDiscountCode($connection, $discountCode, $discountValue)
{
    $updateDiscountCodeQuery = "INSERT INTO discount_codes (code, discount_value) VALUES (?, ?)";
    $stmtUpdateDiscountCode = $connection->prepare($updateDiscountCodeQuery);
    $stmtUpdateDiscountCode->bind_param("sd", $discountCode, $discountValue);
    $stmtUpdateDiscountCode->execute();

    if ($stmtUpdateDiscountCode->affected_rows > 0) {
        echo "Discount code set successfully.";
    } else {
        echo "Error setting discount code.";
    }
}

$orders = Order::getOrdersFromDatabase($connection);

function deleteOrder($connection, $orderId)
{
    $deleteOrderItemsQuery = "DELETE FROM order_items WHERE order_id = ?";
    $stmtDeleteOrderItems = $connection->prepare($deleteOrderItemsQuery);
    $stmtDeleteOrderItems->bind_param("i", $orderId);
    $stmtDeleteOrderItems->execute();

    $deleteOrderQuery = "DELETE FROM orders WHERE id = ?";
    $stmtDeleteOrder = $connection->prepare($deleteOrderQuery);
    $stmtDeleteOrder->bind_param("i", $orderId);
    $stmtDeleteOrder->execute();

    if ($stmtDeleteOrder->affected_rows > 0) {
        echo "Order togs bort.";
    } else {
        echo "Fel vid radering av ordern";
    }
}

function updateOrderStatus($connection, $orderId, $newStatus)
{
    $updateStatusQuery = "UPDATE orders SET status = ? WHERE id = ?";
    $stmtUpdateStatus = $connection->prepare($updateStatusQuery);
    $stmtUpdateStatus->bind_param("si", $newStatus, $orderId);
    $stmtUpdateStatus->execute();

    if ($stmtUpdateStatus->affected_rows > 0) {
        echo "Order status uppdaterades.";
    } else {
        echo "Fel vid uppdatering av order status";
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .order-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .order-details {
            margin-bottom: 20px;
            border: 1px solid black;
            padding: 15px;
            border-radius: 5px;
        }

        .order-details h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .customer-info {
            margin-bottom: 15px;
        }

        .customer-info p {
            margin: 5px 0;
        }

        .order-items {
            list-style-type: none;
            padding: 0;
        }

        .order-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .order-meta {
            margin-top: 15px;
        }

        .order-meta p {
            margin: 5px 0;
        }

        .status-form,
        .delete-form {
            margin-top: 15px;
        }

        .order-meta {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .order-meta p {
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <h2>Aktivera rabattkod</h2>
    <form method="post">
        <label for="discount_code">Sätt rabattkod: </label>
        <input type="text" id="discount_code" name="discount_code" placeholder="ange rabattkod" required>
        <input type="text" name="discount_value" id="discount_value" placeholder="ange rabatt i kr" required>
        <button type="submit" name="set_discount">Sätt rabatt</button>
    </form>

    <h2>Sätt Fraktmetod</h2>
    <form method="post">
        <label for="shipping_method">Sätt fraktmetod: </label>
        <input type="text" id="shipping_method" name="shipping_method" placeholder="Ange fraktmetod" required>
        <input type="text" name="shipping_cost" id="shipping_cost" placeholder="Ange fraktpris" required>
        <button type="submit" name="set_shipping">Sätt frakt</button>
    </form>


    <h2>Beställningar</h2>

    <div class="order-container">
        <?php foreach ($orders as $order) : ?>
            <?php $customer = Customer::getCustomerInfo($connection, $order->getCustomerId()); ?>
            <div class='order-details'>
                <h2>Order ID: <?php echo $order->getId(); ?></h2>
                <p><strong>Order skapad:</strong> <?php echo $order->getCreated(); ?></p>
                <h4><?php echo $order->getStatus(); ?></h4>

                <?php if ($customer) : ?>
                    <div class='customer-info'>
                        <p><strong>Namn:</strong> <?php echo $customer->getFirstName() . " " . $customer->getLastName(); ?></p>
                        <p><strong>Email:</strong> <?php echo $customer->getEmail(); ?></p>
                        <p><strong>Adress:</strong> <?php echo $customer->getAddress() . ", " . $customer->getCity() . ", " . $customer->getPostNumber() ?> </p>

                    </div>

                    <ul class='order-items'>
                        <?php foreach ($order->getOrderItems() as $orderItem) : ?>
                            <?php $product = Product::getProductInfo($connection, $orderItem->getProductId()); ?>
                            <li class='order-item'>
                                <?php if ($product) : ?>
                                    <p><strong>Produkt:</strong> <?php echo $product->getProductName() . " (". $product->getSku() . ") X" . $orderItem->getQuantity() ?></p>
                                    <p><strong>Pris:</strong> <?php echo $orderItem->getPrice(); ?> kr/st</p>
                                    <p><strong>Totalt Belopp:</strong> <?php echo $orderItem->getTotalPrice() * $orderItem->getQuantity(); ?> kr</p>

                                <?php else : ?>
                                    <p>Produktinformation saknas för orderpost med ID: <?php echo $orderItem->getProductId(); ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <p><strong>Fraktmetod:</strong> <?php echo getShippingMethodNameFromId($connection, $order->getShippingId()); ?></p>

                    <?php
                    $discountId = $order->getDiscountCodeId();

                    if (!empty($discountId)) {
                        $discountCode = getDiscountCodeFromId($connection, $discountId);
                        $discountValue = getDiscountValueFromCode($connection, $discountCode);

                        if (!empty($discountCode)) :
                    ?>
                            <p><strong>Rabattkod:</strong> <?php echo $discountCode; ?></p>
                            <p><strong>Rabattvärde:</strong> -<?php echo $discountValue; ?> kr</p>
                        <?php else : ?>
                            <p><strong>Rabattkod:</strong> Ingen rabatt</p>
                    <?php
                        endif;
                    } else {
                        echo "<p><strong>Rabattkod:</strong> Ingen rabatt</p>";
                    }
                    ?>

                    <p><strong>Total Orderbelopp:</strong> <?php echo $order->getTotalPrice(); ?> varav <?php echo getShippingCostFromId($connection, $order->getShippingId()) ?> kr är frakt</p>







                    <!-- change the status -->
                    <form method="post" class="status-form">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="Processing" <?php echo ($order->getStatus() === 'Processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="Shipped" <?php echo ($order->getStatus() === 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Delivered" <?php echo ($order->getStatus() === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="Canceled" <?php echo ($order->getStatus() === 'Canceled') ? 'selected' : ''; ?>>Canceled</option>
                        </select>
                        <input type="hidden" name="update_status_order" value="<?php echo $order->getId(); ?>">
                        <button type="submit">Update Status</button>
                    </form>


                    <!-- delete button -->
                    <form method="post" class="delete-form">
                        <input type="hidden" name="delete_order" value="<?php echo $order->getId(); ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this order?')">Delete Order</button>
                    </form>
                <?php else : ?>
                    <p>Kundinformation saknas för order ID: <?php echo $order->getId(); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>


<?php

function getDiscountValueFromCode($connection, $discountCode)
{
    $selectDiscountQuery = "SELECT discount_value FROM discount_codes WHERE code = ?";
    $stmtDiscount = $connection->prepare($selectDiscountQuery);
    $stmtDiscount->bind_param("s", $discountCode);
    $stmtDiscount->execute();

    $result = $stmtDiscount->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $discountValue = $row['discount_value'];
        return $discountValue;
    }

    return null;
}

function getDiscountCodeFromId($connection, $discountId)
{
    $selectDiscountCodeQuery = "SELECT code FROM discount_codes WHERE id = ?";
    $stmtDiscountCode = $connection->prepare($selectDiscountCodeQuery);
    $stmtDiscountCode->bind_param("i", $discountId);
    $stmtDiscountCode->execute();

    $result = $stmtDiscountCode->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['code'];
    }

    return null;
}



function getShippingMethodNameFromId($connection, $shippingId)
{
    if ($shippingId === null) {
        // Handle the case where shipping ID is null
        return null;
    }

    $selectShippingNameQuery = "SELECT shipping_method FROM shipping WHERE id = ?";
    $stmtShippingName = $connection->prepare($selectShippingNameQuery);

    if (!$stmtShippingName) {
        // Handle the case where the query preparation fails
        error_log('Query preparation failed: ' . $connection->error);
        return null;
    }

    $stmtShippingName->bind_param("i", $shippingId);
    $stmtShippingName->execute();
    $result = $stmtShippingName->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['shipping_method'];
    } else {
        error_log('No shipping method name found for ID: ' . $shippingId);
        return null;
    }
}


function getShippingCostFromId($connection, $shippingId)
{
    if ($shippingId === null) {
        return null;
    }

    $selectShippingQuery = "SELECT shipping_cost FROM shipping WHERE id = ?";
    $stmtShipping = $connection->prepare($selectShippingQuery);

    if (!$stmtShipping) {
        error_log('Query preparation failed: ' . $connection->error);
        return null;
    }

    $stmtShipping->bind_param("i", $shippingId);
    $stmtShipping->execute();
    $result = $stmtShipping->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $shippingCost = $row['shipping_cost'];

        if (is_numeric($shippingCost)) {
            return (float)$shippingCost;
        } else {
            error_log('Shipping cost is not a number: ' . $shippingCost);
            return null;
        }
    } else {
        error_log('No shipping cost found for ID: ' . $shippingId);
        return null;
    }
}
