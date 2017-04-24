<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

use App\Model\User; 

class UserController extends Controller
{
    public function createPassword(Request $request){
    	$wxId = $request->input('wxId');
    	User::createPassword($wxId);
    }

    public function saveUser(Request $request){
    	$wxId = $request->wxId;
    	$wxName = $request->wxName;
    	$wxUser = new User();
    	return $wxUser->saveUser($wxId,$wxName);
    }
}
