<?php

namespace gritStore;

class Product{
    protected $id;
    protected $product_name;
    protected $product_description;
    protected $photo;
    protected $price;
    protected $stock;
    protected $product_created;
    protected $sku;
    protected $vat;



    function __construct($id, $product_name, $product_description, $photo, $price, $stock, $product_created, $sku, $vat) {
        $this->id = $id;
        $this->product_name = $product_name;
        $this->product_description = $product_description;
        $this->photo = $photo;
        $this->price = $price;
        $this->stock = $stock;
        $this->product_created = $product_created;
        $this->sku = $sku;
        $this->vat = $vat;
    }


    function setId($id){
        if(empty($this->id) == false)
        {
            $this->id = $id;
        }
    }

    function getId(){
        return $this->id;
    }

    function setProductName($product_name){
        if(empty($product_name) == false){
            $this->product_name = $product_name;
        }
    }

    function getProductName(){
        return $this->product_name;
    }

    function setProductDescription($product_description){
        if(empty($product_description) == false){
            $this->product_description = $product_description;
        }
    }

    function getProductDescription(){
        return $this->product_description;
    }

    function setPhoto($photo){
        if(empty($photo) == false){
            $this->photo = $photo;
        }
    }

    function getPhoto(){
        return $this->photo;
    }

    function setPrice($price){
        if(empty($price) == false){
            $this->price = $price;
        }
    }

    function getPrice(){
        return $this->price;
    }

    function setStock($stock){
        if(empty($stock) == false){
            $this->stock = $stock;
        }
    }

    function getStock() {
        return $this->stock;
    }

    function setProductCreated($product_created){
        if(empty($product_created) == false){
            $this->product_created = $product_created;
        }
    }

    function getProductCreated() {
        return $this->product_created;
    }

    function setSku($sku){
        if(empty($sku) == false){
            $this->sku = $sku;
        }
    }

    function getSku() {
        return  $this->sku;
    }

    function setVat($vat){
        if(empty($vat) == false)
            $this->vat = $vat;
    }

    function getVat() {
        return $this->vat;
    }


    public static function getProductInfo($connection, $productId) {
        $selectProductQuery = "SELECT * FROM products WHERE id = ?";
        $stmt = $connection->prepare($selectProductQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $product = new Product($row['id'], $row['product_name'], $row['product_description'], $row['photo'], $row['price'], $row['stock'], $row['product_created'], $row['sku'], $row['vat']);
            return $product;
        }

        return null; //handle non-existent product case
    }
}