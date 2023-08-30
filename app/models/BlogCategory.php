<?php

namespace App\Models;	
use PDO;

use App\Models\Database\DbConnect;

class BankBalance extends DbConnect
{

    public $id;
    public $balance;


    /**
     * Get all the bank details from the database
     * @param object $conn Connection to the database
     * @return array An associative array of all the article records
     */

    public function getAll()
    {

        // READING FROM THE DATABASE AND CHECKING FOR ERRORS
        $sql = "SELECT * 
                FROM bank_accounts
                ORDER BY id ASC;";

        // Execute the sql statement, returning a result set as a PDOStatement object
        $results = $this->getConn()->query($sql); 

        $data = $results->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }


    public function getDataById(){
       
        $sql = "SELECT * FROM bank_accounts WHERE id = :id"; 

        $stmt = $this->getConn()->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        // Set the default fetch mode for this statement
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'BankBalance');

        $result = $stmt->execute();

        if ($result === true) {
            // Fetches the next row from a result set in an object format
            return $stmt->fetch();
        }
    }



    public function transferMoney()
    {

        // update the data into the database server
        $sql = "UPDATE bank_accounts 
                SET balance = :balance, 
                WHERE id = :id";

        // Prepares the statement for execution
        $stmt = $this->getConn()->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':balance', $this->balance, PDO::PARAM_STR);

        // Executes a PDO prepared statement
        $result = $stmt->execute();

        return $result;
 
    }
}
