<?php
require_once 'core/init.php';

$user =new User();
if ($user->isLoggedIn()) {
	header('Location: posts.php');
}else{

?>
<h1>You Are not Logged In Yet Please <a href="login.php">LogIn</a> or <a href="register.php">Register</a></h1>

<?php
}