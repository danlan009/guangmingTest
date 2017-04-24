<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class QrCodeController extends Controller
{
	// 生成售货机张贴二维码(包含售货机id)
    public function create(Request $request){
    	$vmId = $request->input('vmId');
    	$url = env('UBOX_HOST').'/mall/show_pros?vmId='.$vmId;
    	QrCode::format('png')
    				->size(300)
    				->generate($url,public_path('qrcodes/'.$vmId.'.png'));
    }
}
