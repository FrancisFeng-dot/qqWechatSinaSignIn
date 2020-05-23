
//微博回调    
public function weibocallback(){
    vendor('Sinaweibo.Saetv2');

    //url中不能缺少code
    $o = new \SaeTOAuthV2( config('sina_weibo_login_config.WB_AKEY') , config('sina_weibo_login_config.WB_SKEY') );
    if (isset($_REQUEST['code'])) {
        $keys = array();
        $keys['code'] = $_REQUEST['code'];
        $keys['redirect_uri'] = config('sina_weibo_login_config.WB_CALLBACK_URL');
        try {
            //生成access token
            $token = $o -> getAccessToken('code', $keys);
        } catch (OAuthException $e) {
            //echo 1;die;
        }
    }
     
    if(!isset($token)){
        header("location: https://www.udparty.com/");
        exit;
    }

    if ($token) {
        $c = new \SaeTClientV2( config('sina_weibo_login_config.WB_AKEY') , 
        config('sina_weibo_login_config.WB_SKEY') , $token['access_token'] );
        $ms  = $c->home_timeline(); // done
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];
        $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
        // p($user_message);die;//到这一步可以获取用户的一些信息，以下逻辑自行扩展
        $data = array(
            'weibonickname'=> $user_message['name'],
            'weiboid'=> $user_message['id'],
            'weiboimgurl'=> $user_message['profile_image_url'],
            'nickname'=> $user_message['name'],
            'headimg'=> $user_message['profile_image_url'],
            'usertype'=>1,
            'logtype'=>2,
            'logintime'=>time()
        );

        $user = Db::name('user')->where(array('weiboid'=>$data['weiboid']))->find();
        if($user){//用户存在
            Db::name('user')->where(array('userid'=>$user['userid']))->update($data);
            setonline($user);
            Session::set('user',$user);
            header("location: https://www.udparty.com/");
            exit;
        }else{//用户不存在则新建
            //生成一个用户名和密码
            $data['username']='ud_'.random();
            while (db('user')->where(array('username'=>$data['username']))->find()) {
                $data['username']='ud_'.random();
            }
            $data['password']=md5('123456');
            $data['createtime']=time();

            $id=Db::name('user')->insertGetId($data);
            $u=Db::name('user')->where(array('userid'=>$id))->find();
            setonline($u);
            Session::set('user',$u);
            header("location: https://www.udparty.com/");
            exit;    
        }
    }
}