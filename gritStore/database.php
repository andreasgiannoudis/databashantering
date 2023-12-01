<?php
require_once "confidential.php";
function getDatabaseConnection()
{
    $connection = new mysqli(HOST, USERNAME, PASSWORD, DATABASE, PORT);
    
    //check if connection was successful or not
    if($connection->connect_error != null){
        die("Anslutning misslyckades: ". $connection->connect_error);
    }
    else{
        echo "Anslutningen lyckades<br>";
        return $connection;
    }
}


function createTablesIfNotExist($connection){
    $createProductsTableQuery = "
    CREATE TABLE IF NOT EXISTS products (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        product_name varchar(50) DEFAULT NULL,
        product_description varchar(50) DEFAULT NULL,
        photo varchar(250) DEFAULT NULL,
        price float DEFAULT NULL,
        stock int DEFAULT NULL,
        product_created timestamp NULL DEFAULT NULL,
        sku varchar(50) DEFAULT NULL,
        vat int DEFAULT NULL
        )";
    $connection->query($createProductsTableQuery);

    $createMediaTableQuery = "
    CREATE TABLE IF NOT EXISTS media (
        id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        photo_path varchar(250) DEFAULT NULL
      )";
    $connection->query($createMediaTableQuery);


    $createOrdersTableQuery = "
    CREATE TABLE IF NOT EXISTS `orders` (
        `id` int NOT NULL AUTO_INCREMENT,
        `customer_id` int NOT NULL,
        `total_price` float DEFAULT NULL,
        `created` timestamp NULL DEFAULT NULL,
        `status` varchar(20) DEFAULT 'Processing',
        `discount_code_id` int DEFAULT NULL,
        `shipping_id` int DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `fk_discount_code_id` (`discount_code_id`),
        KEY `shipping_id` (`shipping_id`),
        KEY `customer_id` (`customer_id`),
        CONSTRAINT `fk_discount_code_id` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`),
        CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`shipping_id`) REFERENCES `shipping` (`id`),
        CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
      )";
    $connection->query($createOrdersTableQuery);


    $createOrderItemsTableQuery = "
    CREATE TABLE IF NOT EXISTS `order_items` (
        `id` int NOT NULL AUTO_INCREMENT,
        `order_id` int DEFAULT NULL,
        `price` float DEFAULT NULL,
        `total_price` float DEFAULT NULL,
        `product_id` int DEFAULT NULL,
        `quantity` int DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
        CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
      )";


    $connection->query($createOrderItemsTableQuery);

    $createCustomersTableQuery = "
    CREATE TABLE IF NOT EXISTS `customers` (
        `id` int NOT NULL AUTO_INCREMENT,
        `firstname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        `lastname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        `personummer` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        `address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        `tel` varchar(15) DEFAULT NULL,
        `postnumber` int NOT NULL,
        PRIMARY KEY (`id`)
      )";


    $connection->query($createCustomersTableQuery);


    $createDiscountTableQuery = "
    CREATE TABLE IF NOT EXISTS `discount_codes` (
        `id` int NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL,
        `discount_value` int NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
      )";


    $connection->query($createDiscountTableQuery);


    $createShippingTableQuery = "
    CREATE TABLE IF NOT EXISTS `shipping` (
        `id` int NOT NULL AUTO_INCREMENT,
        `shipping_method` varchar(50) DEFAULT NULL,
        `shipping_cost` int NOT NULL,
        PRIMARY KEY (`id`)
      )";


    $connection->query($createShippingTableQuery);



}
