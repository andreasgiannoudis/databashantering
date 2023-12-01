<?php
require_once "database.php";
require_once "Products.php";
require_once "Customer.php";
require_once "Order.php";
require_once "Order_item.php";

$connection = getDatabaseConnection();
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedProducts = isset($_POST['selected_products']) ? $_POST['selected_products'] : [];
    $productQuantities = isset($_POST['product_quantity']) ? $_POST['product_quantity'] : [];
  
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $personnumber = $_POST['personnumber'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postcode = $_POST['postcode'];
    $discountCode = $_POST['discount_code'];
    $shipping_method = $_POST['shipping_method']; //it has the id of the shipping method

    $validationErrors = [];

    if (empty($name) || empty($lastname) || empty($personnumber) || empty($email) || empty($tel) || empty($address) || empty($city) || empty($postcode) || empty($selectedProducts)) {
        $validationErrors[] = "Fyll i alla uppgifter. Välj minst en produkt.";
    }

    $discountValue = null;
    $discountId = null;

    if (!empty($discountCode)) {
        $discountValue = getDiscountValueFromCode($connection, $discountCode);
        $discountId = getDiscountIdFromCode($connection, $discountCode);

        if (!$discountId) {
            $validationErrors[] = "Invalid discount code.";
        }
    }
  
    if (!empty($validationErrors)) {
        $errorQueryString = http_build_query(['error' => $validationErrors]);
        header("Location: index.php?$errorQueryString");
        exit();
    } 
    
    $customer = new gritStore\Customer(null, $name, $lastname, $email, $tel, $address, $city, $postcode, $personnumber);
    $customerId = $customer->saveToDatabase($connection);

    $discountId = !empty($discountId) ? $discountId : null;

    $order = new gritStore\Order(null, $customerId, date('Y-m-d H:i:s'), "Processing", 0, $discountId, $shipping_method, $connection);

    foreach ($selectedProducts as $productId) {
        $product = getProductById($connection, $productId);
        $quantity = isset($productQuantities[$productId]) ? intval($productQuantities[$productId]) : 1;
    
        if ($product) {
            $orderItem = new gritStore\Order_item(null, null, $product["price"], $product["price"], $productId, $quantity);
            $order->setTotalPrice($order->getTotalPrice() + ($product["price"] * $quantity));
            $order->addOrderItem($orderItem, $productId);
        }
    }

    if (!empty($discountValue)) {
        $order->setTotalPrice($order->getTotalPrice() - $discountValue);
        $order->setDiscountCode($discountCode);
        $order->setDiscountCodeId($discountId);
    }

    if (!empty($shipping_method)) {
        $shippingCost = getShippingCostFromId($connection, $shipping_method);

        if ($shippingCost !== null) {
            $order->setTotalPrice($order->getTotalPrice() + $shippingCost);
            $order->setShippingId($shipping_method); 
            $order->setShippingMethod(getShippingMethodNameFromId($connection, $shipping_method));
        }
    }

    $order->saveToDatabase($connection);
    header("Location: index.php?success=1");
    exit;
}
  
  function getProductById($connection, $productId) {
    $selectProductQuery = "SELECT * FROM products WHERE id = ?";
    $stmtProduct = $connection->prepare($selectProductQuery);
    $stmtProduct->bind_param("i", $productId);
    $stmtProduct->execute();
    $result = $stmtProduct->get_result();
  
    if ($result->num_rows > 0) {
      $product = $result->fetch_assoc();
      return $product;
    }
  
    return null;
  }

function getDiscountValueFromCode($connection, $discountCode) {
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


function getDiscountIdFromCode($connection, $discountCode) {
  $selectDiscountQuery = "SELECT id FROM discount_codes WHERE code = ?";
  $stmtDiscount = $connection->prepare($selectDiscountQuery);
  $stmtDiscount->bind_param("s", $discountCode);
  $stmtDiscount->execute();

  $result = $stmtDiscount->get_result();

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $discountId = $row['id'];

      if (is_numeric($discountId)) {
          return $discountId;
      }
  }

  return null;
}


function getShippingCostFromId($connection, $shippingId) {
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
          return $shippingCost;
      } else {
          error_log('Shipping cost is not a number: ' . $shippingCost);
          return null;
      }
  } else {
      error_log('No shipping cost found for ID: ' . $shippingId);
      return null;
  }
}




function getShippingCostFromCode($connection, $shippingMethod)
{
    $selectShippingQuery = "SELECT shipping_cost FROM shipping WHERE shipping_method = ?";
    $stmtShipping = $connection->prepare($selectShippingQuery);
    $stmtShipping->bind_param("s", $shippingMethod);
    $stmtShipping->execute();

    $result = $stmtShipping->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $shippingCost = $row['shipping_cost'];

        if (is_numeric($shippingCost)) { 
            return $shippingCost;
        }
    }

    return null;
}

function getShippingIdFromCode($connection, $shippingMethod)
{
    $selectShippingIdQuery = "SELECT id FROM shipping WHERE shipping_method = ?";
    $stmtShippingId = $connection->prepare($selectShippingIdQuery);
    $stmtShippingId->bind_param("s", $shippingMethod);
    $stmtShippingId->execute();

    $result = $stmtShippingId->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $shippingId = $row['id'];

        if (is_numeric($shippingId)) {
            return $shippingId;
        }
    }

    return null;
}


function getShippingMethodNameFromId($connection, $shippingId) {
  if ($shippingId === null) {
      return null;
  }

  $selectShippingNameQuery = "SELECT shipping_method FROM shipping WHERE id = ?";
  $stmtShippingName = $connection->prepare($selectShippingNameQuery);

  if (!$stmtShippingName) {
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

