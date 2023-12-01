<?php 
namespace gritStore;

class Order_item{
    protected $id;
    protected $order_id;
    protected $price;
    protected $total_price;
    protected $productId;
    protected $quantity;


    function __construct($id, $order_id, $price, $total_price, $productId, $quantity)
    {
        $this->id = $id;
        $this->order_id = $order_id;
        $this->price = $price;
        $this->total_price = $total_price ?? $price;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    function getId(){
        return $this->id;
    }

    function setOrderId($order_id) {
        if(empty($order_id) == false){
            $this->order_id = $order_id;
        }
    }
    
    function getOrderId(){
        return $this->order_id;
    }


    function setPrice($price){
        if(empty($price) == false){
            $this->price = $price;
        }
    }
    
    function getPrice(){
        return $this->price;
    }
    
    function setTotalPrice($total_price){   
        if(empty($total_price) == false){
            $this->total_price = $total_price;
        }
    }
    
    function getTotalPrice(){
        return $this->total_price;
    }

    public function setProduct(Product $product) {
        $this->productId = $product;
    }

    public function getProduct() {
        return $this->productId;
    }

    public function setProductId($productId) {
        $this->productId = $productId;
    }

    public function getProductId() {
        return $this->productId;
    }

    function setQuantity($quantity) {
        if(empty($quantity) == false)
        {
            $this->quantity = $quantity;
        }
    }

    function getQuantity(){
        return $this->quantity;
    }


    public static function getOrderItemsForOrder($connection, $orderId) {
        $orderItems = [];
        $selectOrderItemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $connection->prepare($selectOrderItemsQuery);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orderItem = new Order_item($row['id'], $row['order_id'], $row['price'], $row['total_price'], $row['product_id'], $row['quantity']);
                $orderItems[] = $orderItem;
            }
        }

        return $orderItems;
    }
}