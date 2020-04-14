<?php 

class Sql extends PDO {

    private $conn;

    public function __construct(){
        
        global $config;

        try {
            $this->conn = new PDO("mysql:dbname=".$config['dbname'].';host='.$config['host'], $config['dbuser'], $config['dbpass']);
            //$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } 
        catch(PDOException $e){
            $msgError = "Error DB: " . $e->getMessage();
        }

    }

    private function setParams($statement, $parameters = array() ){

        foreach( $parameters as $key => $value){

            $this->setParam($statement, $key, $value);

        }

    }

    private function setParam($statement, $key, $value){

        $statement->bindParam($key, $value);

    }

    public function query($rawQuery, $params = array() ){

        $stmt = $this->conn->prepare($rawQuery);
        
        $this->setParams($stmt, $params);
        
        $stmt->execute();

        return $stmt;

    }

    public function select($rawQuery, $params = array() ){

        $stmt = $this->query($rawQuery, $params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    public function selectAll($rawQuery, $params = array() ){

        $stmt = $this->query($rawQuery, $params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function insertReturnId( $rawQuery, $params = array() ){

        $arrayReturn = [
            "ROWCOUNT" => NULL
            ,"ID" => NULL
        ];

        $stmt = $this->query($rawQuery, $params);
        
        if( $stmt->rowCount() > 0 ){

            $last_id = $this->conn->lastInsertId();
            $rowCount = $stmt->rowCount();

            $arrayReturn = [
                "ROWCOUNT" =>$rowCount
                ,"ID" =>$last_id
            ];
        }

        return $arrayReturn;

    }

    public function update( $rawQuery, $params = array() ){

        $arrayReturn = [
            "ROWCOUNT" => NULL
        ];

        $stmt = $this->query($rawQuery, $params);
        
        if( $stmt->rowCount() > 0 ){

            $rowCount = $stmt->rowCount();

            $arrayReturn = [
                "ROWCOUNT" =>$rowCount
            ];
        }

        return $arrayReturn;

    }

}