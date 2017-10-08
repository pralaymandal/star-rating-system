<?php
require_once 'core/init.php';
if(Input::exists()){

	//get entered details
	$username=Input::get('username');
	$password=Input::get('password');

	//log user in
	$user = new User();
	if($user->login($username,$password)){
		header('Location: posts.php');
	}else{
		echo "<p>Sorry Login Failed</p>";
	}

}
?>

<form method="POST" action="">
	<input type="text" name="username" placeholder="Username" />
	<br>
	<input type="password" name="password" placeholder="Password" />
	<br>
	<input type="submit" name="submit" value="LogIn" />
</form>

