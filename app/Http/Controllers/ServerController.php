<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Log;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
class ServerController extends Controller
{
    public function index(Application $app){
    	Log::debug('Weixin message come in!');
    	$server = $app->server;
    	$server->setMessageHandler(function($message){
    		switch (strtolower($message->MsgType)) {
                    case 'text':
                        if(trim($message->Content) === '开始补货'){
                            $openid = $message->FromUserName;
                            Log::debug($openid.' wants to start_supplyment!');
                            // 身份验证
                            if($this->authentication($openid)){
                                return $this->answerSupply();
                                
                            }
                        }else if(trim($message->Content) === '补货员注册'){
                            $openid = $message->FromUserName;
                            Log::debug($openid.' wants to register become sendor!');
                            // 检测是否已经注册
                            if($this->authentication($openid)){
                                return '不可重复注册!';
                            }
                            return $this->answerRegister();

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
        // $imgUrl = '';
        $news = new News([
                'title'       => '开始补货',
                'description' => '现在可以开始 '.$date.' 的补货!',
                'url'         => $url,
                'image'       => ''
            ]);
        Log::debug('answerSupply returns:'.json_encode($news));
        return $news;
        
    }

    public function answerRegister(){
        $date = date('Y-m-d');
        $url = env('UBOX_TEST_HOST').'supply/register';
        // $imgUrl = '';
        $news = new News([
                'title'       => '注册成为补货员!',
                'description' => '请点击进入注册申请页面!',
                'url'         => $url,
                'image'       => ''
            ]);
        Log::debug('answerRegister returns:'.json_encode($news));
        return $news;
        
    }

    public function authentication($openid){
        $json = json_decode(file_get_contents('sources/'.env('JSON_PATH').'/sender.json'),true);
        Log::debug('ServerController-authentication---data from json file returns:'.json_encode($json));
        $openids = array_pluck($json,'openid');
        if(in_array($openid,$openids)){
            return 1;
            
        }
    }

    public function 
}
