<?php 
namespace gritStore;

class Customer{

    protected $id;
    protected $firstname;
    protected $lastname;
    protected $email;
    protected $tel;
    protected $address;
    protected $city;
    protected $postnumber;
    protected $personummer;

    function __construct($id, $firstname, $lastname, $email, $personummer, $city, $address, $tel, $postnumber)
    {
        $this->id = $id; 
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->personummer = $personummer;
        $this->city = $city;
        $this->address = $address;  
        $this->tel = $tel;
        $this->postnumber = $postnumber;
        
    }

    function getId(){
        return $this->id;
    }

    function setLastname($lastname){
        if(empty($lastname) == false){
            $this->lastname = $lastname;
        }
    }
    function getLastname(){
        return $this->lastname;
    }
    function setFirstname($firstname){
        if(empty($firstname) == false){
            $this->firstname = $firstname;
        }
    }
    function getFirstname(){
        return $this->firstname;
    }
    function setEmail($email){
        if(empty($email) == false){
            $this->email = $email;
        }
    }
    function getEmail(){
        return $this->email;
    }
    function setTel($tel){
        if(empty($tel) == false){
            $this->tel = $tel;
        }
    }
    function getTel(){
        return $this->tel;
    }

    function setAddress($address){
        if(empty($address) == false){
            $this->address = $address;
        }
    }

    function getAddress(){
        return $this->address;
    }

    function setCity($city){
        if(empty($city) == false){
            $this->city = $city;
        }
    }
    function getCity(){
        return $this->city;
    }

    function setPostNumber($postNumber){
        if(empty($postNumber) == false){
            $this->postnumber = $postNumber;
        }
    }

    function getPostNumber(){
        return $this->postnumber;
    }

    function setPersonummer($personnummer){
        if(empty($personnummer) == false){
            $this->personummer = $personnummer;
        }
    }

    function getPersonnumer(){
        return $this->personummer;
    }




    public function saveToDatabase($connection) {
        $checkCustomerIfExistsByEmailQuery = "SELECT id FROM customers WHERE email = ?";
        $statement = $connection->prepare($checkCustomerIfExistsByEmailQuery);
        $statement->bind_param("s", $this->email);
        $statement->execute();
    
        $result = $statement->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $customerId = $row['id'];
            $statement->close();
            return $customerId;
        }
    
        $insertCustomerQuery = "INSERT INTO customers (firstname, lastname, email, personummer, city, address, tel, postnumber) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtCustomer = $connection->prepare($insertCustomerQuery);
        $stmtCustomer->bind_param("sssissss", $this->firstname, $this->lastname, $this->email, $this->personummer, $this->city, $this->address, $this->tel, $this->postnumber);
        $stmtCustomer->execute();
    
        $customerId = $stmtCustomer->insert_id;
    
        $stmtCustomer->close();
    
        return $customerId;
    }


    public static function getCustomerInfo($connection, $customerId) {
        $selectCustomerQuery = "SELECT * FROM customers WHERE id = ?";
        $stmt = $connection->prepare($selectCustomerQuery);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $customer = new Customer($row['id'], $row['firstname'], $row['lastname'], $row['email'], $row['personummer'], $row['city'], $row['address'], $row['tel'], $row['postnumber']);
            return $customer;
        }

        return null; 
    }
}
