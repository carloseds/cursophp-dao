<?php 
//jwt.io
class Jwt {

    private $secret;

    public function __construct(){
        $this->secret = "qwertY";
    }

    public function create($data){

        $header = json_encode(array(
            "typ" => "JWT"
            ,"alg" => "HS256"
        ));
        
        $payload = json_encode($data);

        $hbase = $this->base64url_encode($header);
        $pbase = $this->base64url_encode($payload);

        $hash = 
        $signature = hash_hmac("sha256", $hbase.".".$pbase, $this->secret, true);
        $base64_signature = $this->base64url_encode($signature);

        $jwt = $hbase.".".$pbase.".".$base64_signature;

        return $jwt;

    }

    private function base64url_encode($data){

        if( !empty($data) ){

            return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');

        }

    }

    private function base64url_decode($data){

        if( !empty($data) ){

            return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));

        }

    }

    public function validateJwt($token){

        $jwt_split = explode('.', $token);
        
        if(  isset($jwt_split[2]) && !isset($jwt_split[3]) ){

            $signature = hash_hmac("sha256", $jwt_split[0].".".$jwt_split[1], $this->secret, true);
            $base64_signature = $this->base64url_encode($signature);

            if( $base64_signature == $jwt_split[2] ){

                $arrayReturn = json_decode( $this->base64url_decode($jwt_split[1]) );

                return $arrayReturn;

            } else {

                return false;
            }

        } else {

            return false;

        }

    }

}