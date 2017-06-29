<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\News;
use Log;
class ServerController extends Controller
{
    public function index(Application $app){
    	Log::debug('Weixin message come in!');
    	$server = $app->server;
    	$server->setMessageHandler(function($message){
    		switch (strtolower($message->MsgType)) {
                    case 'text':
                        if(trim($message->Content) === '开始补货'){
                            Log::debug('case - if---');
                            // return "gm.dev.uboxol.com/supply/start_supplyment";
                            return $this->answerSupply();
                            
                        }
                        break;
                    
                    
                    default:
                        return 'welcome!';
                        break;
                }
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

    public function answerSupply(){
        $date = date('Y-m-d');
        $url = env('UBOX_TEST_HOST').'supply/start_supplyment';
        $img = env('UBOX_TEST_HOST').''
        $news = new News([
                'title'       => '开始补货',
                'description' => '现在可以开始 '.$date.' 的补货!',
                'url'         => $url,
                'image'       => '',
            ]);
        return $news;
    }
}
