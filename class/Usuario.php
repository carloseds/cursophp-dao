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

    public function getActive(){
        return $this->active;
    }

    public function setActive($value){
        $this->active = $value;
    }

    public function loadById($id){

        $sql = new Sql();
        $result = $sql->select("SELECT * FROM USERS WHERE ID = :IDUSER", array(
            ":IDUSER"=>$id
        ));
        
        if( !empty($result) ){

            $this->setData($result);

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

    public static function getAllUsers(){

        $sql = new Sql();

        return $sql->selectAll("SELECT * FROM USERS WHERE ACTIVE = 1 ");

    }

    public static function busca($login){

        $sql = new Sql();

        return $sql->selectAll("SELECT * FROM USERS WHERE LOGIN LIKE :SEARCH ORDER BY ID DESC", array(
            ':SEARCH'=>"%".$login."%"
        ));

    }

    public function login($login,$password){

        $sql = new Sql();
        $result = $sql->select("SELECT * FROM USERS WHERE LOGIN = :USR AND PASSWORD = :PASSWD AND ACTIVE = 1", array(
            ":USR"=>$login 
            ,":PASSWD"=>$password
        ));
        
        if( $result ){

            $this->setData($result);

        } else {

            throw new Exception("usuario ou senhas invalidos");
        }

    }

    public function setData($data){
        
        $this->setIdUser($data['ID']);
        $this->setLogin($data['LOGIN']);
        $this->setPassword($data['PASSWORD']);
        $this->setDataCreate(new DateTime($data['DATA_CREATE']));
        ( !empty($data['DATA_UPDATE']) ) ? $this->setDataUpdate(new DateTime($data['DATA_UPDATE'])) : '';
        $this->setActive($data['ACTIVE']);

    }

    public function newUser(){

        $sql = new Sql();
        $result = $sql->insertReturnId("INSERT INTO USERS (LOGIN,PASSWORD) VALUES (:USR, :PASSWD)", array(
            ":USR"=>$this->getLogin()
            ,":PASSWD"=>$this->getPassword()
        ));
        
        if( !empty($result['ID']) ){

            $this->loadById($result['ID']);

        } else {

            throw new Exception("Erro ao cadastrar novo usuário");
        
        }

    }

    public function updateUser($login,$password){

        $this->setLogin($login);
        $this->setPassword($password);

        $sql = new Sql();
        
        $result = $sql->update("UPDATE USERS SET LOGIN = :LGN, PASSWORD = :PASSWD WHERE ID = :IDUSER AND ACTIVE = :ACT",array(
            ":LGN"=>$this->getLogin()
            ,":PASSWD"=>$this->getPassword()
            ,":IDUSER"=>$this->getIdUser()
            ,":ACT"=>$this->getActive()
        ));

        if( !empty($result['ROWCOUNT']) ){

            $this->loadById($this->getIdUser());

        } else {

            throw new Exception("Erro ao atualizar dados do usuário");
        
        }

    }

    public function enableDisableUser($id_user,$status){

        $sql = new Sql();
        $this->setIdUser($id_user);

        $result = $sql->update("UPDATE USERS SET ACTIVE = :ST WHERE ID = :IDUSER", array(
            ":ST"=>$status
            ,":IDUSER"=>$this->getIdUser()
        ));

        if( !empty($result['ROWCOUNT']) ){

            $this->loadById($this->getIdUser());

        } else {

            throw new Exception("Erro ao excluir usuário");
        
        }

    }

}