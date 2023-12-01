<?php

namespace gritStore;


class Order
{
    protected $id = null;
    protected $customer_id = null;
    protected $created = null;
    protected $status = null;
    protected $total_price = null;
    protected $discount_code = null;
    protected $shipping_method = null;
    protected $discount_code_id = null;
    protected $shipping_id = null;
    protected $connection = null;

    protected $orderItems = [];

    function __construct($id, $customer_id, $created, $status, $total_price, $discount_code_id, $shipping_id, $connection)
    {
        $this->id = $id;
        $this->customer_id = $customer_id;
        $this->created = $created;
        $this->status = $status;
        $this->total_price = $total_price;
        $this->discount_code_id = $discount_code_id;
        $this->shipping_id = $shipping_id;

        $this->connection = $connection;
    }

    public function setDiscountCode($discountCode)
    {
        $this->discount_code = $discountCode;
    }

    public function getDiscountCode()
    {
        return $this->discount_code;
    }

    public function setShippingMethod($shippingMethod)
    {
        $this->shipping_method = $shippingMethod;
    }

    public function getShippingMethod()
    {
        return $this->shipping_method;
    }

    public function getShippingCost()
    {
        $shippingCost = getShippingCostFromCode($this->connection, $this->shipping_method);
        return $shippingCost;
    }

    public function setDiscountCodeId($discountCodeId)
    {
        $this->discount_code_id = $discountCodeId;
    }

    public function getDiscountCodeId()
    {
        return $this->discount_code_id;
    }

    public function setShippingId($shippingId){
        if(empty($shippingId) == false){
            $this->shipping_id = $shippingId;
        }
    }

    public function getShippingId(){
        return $this->shipping_id;
    }


    public function addOrderItem(Order_item $orderItem, $productId)
    {
        $orderItem->setProductId($productId);
        $this->orderItems[] = $orderItem;
        $orderItem->setOrderId($this->id);
    }

    public function getOrderItems()
    {
        return $this->orderItems;
    }




    public function calculateTotalPrice()
{
    $totalPrice = 0;

    foreach ($this->orderItems as $orderItem) {
        $totalPrice += $orderItem->getTotalPrice();
    }

    if (isset($this->shipping_id)) {
        $shippingCost = getShippingCostFromId($this->connection, $this->shipping_id);
        if ($shippingCost !== null) {
            $totalPrice += $shippingCost;
        }
    }

    if (!empty($this->discount_code)) {
        $discountValue = getDiscountValueFromCode($this->connection, $this->discount_code);
        if ($discountValue !== null) {
            $totalPrice -= $discountValue;
        }
    }

    $this->setTotalPrice($totalPrice);

    return $totalPrice;
}





    public function saveToDatabase($connection)
    {
        $insertOrderQuery = "INSERT INTO orders (customer_id, created, status, total_price, discount_code_id, shipping_id)
                                   VALUES (?, ?, ?, ?, ?, ?)";
        $stmtOrder = $connection->prepare($insertOrderQuery);
        $stmtOrder->bind_param("issdii", $this->customer_id, $this->created, $this->status, $this->total_price, $this->discount_code_id, $this->shipping_id); 
        $stmtOrder->execute();

        $orderId = $stmtOrder->insert_id;

        foreach ($this->orderItems as $orderItem) {
            $itemPrice = $orderItem->getPrice();
            $itemTotalPrice = $orderItem->getTotalPrice();

            $insertOrderItemQuery = "INSERT INTO order_items (order_id, price, total_price, product_id, quantity) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmtOrderItem = $connection->prepare($insertOrderItemQuery);
            $stmtOrderItem->bind_param("iddii", $orderId, $itemPrice, $itemTotalPrice, $orderItem->getProductId(), $orderItem->getQuantity());
            $stmtOrderItem->execute();
        }
    }





    function setId($id)
    {
        if (empty($this->id) == false) {
            $this->id = $id;
        }
    }



    public function getId()
    {
        return $this->id;
    }

    public function setCustomerId($customer_id)
    {
        if (empty($customer_id) == false) {
            $this->customer_id = $customer_id;
        }
    }

    public function getCustomerId()
    {
        return $this->customer_id;
    }

    public function setCreated($created)
    {
        if (empty($created) == false) {
            $this->created = $created;
        }
    }

    public function getCreated()
    {
        return $this->created;
    }

    function setStatus($status)
    {
        if (empty($status) == false) {
            $this->status = $status;
        }
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setTotalPrice($total_price)
    {
        if (empty($total_price) == false) {
            $this->total_price = $total_price;
        }
    }

    public function getTotalPrice()
    {
        return $this->total_price;
    }



    public static function getOrdersFromDatabase($connection)
    {
        $orders = [];
        $selectOrdersQuery = "SELECT * FROM orders ORDER BY created DESC";

        $result = $connection->query($selectOrdersQuery);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $order = new Order($row['id'], $row['customer_id'], $row['created'], $row['status'], $row['total_price'], $row['discount_code_id'], $row['shipping_id'], $connection);

                $orderItems = Order_Item::getOrderItemsForOrder($order->connection, $row['id']);

                foreach ($orderItems as $orderItem) {
                    $order->addOrderItem($orderItem, $orderItem->getProductId());
                }

                $orders[] = $order;
            }
        }

        return $orders;
    }
}
