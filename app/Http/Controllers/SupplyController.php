<?php
 
namespace App\Http\Controllers; 

use Illuminate\Http\Request; 
use App\Lib\Bussiness; 
use Log; 
use DB;  
use App\Service\MallService.php
class SupplyController extends Controller
{
    // 补货控制器   
    public function test(){
        $mallService = new MallService();
        $proList = $mallService->showPros('0081008','book');
        dd($proList);
    }
}
