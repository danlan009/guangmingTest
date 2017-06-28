<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use EasyWeChat\Foundation\Application;
use Log;
class ServerController extends Controller
{
    public function index(Application $app){
    	Log::debug('Weixin message come in!');
    	$server = $app->server;
    	$server->setMessageHandler(function($message){
    		// switch ($message->MsgType) {
    		//         case 'event':
    		//             return '收到事件消息';
    		//             break;
    		//         case 'text':
    		//             return '收到文字消息';
    		//             break;
    		//         case 'image':
    		//             return '收到图片消息';
    		//             break;
    		//         case 'voice':
    		//             return '收到语音消息';
    		//             break;
    		//         case 'video':
    		//             return '收到视频消息';
    		//             break;
    		//         case 'location':
    		//             return '收到坐标消息';
    		//             break;
    		//         case 'link':
    		//             return '收到链接消息';
    		//             break;
    		//         // ... 其它消息
    		//         default:
    		//             return '收到其它消息';
    		//             break;
    		//     }
    		return 'hello';
    	});

    	Log::debug('Message has returned to Weixin!');
    	$response = $server->serve();

    	return $response;
    }

    public function createMenu(Application $app){
    	$buttons = [
		    			[
		    				'type' => 'view',
		    				'name' => '预定',
		    				'url' => env('UBOX_TEST_HOST').'/wx/vmlist?f=mp'
		    			],

		    			[
		    				'type' => 'view',
		    				'name' => '购买',
		    				'url' => env('UBOX_TEST_HOST').'/wx/buy?f=mp'
		    			],

		    			[
		    				'name' => '我的订单',
		    				'sub_button' => [
		    					[
		    						'type' => 'view',
				    				'name' => '我的订单',
				    				'url' => env('UBOX_TEST_HOST').'/wx/orders?f=mp'
		    					],
		    					[
		    						'type' => 'view',
		    						'name' => '客服申请',
		    						'url' => env('UBOX_TEST_HOST').'/wx/vmlist?f=mp'
		    					]
		    				]
		    			]
    			
    	];

    	$menu = $app->menu;
    	$menu->add($buttons);
    }
}
