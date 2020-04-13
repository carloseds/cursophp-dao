<?php 

class Usuario {

    private $id;
    private $login;
    private $password;
    private $data_create;
    private $data_update;
    private $active;

    public function getIdUser(){
        return $this->id;
    }

    public function setIdUser($value){
        $this->id = $value;
    }

    public function getLogin(){
        return $this->login;
    }

    public function setLogin($value){
        $this->login = $value;
    }

    public function getPassword(){
        return $this->password;
    }

    public function setPassword($value){
        $this->password = $value;
    }

    public function getDataCreate(){
        return $this->data_create;
    }

    public function setDataCreate($value){
        $this->data_create = $value;
    }

    public function getDataUpdate(){
        return $this->data_update;
    }

    public function setDataUpdate($value){
        $this->data_update = $value;
    }

    public function getActive():bool{
        return $this->active;
    }

    public function setActive($value){
        $this->active = $value;
    }

    public function loadById($id){

        $sql = new Sql();
        $result = $sql->select("SELECT * FROM USERS WHERE ID = :IDUSER AND ACTIVE = 1", array(
            ":IDUSER"=>$id
        ));
        
        if( !empty($result) ){

            $row = $result;
            $this->setIdUser($row['ID']);
            $this->setLogin($row['LOGIN']);
            $this->setPassword($row['PASSWORD']);
            $this->setDataCreate(new DateTime($row['DATA_CREATE']));
            ( !empty($row['DATA_UPDATE']) ) ? $this->setDataUpdate(new DateTime($row['DATA_UPDATE'])) : '';
            $this->setActive($row['ACTIVE']);

        }

    }

    public function __toString(){

        return json_encode(array(
            "id"=>$this->getIdUser()
            ,"login"=>$this->getLogin()
            ,"password"=>$this->getPassword()
            ,"data_create"=>$this->getDataCreate()->format('d-m-Y H:i:s')
            ,"data_update"=> ( !empty( $this->getDataUpdate() ) ) ? $this->getDataUpdate()->format('d-m-Y H:i:s') : ''
            ,"active"=>$this->getActive()
        ));

    }

}