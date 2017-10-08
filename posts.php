<?php
require_once 'core/init.php';
$user = new User();
if($user->isLoggedIn()){
$results=DB::getInstance()->getAll('posts')->results();
if(Input::exists()){

	$insert=DB::getInstance()->insert('ratings',
		array(

			'user_id'=>$user->data()->id,
			'post_id'=>Input::get('post_id'),
			'ratting'=>Input::get('rating')

		));

}

foreach($results as $result){
	// var_dump($result);
	echo "<h1>".$result->title."</h1>";
	echo "<h3>".$result->content."</h3>";
	echo "<hr>";
	$ratingResult=DB::getInstance()->get('ratings',array('post_id','=',$result->id))->results();
	$sum = 0;
 	$count=count($ratingResult);
 	if($count>0){
	 	foreach($ratingResult as $results){
	 		$sum=$sum+$results->ratting;
	 	}
	 	$avg=$sum/$count;
 	}else{
 		$avg=false;
 	}

	if($avg==false){
?>
	<h5>This Post have no ratting yet! </h5>

<?php
		}else{
	
			echo "<h5>Avarage rating result is ".round($avg,2)."</h5>"; 
	}



	echo "Rate this Post >>>";
	echo "<form action='' method='POST'>";
	echo "<select name='rating'>";
	echo "<option value=1>1</option>";
	echo "<option value=2>2</option>";
	echo "<option value=3>3</option>";
	echo "<option value=4>4</option>";
	echo "<option value=5>5</option>";
	echo "</select>";
	echo "<input type='hidden' name='post_id' value='".$result->id."'/>";
	echo "<input type='submit' name='rate' value='Rate'/>";
	echo "</form>";
	echo "<br>";
	echo "<br>";
	echo "<br>";
}
}else{
	header('Location: index.php');
}
