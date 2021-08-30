<?php /** @noinspection PhpUnused */
/** @noinspection SqlDialectInspection */

/** @noinspection SqlNoDataSourceInspection */

namespace db;

use mysqli;
use pocketmine\plugin\PluginBase;
use function mysqli_connect_errno;
use function mysqli_connect_error;
use function mysqli_connect;
use function mysqli_error;
use function mysqli_close;
use function mysqli_fetch_array;

class Main extends PluginBase {
    public mysqli $db;

    private array $cache;

    public function onEnable() {
        $this->getLogger()->info("База данных запущена...");
        $this->connectdb();
    }
    public function onDisable(){
        $this->closeConnect();
        $this->getLogger()->info("База данных выключена");
    }

	// подключение к бд
	public function connectdb(){
        $this->db = mysqli_connect("127.0.0.1", "dax", "456411", "serv", 3306);
        if (mysqli_connect_errno()) {
            $this->getLogger()->error("Соединение с базой не удалось: " . mysqli_connect_error());
            $this->getLogger()->alert("Выполняется переподключение...");
            $this->connectdb();
        } else {
            $this->getLogger()->alert("К MySQl успешно подключился");
        }
    }

    /**
     * @return bool
     */
    public function isConnect() :bool {
        if(mysqli_get_connection_stats($this->db) == false){
          return false;
        }
        return true;
    }


    public function reConnect(){
        if(!$this->isConnect()){
            $this->getLogger()->error("Соединение с базой потеряно");
            $this->getLogger()->alert("Выполняется переподключение...");
            $this->connectdb();
        }
    }

    /**
     * @return bool
     */
    public function closeConnect() :bool {
        if($this->isConnect()){
            return mysqli_close($this->db);
        }
        return false;
    }

    /**
     * @param $name
     * @return bool
     */
  public function createPlayer($name) :bool {
  	$names = strtolower($name);
  	if(!$this->getPlayer($name)){
        $sql = "INSERT INTO `users` (`name`, `joins`) VALUES ('" . $names . "', '1')";
        $res = mysqli_query($this->db, $sql);
        if($res){
            $this->getLogger()->alert("Новый игрок зарегистрирован на сервере");
            return true;
        }else{
            $this->getLogger()->error("Произошла ошибка при выполнении запроса" . $sql . "Код:" . mysqli_error($this->db));
            return false;
        }
    }
  	return false;
  } // achievements

    /**
     * @return array|false
     */
  public function getAchievements(){
  	$sql = "SELECT * FROM achievements";
  	$result = mysqli_query($this->db, $sql);
  	$row = mysqli_fetch_array($result); 	
  	if($row == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return $row;
  	}
  }

    /**
     * @param $name
     * @return array|int
     */
  public function getAllColumnAchievement($name){
  	$sql = "SELECT * FROM achievements_users WHERE name = '". $name ."' LIMIT 1";
  	$result = mysqli_query($this->db, $sql);
  	$row = mysqli_fetch_array($result); 	
  	if($row == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return 0;		
  	}else{
  		return $row;
  	}
  }

    /**
     * @param $name
     * @param $column
     * @return int|mixed
     */
  public function getColumnAchievement($name, $column){
  	$n = strtolower($name);
  	$sql = "SELECT ". $column ." FROM achievements_users WHERE name = '". $n ."' LIMIT 1";
  	$result = mysqli_query($this->db, $sql);
  	$row = mysqli_fetch_array($result); 	
  	if($row == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return 0;		
  	}else{
  		return $row[$column];
  	}
  }

    /**
     * @param $name
     * @param $column
     * @param $num
     * @return bool
     */
  public function addAchievement($name, $column, $num) :bool{
  	$sql = "UPDATE achievements_users SET ". $column ." = $column + $num WHERE name = '". $name ."'";
  	$result = mysqli_query($this->db, $sql);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }

    /**
     * @param $name
     * @param $column
     * @param $num
     * @return bool
     */
  public function reduceAchievement($name, $column, $num) :bool{
  	$sql = "UPDATE achievements_users SET ". $column ." = $column - $num WHERE name = '". $name ."'";
  	$result = mysqli_query($this->db, $sql);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }

    /**
     * @param $name
     * @param $column
     * @param $num
     * @return bool
     */
  public function setAchievement($name, $column, $num) :bool{
  	$sql = "UPDATE achievements_users SET ". $column ." = '". $num . "' WHERE name = '". $name ."'";
  	$result = mysqli_query($this->db, $sql);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }

    /**
     * @param $name
     * @return bool
     */
  public function getPlayer($name) : bool{
  	$names = strtolower($name);
  	$sql = /** @lang text */
        "SELECT id FROM users where name = '". $names ."'";
  	$result = mysqli_query($this->db, $sql);
  	$row = mysqli_fetch_array($result);
  	if($row == "[]"){
     	$this->getLogger()->alert("Игрок не найден. Регистрирую нового игрока.");  		
  		return false;
  	}else if($row == false){ 
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
	     return false;
	}else{
		return true;
	} 	
  }

    /**
     *
     * возвращает строку с определённым полем.
     *
     * @param $name
     * @param $column
     * @return int|string|mixed
     */
  public function getColumn($name, $column) :?string{
      if($this->isCache($name)){
          return $this->getCache($name)[$column];
      }
  	$sql = "SELECT ". $column ." FROM users where name = '". $name ."' LIMIT 1";
  	$result = mysqli_query($this->db, $sql);
  	$row = mysqli_fetch_array($result); 	
  	if($row == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return 0;		
  	}else{
  	    $this->addCache($name, $row);
  		return $row[$column];
  	}
  }
  // функция getAll возвращает массив с данными о пользователе

    /**
     * @param $name
     * @return array|false
     */
  public function getAll($name) :?array {
      if($this->isCache($name)){
          return $this->getCache($name);
      }
  	  $sql = "SELECT * FROM users where name = '". $name ."' LIMIT 1";
  	  $result = mysqli_query($this->db, $sql);
  	  $row = mysqli_fetch_array($result);
  	  if($row == false){
  	      $this->getLogger()->error("Произошла ошибка при выполнении запроса");
  	      return false;
  	  }else{
  	      $this->addCache($name, $row);
  	      return $row;
  	}
  }
    /**
     * @param $name
     * @param $column
     * @param $num
     * @return bool
     */
  public function add($name, $column, $num) :bool{
  	$sql = "UPDATE users SET ". $column ." = $column + $num WHERE name = '". $name ."'";
  	$result = mysqli_query($this->db, $sql);
  	$this->cacheClean($name);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }

    /**
     * @param $name
     * @param $column
     * @param $num
     * @return bool
     */
  public function reduce($name, $column, $num) :bool{
      $sql = "UPDATE users SET ". $column ." = $column - $num WHERE name = '". $name ."'";
      $result = mysqli_query($this->db, $sql);
      $this->cacheClean($name);
      if($result == false){
          $this->getLogger()->error("Произошла ошибка при выполнении запроса");
          return false;
      }else{
          return true;
      }
  }
  // Установить значение полю(одному)

    /**
     * @param $name
     * @param $column
     * @param $num
     * @return bool
     */
  public function set($name, $column, $num) :bool{
      $sql = "UPDATE users SET ". $column ." = '". $num . "' WHERE name = '". $name ."'";
      $result = mysqli_query($this->db, $sql);
      $this->cacheClean($name);
      if($result == false){
          $this->getLogger()->error("Произошла ошибка при выполнении запроса");
          return false;
      }else{
          return true;
      }
  }
  // Прибавление значения поля у всех

    /**
     * @param $column
     * @param $num
     * @return bool
     */
  public function addAll($column, $num) :bool{
  	$sql = "UPDATE users SET ". $column ." = $column + $num";
  	$result = mysqli_query($this->db, $sql);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }

    /**
     * @param $column
     * @param $num
     * @return bool
     */
  public function reduceAll($column, $num) :bool{
  	$sql = "UPDATE users SET ". $column ." = $column - $num";
  	$result = mysqli_query($this->db, $sql);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }  
  // Установить значения поля для всех

    /**
     * @param $column
     * @param $num
     * @return bool
     */
  public function setAll($column, $num) :bool{
  	$sql = "UPDATE users SET ". $column ." = $num";
  	$result = mysqli_query($this->db, $sql);
  	if($result == false){
     	$this->getLogger()->error("Произошла ошибка при выполнении запроса"); 
     	return false;		
  	}else{
  		return true;
  	}
  }

  private function getCache(string $name){
      if($this->isCache($name)){
          return $this->cache[strtolower($name)];
      }
      return null;
  }

  private function isCache(string $name){
      return isset($this->cache[strtolower($name)]);
  }

  private function addCache(string $name, array $array){
      if(!$this->isCache($name)){
          $this->cache[strtolower($name)] = $array;
      }
  }

  private function cacheClean(string $name){
      unset($this->cache[strtolower($name)]);
  }
}