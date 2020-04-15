<?php 

class Funcoes {

    public static function lowerCase($string){

        if( !empty($string) ){

            $result = mb_convert_case($string, MB_CASE_LOWER ,'UTF-8');
            return $result;

        }

    }

    public static function upperCase($string){
        
        if( !empty($string) ){

            $result = mb_convert_case($string, MB_CASE_UPPER ,'UTF-8');
            return $result;

        }

    }

    public static function firstUpperCase($string){

        if( !empty($string) ){

            $result = mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
            return $result;
            
        }

    }

    public static function fileDownload($link,$path){

        if( !empty($link) && !empty($path) ){
            
            $options = [
                "ssl"=> [
                "cafile" => "/Applications/XAMPP/xamppfiles/share/curl/curl-ca-bundle.crt",
                "verify_peer"=> true,
                "verify_peer_name"=> true,
                ],
            ]; 

            $content = file_get_contents($link, false, stream_context_create($options));
            
            $parse = parse_url($link);
            $basename = time()."-".basename($parse["path"]);
            
            $file = fopen($basename, "w+");

            fwrite($file, $content);
            fclose($file);

            if( !is_dir($path) ) mkdir($path);
            $finalpath =  $path . $basename; 

            if( file_exists($basename) ){
                
                if( rename($basename,$finalpath) ){

                    return $finalpath;
    
                } else {
                    
                    return false;
                }

            } else {
                
                return false;

            }
            
        }

    }

    public static function viaCep($cep){
        
        $data = ["erro" => 1];
        $cep = Funcoes::justNumbers($cep);

        if( !empty($cep) ){

            $link = "https://viacep.com.br/ws/$cep/json/";
        
            $ch = curl_init($link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);

            curl_close($ch);

            $data = json_decode($response, true);

        }

        return $data;

    }

    public static function justNumbers($string){

        if( !empty($string) ){

            return preg_replace("/[^0-9]/", "", $string);

        }

    }

    public static function setCookie($cookie_name, $array_data, $timeStamp){

        if( !empty($cookie_name) && !empty($array_data) && !empty($timeStamp) ){

            if( setcookie($cookie_name, json_encode($array_data), $timeStamp) ){
                
                return true;

            } else {

                return false;
            }

        } else {

            return false;

        }

    }

    public static function setLog($filename,$title ,$path, $content){

        if( !empty($filename) && !empty($path) && !empty($content) ){

            if( !is_dir($path) ) mkdir($path);
            
            $finalpath =  $path . $filename; 

            if($file = fopen($finalpath, "a+")){

                fwrite( $file, $title." -> ".date("d-m-Y H:i:s") . " -> " . json_encode($content)."\r\n");
                fclose($file);

                return $finalpath;

            } else {

                return false;
            }

        } else {

            return false;
        }

    }

}