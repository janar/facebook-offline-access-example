<?php

require './config.php';
require './facebook.php';

//connect to mysql database
mysql_connect($db_host, $db_username, $db_password);
mysql_select_db($db_name);
mysql_query("SET NAMES utf8");

//Create facebook application instance.
$facebook = new Facebook(array(
  'appId'  => $fb_app_id,
  'secret' => $fb_secret
));

$output = '';

//if form below is posted- 
//lets try to send wallposts to users walls, 
//which have given us a access_token for that
if(isset($_POST['send_messages'])){
	
	//default message/post
	$msg =  array(
		'message' => 'date: ' . date('Y-m-d') . ' time: ' . date('H:i')
	);
	
	//construct the message/post by posted data
	if(isset($_POST['message'])){
		$msg['message'] = $_POST['message'];
	}
	if(isset($_POST['url']) && $_POST['url'] != 'http://'){
		$msg['link'] = $_POST['url'];
	}
	if(isset($_POST['picture_url']) && $_POST['picture_url'] != ''){
		$msg['picture'] = $_POST['picture_url'];
	}
	
	//get users and try posting our message/post
	$result = mysql_query("
		SELECT
			*
		FROM
			offline_access_users
	");
	
	if($result){
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$msg['access_token'] = $row['access_token'];
			try {
				$facebook->api('/me/feed', 'POST', $msg);
				$output .= "<p>Posting message on '". $row['name'] . "' wall success</p>";
			} catch (FacebookApiException $e) {
				$output .= "<p>Posting message on '". $row['name'] . "' wall failed</p>";
			}
		}
	}
}


?><!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et" lang="en">
	<head>
		<title>Batch posting</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			body { font-family:Verdana,"Lucida Grande",Lucida,sans-serif; font-size: 12px}
		</style>
	</head>
	<body>
		<h1>Batch posting</h1>
		<form method="post" action="">
			<p>Link url: <br /><input type="text" value="http://" size="60" name="url" /></p>
			<p>Picture url: <br /><input type="text" value="" size="60" name="picture_url" /></p>
			<p>Message: <br /><textarea type="text" value="" cols="160" rows="6" name="message" />Message here</textarea></p>
			<p><input type="submit" value="Send message to users walls" name="send_messages" /></p>
		</form>
		<?php echo $output; ?>
	</body>
</html>