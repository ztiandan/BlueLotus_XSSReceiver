<?php
define("IN_XSS_PLATFORM",true);
//CSP开启
header("Content-Security-Policy: default-src 'self'; object-src 'none'; frame-src 'none'");
header("X-Content-Security-Policy: default-src 'self'; object-src 'none'; frame-src 'none'");
header("X-WebKit-CSP: default-src 'self'; object-src 'none'; frame-src 'none'");

ini_set("session.cookie_httponly", 1); 
session_start();
require_once("config.php");
require_once("functions.php");


if(isset($_SESSION['isLogin']) && $_SESSION['isLogin']===true)
{
	header("Location: admin.php");
	exit();
}

$forbiddenIPList=loadForbiddenIPList();
$ip=$_SERVER['REMOTE_ADDR'];
if(!isset($forbiddenIPList[$ip]) || $forbiddenIPList[$ip]<3)
{
	if(isset($_POST['password']) && $_POST['password']!='' )
	{
		if(checkPassword($_POST['password']))
		{
			$_SESSION['isLogin']=true;
			$_SESSION['user_IP']=$ip;
			$_SESSION['user_agent']=$_SERVER['HTTP_USER_AGENT'];
			if(isset($forbiddenIPList[$ip]))
			{
				unset($forbiddenIPList[$ip]);
				saveForbiddenIPList($forbiddenIPList);
			}
			header("Location: admin.php");
			exit();
		}
		else
		{
			if(isset($forbiddenIPList[$ip]))
				$forbiddenIPList[$ip]++;
			else
				$forbiddenIPList[$ip]=1;
			saveForbiddenIPList($forbiddenIPList);
		}
	}
	
}

function loadForbiddenIPList()
{
	$logfile = DATA_PATH . '/forbiddenIPList.dat';
	!file_exists( $logfile ) && @touch( $logfile );
	$str = file_get_contents( $logfile );
	$str =decrypt($str,ENCRYPT_PASS);
	if($str!='')
	{
		$result=json_decode($str,true);
		if($result!=null)
			return $result;
		else
			return array();
	}
	else
		return array();
}

function saveForbiddenIPList($forbiddenIPList)
{
	$logfile = DATA_PATH . '/forbiddenIPList.dat';
	!file_exists( $logfile ) && @touch( $logfile );
	@file_put_contents($logfile, encrypt(json_encode($forbiddenIPList),ENCRYPT_PASS));
}

/*
生成密码
php -r "$salt='!KTMdg#^^I6Z!deIVR#SgpAI6qTN7oVl';$key='bluelotus';$key=md5($salt.$key.$salt);$key=md5($salt.$key.$salt);$key=md5($salt.$key.$salt);echo $key;"
*/
function checkPassword($p)
{
	if(isset($_SESSION['firesunCheck'])&&isset($_POST['firesunCheck'])&&$_SESSION['firesunCheck']!=""&&$_POST['firesunCheck']===$_SESSION['firesunCheck'])
	{
		$salt="!KTMdg#^^I6Z!deIVR#SgpAI6qTN7oVl";
		$key=PASS;
		$key=md5($salt.$key.$_SESSION['firesunCheck'].$salt);
		$key=md5($salt.$key.$_SESSION['firesunCheck'].$salt);
		$key=md5($salt.$key.$_SESSION['firesunCheck'].$salt);
		return $key===$p;
		
	}	
	return false;
}

function generate_password( $length = 32 ) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  
	$password = "";  
	for ( $i = 0; $i < $length; $i++ )
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	return $password;  
} 


?>

<html>
    <head>
        <meta charset="utf-8" />
		<title>登录</title>
		<link rel="stylesheet" href='static/css/font-awesome.css' type="text/css" >
		<link rel="stylesheet" href="static/css/login.css" type="text/css" />
		
        <script type="text/javascript" src="static/js/jquery.min.js" ></script>
        <script type="text/javascript" src="static/js/login.js" ></script>
    </head>
    
    <body>
        <div id="loginform">
			<div id="logo"></div>
            <div id="mainlogin">
                <h1>
                    登录控制面板
                </h1>
                <form action="" method="post">
                    <input type="password" placeholder="password" id="password" name="password" required="required">
					<input id="firesunCheck" type="hidden" name="firesunCheck" value=<?php $firesunCheck=generate_password(32); $_SESSION['firesunCheck']=$firesunCheck;echo json_encode($_SESSION['firesunCheck']);?> />
                    <button type="submit" id="submit">
                        <i class="fa fa-arrow-right">
                        </i>
                    </button>
                </form>
                <div id="note">
                    <a href="#">
                        忘记密码?
                    </a>
                </div>
            </div>
        </div>
    </body>

</html>