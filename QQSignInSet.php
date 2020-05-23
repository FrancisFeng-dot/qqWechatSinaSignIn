//qq登录回调   详细步骤全部在这里；走通了；
public function qqcallback(){ 
$code = $_GET['code'];

// 1、获取code以后，去拿accesstoken；
$url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=101424156&client_secret=d8ba9204b726d75e8ec4bdc0cc6c54c6&code=".$code."&redirect_uri=http%3A%2F%2Fwww.udparty.com%2FIndex.php%2Findex%2Fqqcallback"; 
$re = getcurlstr($url);

//2、截取token    
//$re如下access_token=26865394E123C02F61BCF858977EA0C4&
//expires_in=7776000&refresh_token=B3B48A03A6F8B36CFB9FD8419D0E940D
$arr = explode('&',$re);
$tkarr = explode('=',$arr[0]);
$accesstoken = $tkarr[1];

//3、获取OPenID：callback( {"client_id":"101271490","openid":"3399F56A8493218069B0CD8F27EC5874"} ); 
$uurl = 'https://graph.qq.com/oauth2.0/me?access_token='.$accesstoken;
$opidstr = getcurlstr($uurl); 

//4、截取只要openid
$b = explode('(',$opidstr);
$c = explode(')',$b[1]);
$oidobj = json_decode($c[0], true); 
$openid = $oidobj['openid'];

//5、获取个人信息
$curl = 'https://graph.qq.com/user/get_user_info?access_token='.$accesstoken.'&oauth_consumer_key=101424156&openid='.$openid;
$userifo = getcurl($curl);
    $data = array(
      'qqnickname'=>$userifo['nickname'],
      'nickname'=>$userifo['nickname'],
      'qqimg'=>$userifo['figureurl_qq_2'],
      'headimg'=>$userifo['figureurl_qq_2'],
      'qqopenid'=>$openid,
      'usertype'=>1,
      'logtype'=>3,
      'logintime'=>time()
    );
    // if ($_GET['state'] == 'bindqq') {       //个人信息绑定QQ
    //     $uid = getuser()['uid'];
    //     $save = M('user')->where(array('uid'=>$uid))->save($data);
    //     // if($save){
    //         header("location: http://www.ysjianzhu.com/main.html#/user/info");
    //         exit;
    //     // } 
    // }
    // else{               //登录绑定QQ
        // session('qqnickname',$data['qqnickname']);
        // session('qqimg',$data['qqimg']);
        // session('qqopenid',$data['qqopenid']);
        // session('logtype',3);
        // 查询数据库中有没有此openid
        $user = db('user')->where(array('qqopenid'=>$data['qqopenid']))->find();
        if($user){
        // 有，更新头像，昵称；
            db('user')->where(array('userid'=>$user['userid']))->update($data);
            // 做登录手续
                setonline($user);
                Session::delete('user');
                Session::set('user',$user);
            //header("location: http://www.ysjianzhu.com/main.html");
            header("location: https://www.udparty.com/");
            exit;
        }else{
            //没有，添加进数据库并跳转首页
            //生成一个用户名和密码
            $data['username']='ud_'.random();
            while (db('user')->where(array('username'=>$data['username']))->find()) {
                $data['username']='ud_'.random();
            }
            $data['password']=md5('123456');
            $data['createtime']=time();
            //header("location: http://www.ysjianzhu.com/login.html?tap=3&nickname=".$data['qqnickname']);
            $id=db('user')->insertGetId($data);
            $u=db('user')->where(array('userid'=>$id))->find();
            // setonline($u);
            // session('user',$u);
                setonline($u);
                Session::delete('user');
                Session::set('user',$u);
            header("location: https://www.udparty.com/");
            exit;
        } 
    //}
}