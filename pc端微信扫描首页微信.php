
//pc端微信扫描首页微信
public function pcwecallback(){ 
    $code = $_GET['code']; 
    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxa005e01435dc15c8&secret=7a7ac2776452afb5ac8c785cabb13760&code='.$code.'&grant_type=authorization_code';
    if($code){ 
        //获取accestoken
        $jsoninfo = getcurl($url);
        if($jsoninfo['access_token']){
            //获取用户信息
            $getuserinfourl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$jsoninfo['access_token'].'&openid='.$jsoninfo['openid'];
            $userinfo = getcurl($getuserinfourl);
            $data = array(
                'wenickname'=> $userinfo['nickname'],
                'weimg'=> $userinfo['headimgurl'],
                'nickname'=> $userinfo['nickname'],
                'headimg'=> $userinfo['headimgurl'],
                'weopenid'=> $userinfo['openid'], 
                'usertype'=>1,
                'logtype'=>2,
                'logintime'=>time()
                );
        }
        $user = Db::name('user')->where(array('weopenid'=>$data['weopenid']))->find();
        if($user){
        //如果有这个人；设置登录状态，直接跳转到后台页面；
            Db::name('user')->where(array('userid'=>$user['userid']))->update($data);
            setonline($user);
            Session::set('user',$user);
            header("location: https://www.udparty.com/");
            exit;
        }else{
        //没有这个用户，跳转到
            //header("location: http://www.ysjianzhu.com/login.html?tap=3&nickname=".$data['pcwenickname']);
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