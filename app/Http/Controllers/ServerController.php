<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
// use Log;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\News;
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
                            $this->answerSupply();
                            // return $news;
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
        $imgUrl = 'http://image.baidu.com/search/detail?ct=503316480&z=0&ipn=d&word=%E9%85%8D%E9%80%81%E5%91%98&step_word=&hs=0&pn=1&spn=0&di=113141975100&pi=0&rn=1&tn=baiduimagedetail&is=0%2C0&istype=2&ie=utf-8&oe=utf-8&in=&cl=2&lm=-1&st=-1&cs=131123587%2C1773344591&os=1062641244%2C777409784&simid=4039780477%2C431041049&adpicid=0&lpn=0&ln=1961&fr=&fmq=1498719847733_R&fm=result&ic=0&s=undefined&se=&sme=&tab=0&width=&height=&face=undefined&ist=&jit=&cg=&bdtype=0&oriquery=&objurl=http%3A%2F%2Fimages.quanjing.com%2Fbld010%2Fhigh%2Fbld113349.jpg&fromurl=ippr_z2C%24qAzdH3FAzdH3Fooo_z%26e3Bq7wg3tg2_z%26e3Bv54AzdH3Fp5rtvAzdH3F90aaa_z%26e3Bip4s&gsm=0&rpstart=0&rpnum=0';
        $news = new News([
                'title'       => '开始补货',
                'description' => '现在可以开始 '.$date.' 的补货!',
                'url'         => $url,
                'image'       => $imgUrl,
            ]);
        // Log::debug('answerSupply returns:'.json_encode($news));
        return $news;
    }
}
