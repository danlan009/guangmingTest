<?php
namespace App\Service;
use Log;
use Cache; 
class StatService{
    

    public static function getImg($dir="products",$id,$type){
        $image_path = env('IMAGE_PATH');
        switch($dir){
            case 'products':
                $file = $dir."/".$id."/".$id."_".$type.".jpg";
                
            break;
            case 'subjects': 
                $file = $dir."/".$id."_$type".".jpg";
            break;
        }
        $md5 = Cache::get('API_IMG_MD5_'.$file);
        if(isset($md5)){
            $file .= "?v=$md5";
        }

        return "$image_path/$file";
    }
}
?>