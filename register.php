<?php 

require_once 'core/init.php';

if(Input::exists()){

	$user= new User();
	try{
		$user->create(array(

			'username' => Input::get('username'),
			'password' => Input::get('password')

			));

		Session::flash('home','You have registered and now you can log in!');
		header('Location: index.php');

	}catch(Exception $e){
		die($e->getMessage());
	}




}
?>

<form action="" method="post">
	<input type="text" id="username" name="username" autocomplete="off" placeholder="Username">
	<br>
	<input type="password" id="password" name="password" placeholder="Password">
	<br>
	<input type="submit" value="Register">
</form>