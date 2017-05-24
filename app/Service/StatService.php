<?php
namespace App\Service;
use Log;
use Cache; 
class StatService{
    
 
    public static function getImg($dir="products",$id,$type){
        $image_path = env('UBOX_TEST_HOST').'/'.env('IMAGE_PATH'); //需要修改,上线时更改到指定服务器图片目录
        switch($dir){
            case 'products':
                $file = $dir."/".$id."/".$id."_".$type.".jpg";
                
            break;
            case 'subjects': 
                $file = $dir."/".$id."_$type".".jpg";
            break;
        }
        Log::debug('StatService --- api_img_md5---returns---'.'API_IMG_MD5_'.$image_path.'/'.$file);
        $md5 = Cache::get('API_IMG_MD5_'.$image_path.'/'.$file);
        if(isset($md5)){
            $file .= "?v=$md5";
        }
        // Log::debug('statService getImg returns---'."$image_path/$file");
        return "$image_path/$file";
    }
}
?>