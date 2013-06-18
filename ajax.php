<?php
  function throwJSError($error){
		echo "showError('$error');";
	}

	function sendAccept($text){
		echo "notifyAccept('$text');";
	}

	function auth($login){
		$_SESSION["login"] = $login;
		sendAccept("Вы успешно авторизованы");
		echo '$("#loginBlock").hide();';
		echo '$("#info").show();';
		echo "game.login = '$login';";
		echo "genField();";
	}
	function save($text){
		$f = fopen("db/field.txt", "w");
		fwrite($f, serialize($text));
		fclose($f);
	}
	function load(){
		return unserialize(file_get_contents("db/field.txt"));
	}
	function getLetters($text){
		$tmp = $text;
		$tmp[0] = chr(ord($tmp[0]) + 48);
		$tmp[2] = chr(ord($tmp[2]) + 48);
		return $tmp;
	}
	function isWon($l1, $l2, $l3){
		$tmp = $l1[0];
		if(($l1[1] == $tmp) && ($l1[2] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		if(($l2[0] == $tmp) && ($l3[0] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		if(($l2[1] == $tmp) && ($l3[2] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		$tmp = $l2[0];
		if(($l2[1] == $tmp) && ($l2[2] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		$tmp = $l3[0];
		if(($l3[1] == $tmp) && ($l3[2] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		$tmp = $l1[1];
		if(($l2[1] == $tmp) && ($l3[1] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		$tmp = $l1[2];
		if(($l2[2] == $tmp) && ($l3[2] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		if(($l2[1] == $tmp) && ($l3[0] == $tmp)){
			if($tmp != "_"){
				return true;
			}
		}
		return false;
	}
	function isFullElem($elem){
		if($elem != "_"){
			return true;
		}else{
			return false;
		}
	}
	function isFull($field){
		$arr = $field[getLetters($field["last_coords"])];
		foreach($arr as $elem => $data){
			if(!isFull($data)){
				return false;
			}
		}
		return true;
	}
	function printLog($text){

	}

	if(!file_exists("db/field.txt")){
		$blockField = array(
			"1x1" => "_",
			"1x2" => "_",
			"1x3" => "_",
			"2x1" => "_",
			"2x2" => "_",
			"2x3" => "_",
			"3x1" => "_",
			"3x2" => "_",
			"3x3" => "_"
		);
		$field = array(
			"axa" => $blockField,
			"axb" => $blockField,
			"axc" => $blockField,
			"bxa" => $blockField,
			"bxb" => $blockField,
			"bxc" => $blockField,
			"cxa" => $blockField,
			"cxb" => $blockField,
			"cxc" => $blockField,
			"last" => "second",
			"last_coords" => "0x0",
			"winners" => array(
				"axa" => "_",
				"axb" => "_",
				"axc" => "_",
				"bxa" => "_",
				"bxb" => "_",
				"bxc" => "_",
				"cxa" => "_",
				"cxb" => "_",
				"cxc" => "_"
			),
			"winner" => "_"
		);
		save($field);
	}else{
		$field = load();
	}
	session_start();
	if(isset($_POST["p"])){
		$p = $_POST["p"];
		if(($p != "login") && (!isset($_SESSION["login"]))){
			die();
		}
		if($p == "login"){
			if($_POST["login"] == "first"){
				if($_POST["password"] == "test"){
					auth($_POST["login"]);
				}
			}else if($_POST["login"] == "second"){
				if($_POST["password"] == "test"){
					auth($_POST["login"]);
				}
			}else{
				die(throwJSError("Такого аккаунта не существует!"));
			}
		}else if($p == "load"){
			if(isset($_SESSION["login"])){
				auth($_SESSION["login"]);
			}
		}else if($p == "logout"){
			session_destroy();
			sendAccept("Вы успешно вышли из системы");
			echo "setTimeout(function(){ document.location.reload(true);}, 1000);";
			printLog("Пользователь ".$_SESSION["login"]." вышел из системы");
		}else if($p == "go"){
			if($field[$_POST["f"]][$_POST["b"]] == "_"){
				if($field["last"] != $_SESSION["login"]){
					if(($_POST["f"] == getLetters($field["last_coords"])) || ($field["last_coords"] == "0x0")){
						$field[$_POST["f"]][$_POST["b"]] = ($_SESSION["login"] == "first")?"X":"O";
						$field["last"] = $_SESSION["login"];
						$field["last_coords"] = $_POST["b"];
						sendAccept("Вы успешно походили в клетку ".$_POST["f"].":".$_POST["b"]);
						if(isWon(array($field[$_POST["f"]]["1x1"], $field[$_POST["f"]]["1x2"], $field[$_POST["f"]]["1x3"]),
							array($field[$_POST["f"]]["2x1"], $field[$_POST["f"]]["2x2"], $field[$_POST["f"]]["2x3"]),
							array($field[$_POST["f"]]["3x1"], $field[$_POST["f"]]["3x2"], $field[$_POST["f"]]["3x3"]))){
							if($field["winners"][$_POST["f"]] == "_"){
								$field["winners"][$_POST["f"]] = $_SESSION["login"];
								sendAccept("Вы выиграли поле ".$_POST["f"]);
							}
						}
						if(isWon(array($field["winners"]["axa"], $field["winners"]["axb"], $field["winners"]["axc"]),
							array($field["winners"]["bxa"], $field["winners"]["bxb"], $field["winners"]["bxc"]),
							array($field["winners"]["cxa"], $field["winners"]["cxb"], $field["winners"]["cxc"]))){
							if($field["winner"] == "_"){
								$field["winner"] = $_SESSION["login"];
								sendAccept("Вы выиграли!");
							}
						}
						save($field);
						echo "game.lastEdit = 0;";
					}else{
						throwJSError("Вы не можете походить в эту область");
					}
				}else{
					throwJSError("Вы не можете походить два раза подряд");
				}
			}else{
				throwJSError("Клетка не пуста, вы не можете в неё походить");
			}
		}else if($p == "get"){
			$time = filectime("db/field.txt");
			if($_POST["last"] <= $time){
				echo "game.lastEdit = {$time};";
				foreach($field as $f => $block){
					if(($f != "last") && ($f != "last_coords") && ($f != "winners") && ($f != "winner")){
						foreach($block as $b => $text){
							echo "setCell('$f', '$b', '$text');";
						}
					}else if($f == "last"){
						echo "game.last = '$block';";
						if($block != $_SESSION["login"]){
							echo "notifyBarUpdate('Ваш ход');";
						}else{
							echo "notifyBarUpdate('Ход вашего соперника');";
						}
					}else if($f == "last_coords"){
						
					}
				}
				if($field["winner"] != "_"){
					echo "notifyBarUpdate('Победил игрок ".$field["winner"]."');";
				}
			}
		}else if($p == "new"){
			if(isset($_SESSION["login"])){
				unlink("db/field.txt");
			}
		}
	}
?>
