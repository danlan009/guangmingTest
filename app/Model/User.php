<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Log;
use App\Model\Order;
class User extends Model
{ 
	protected $table = 'users';
	public $timestamps = false;
	protected $fillable = ['wx_id','wx_name'];
    
    public static function createPassword($wxId){
    	$time = time();
    	$pass = substr(str_shuffle($time),2,8); //生成8位密码
    	$pwds = User::pluck('password')->toArray(); //拉取已存在用户密码,排重
    	if(!empty($pwds)){
            if(!in_array($pass,$pwds)){
                $res = User::where('wx_id',$wxId)->update(['password'=>$pass]);
                Log::debug('User_'.$wxId.' password created---'.$res);
                echo $res?'successful':'fail';
            }else{
               $this->createPassword($wxId); 
            }	
    	}else{
            $res = User::where('wx_id',$wxId)->update(['password'=>$pass]);
            Log::debug('User_'.$wxId.' password created---'.$res);
    	}
    }

    public function saveUser($wxId,$wxName){
    	$wxUser = User::create([
    			'wx_id' => $wxId,
    			'wx_name' => $wxName
    		]);
        return $wxUser;
    }

    // 下单时获取最近一单联系方式
    public static function getPhone($wxId){
        
        $phone = Order::where('wx_id',$wxId)
                            ->orderBy('id','desc')
                            ->take(1)
                            ->value('phone');

        if(empty($phone)){
            $phone = User::where('wx_id',$wxId)->value('phone');
        }

        if(!empty($phone)){
            return $phone;
        }else{
            return null;
        }
    }

    public static function addPhone($wxId,$phone){
        return User::where('wx_id',$wxId)->update(['phone' => $phone]);
    }

    public static function getUserByWxId($wxId){
        return User::where('wx_id',$wxId)->first();

    }
}
