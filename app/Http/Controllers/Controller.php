<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function get_error_from_validation($error_data)
    {
        $temp_array = [];
        $error_length = count($error_data);
        
        for ($i=0; $i <$error_length ; $i++) { 
            $field = $this->get_string_between($error_data[$i],"The "," field");
            if ( preg_match('/\s/',$field) ){
                $field = str_replace(' ', '_', $field);
            }
            $arrayName = array('message' => $error_data[$i],
                                'field' =>  $field);
            array_push($temp_array,$arrayName);
        } 
        return $temp_array;
    }

    public function get_string_between($string, $start, $end){
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);   
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }
}
