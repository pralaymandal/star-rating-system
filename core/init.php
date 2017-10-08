<?php
session_start();

$GLOBALS['config']=array(
	'mysql' => array(
		'host' => '127.0.0.1',
		'username' => 'root',
		'password' =>'',
		'db' => 'starsys'
	),
	'session' => array(
		'session_name'=>'user',
		'token_name' => 'token'
	)
);
// $connect=new Mysqli('127.0.0.1','root','','starsys');
// $connection=new mysqli('127.0.0.1','root','','starsys');
// $x = ($connection=true) ? "Success" : "Fail" ;
// echo $x;
// $result=$connection->query("SELECT * FROM 'users'");

// function all($table){
// 	global $connection;
// 	return $result=$connection->query("SELECT * FROM '$table'");
// }


class Config{
	public static function get($path=null){					// $path =session/token_name  != null
		if($path){											// if(true)
			$config=$GLOBALS['config'];						// 
			$path=explode('/',$path);
			
			foreach($path as  $bit){
				if(isset($config[$bit])){
					$config = $config[$bit];

				}
			}
			return $config;
		}
		return false;
	}
}

class DB{

	private static $_instance =null;
	private $_pdo,
			$_query,
			$_error = false,
			$_results,
			$_count=0;

	private function __construct(){
		try{
			$this->_pdo = new PDO('mysql:host='.Config::get('mysql/host').';dbname='.Config::get('mysql/db'),Config::get('mysql/username'),Config::get('mysql/password'));
		}catch(PDOException $e){
			die($e->getMessage());
		}
	}
	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function query($sql,$params =array()){
		$this->_error =false;
		if($this->_query = $this->_pdo->prepare($sql)){
			$x=1;
			if(count($params)){
				foreach($params as $param){
					$this->_query->bindValue($x, $param);
					$x++;
				}
			}
			if($this->_query->execute()){
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count =$this->_query->rowCount();
			}else{
				$this->_error = true;
			}
		}
		return $this;
	}
	public function getAll($table){
		if(!$this->query("SELECT * FROM {$table}")->error()){
			return $this;
		}else{
			return $this->error();
		}
	}
	public function action($action,$table,$where=array()){
		if(count($where) === 3){
			$operators=array('=','>','<','>=','<=');
			$field		=$where[0];
			$operator 	=$where[1];
			$value 		=$where[2];
			if(in_array($operator,$operators)){
				$sql= "{$action} FROM {$table} WHERE {$field} {$operator} ?";
				if(!$this->query($sql,array($value))->error()){
					return $this;
				}
			}
		}
		return false;
	}
	public function get($table,$where){
		return $this->action('SELECT *',$table, $where);
	}
	public function delete($table,$where){
		return $this->action('DELETE',$table, $where);
	}
	public function insert($table, $fields = array()){

			$keys = array_keys($fields);
			$values='';
			$x=1;

			foreach($fields as $field){
				$values .='?';
				if ($x<count($fields)) {
					$values.=', ';
				}
				$x++;
			}


			$sql = "INSERT INTO {$table} (`". implode('`,`', $keys)."`) VALUES({$values}) ";
			if (!$this->query($sql,$fields)->error()) {
				return true;
			}

		return false;
	}
	public function update($table,$id,$fields){
		$set = "";
		$x=1;
		foreach($fields as $name => $value){
			$set .= "{$name} = ? ";
			if($x < count($fields)){
				$set.=', ';
			}
			$x++;
		}
		$sql = " UPDATE {$table} SET {$set} WHERE id = {$id}";
		if (!$this->query($sql,$fields)->error()) {
			return true;
		}
		return false;
	}
	public function results(){
		return $this->_results;
	}
	public function first(){
		return $this->results()[0];
	}
	public function error(){
		return $this->_error;
	}
	public function count(){
		return $this->_count;
	}
}





class Session{
	public static function exists($name){
		return (isset($_SESSION[$name])) ? true : false;
	}
	public static function put($name,$value){
		$_SESSION[$name] = $value;
	}
	public static function get($name){
		return $_SESSION[$name];
	}
	public static function  delete($name){
		if(self::exists($name)){

			unset($_SESSION[$name]);
		}
	}

	public static function flash($name,$string = ''){
		if(self::exists($name)){
			$session = self::get($name);
			self::delete($name);
			return $session;
		}else{
			self::put($name,$string);
		}
	}
}




class Input{
	public static function exists($type='post'){
		switch ($type) {
			case 'post':
				 return(!empty($_POST)) ? true :false;
				break;
			case 'get':
				return(!empty($_GET)) ? true :false;
				break;
			
			default:
				return false;
				break;
		}
	}
	public static function get($item){
		if(isset($_POST[$item])){
			return $_POST[$item];
		}else if(isset($_GET[$item])){
			return $_GET[$item];
		}
		return '';
	}
}



class User{
	private $_db,
			$_data,
			$_sessionName,
			$_isLoggedIn;

	public function __construct($user = null){
		$this->_db =DB::getInstance();

		$this->_sessionName = Config::get('session/session_name');

		if(!$user){
			if(Session::exists($this->_sessionName)){
				$user = Session::get($this->_sessionName);
				if($this->find($user)){
					$this->_isLoggedIn =true;
				}else{
					//
				}
			}
		}else{
			$this->find($user);
		}
	}

	public function update($fields = array(),$id = null){
		if(!$id && $this->isLoggedIn()){
			$id = $this->data()->id;
		}
		
		$qr_rslt = $this->_db->update('users',$id,$fields);
		if(!$qr_rslt){
			throw new Exception("There was a problem Updating Data");
			
		}
		
	}

	public function create($fields = array()){
		if(!$this->_db->insert('users', $fields)){
			throw new Exception('There was a problem creating an account.');
		}
	}
	public function find($user = null){
		if($user){
			$field =(is_numeric($user)) ? 'id' :'username';
			$data = $this->_db->get('users', array($field, '=', $user));
			if($data->count()){
				$this->_data =$data->first();
				return true;
			}
		}
		return false;
	}
	public function login($username=null, $password=null){
		if(!$username && !$password &&$this->exists()){
			Session::put($this->_sessionName,$this->data()->id);
		}else{
			$user = $this->find($username);
			if($user){
				if($this->data()->password==$password){
					Session::put($this->_sessionName, $this->data()->id);
					return true;
					}
				}
				echo 'Please Enter Correct Password';
			}

			return false;
		}

	public function hasPermission($key){
		$group = $this->_db->get('groups', array('id','=',$this->data()->group));
		if($group->count()){
			$permissions = json_decode($group->first()->permissions,true);
			if($permissions[$key] == true){
				return true;
			}
		}
		return false;
	}
	public function exists(){
		return (!empty($this->_data)) ? true :false ;
	}
	public function logout(){

		$this->_db->delete('users_session', array('user_id','=',$this->data()->id));
		

		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);
	}
	public function data(){
		return $this->_data;
	}

	public function isLoggedIn(){
		return  $this->_isLoggedIn;
	}

	public function permissionCheck(){
		if($this->hasPermission('admin')){
			echo 'an Administrator';
		} else{
			echo 'a Standared User';
		}
	}
}




function makeRatingForm($post){
	
}
