<?php
session_start();
/**
* Framework simples MVC
* Autor:Alan Klinger 05/06/2017
*/
require 'app/sys/config.php';
require 'app/sys/util.php';
#require 'sys/Pagination.php';
#require 'sys/validate.php';
#require 'sys/messages.php';
require 'app/models/Model.php';
require 'app/models/Usuario.php';
require 'app/models/Veiculo.php';


$server_url = "http://".$_SERVER['SERVER_NAME'] . explode("index.php",$_SERVER['SCRIPT_NAME'])[0];


/**
*Retorna o endereco da url ate a pasta principal do projeto
*/
function serverUrl(){
	global $server_url;
	return $server_url;
}

function _url($url){

	$arr = explode("/",$url);

	$urlBack = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	if (strstr($url,".")){
		if (!file_exists($url) && !file_exists(".".$url)){
			Header("Location: .app/errors/url.php?url=$url&back=$urlBack");
		}
	} else
	if (file_exists('app/controllers/'.$arr[0].'Controller.php')){
		include_once 'app/controllers/'.$arr[0].'Controller.php';
		$methods = get_class_methods($arr[0]."Controller");
		if ( !in_array($arr[1],$methods) ){
			Header("Location: .app/errors/url.php?url=$url&back=$urlBack");
		}
	} else {
		Header("Location: .app/errors/url.php?url=$url&back=$urlBack");
	}




	$local = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	$local = substr($local,0,strpos($local,"index.php")) . $url;
	return "http://".$local;
}

function route($route){
	global $server_url;
	return $server_url.$route;
}

function assets($route){
	global $server_url;
	return $server_url."public/".$route;
}

/**
* Redireciona para outra pagina
*/
function redirect($url){

	if (!strstr($url,"http")){
		$local = _url($url);
	} else {
		$local = $url;
	}
	
	Header("Location: $local");
}

/**
* Chama a view php
*/
function render($name, $send=array()){
	global $server_url;
	if (file_exists("app/views/$name.php")){
		extract($send, EXTR_PREFIX_SAME, "wddx");
		include "app/views/$name.php";
	}
}

function model($name){
	include 'app/models/'.$name.'.php';
}

function all_models(){
	global $createTables;
	$model_files = scandir("app/models/");

	foreach($model_files as $file){
		$ff = explode('.', $file);
		if(
			strtolower($ff[0]) !== strtolower(__CLASS__) &&
			strtolower($ff[1]) === 'php') {
			require_once("app/models/".$file);
			
			
			if ($createTables)
				$ff[0]::createTable(); 
		}
	}
}

$local = str_replace("index.php","", $_SERVER["SCRIPT_NAME"]);
$parts = str_replace($local,"", $_SERVER["REQUEST_URI"]);
$parts = trim(str_replace("index.php","", $parts),"/");

if (strstr($parts,"#")){
	$parts = substr($parts,0,strpos($parts,"#"));
}
if (strstr($parts,"?")){
	$parts = substr($parts,0,strpos($parts,"?"));
}

if ($parts != ""){
	$parts = explode("/", $parts );
} else {
	$parts = array();
}


//carrega a classe controle
if (_v($parts,0) != ""){
	$class = ucwords(strtolower($parts[0]));
} else {
	$class = "Principal";
}

include 'app/controllers/'.$class.'Controller.php';


//carrega o metodo
if (_v($parts,1) != ""){
	$metodo = $parts[1];		
} else {
	$metodo = "index";
}

$class .= "Controller";
#carrega o controller
$controller = new $class();


$params_to_controller = array();

#Converte o request para objetos
$request = $_REQUEST;
$r = new ReflectionMethod( $controller, $metodo );
$params = $r->getParameters();
$methodDoc = strtolower($r->getDocComment());

if ( !empty( $params ) ) {
	$param_names = array();
	foreach ( $params as $param ) {
		$obj = null;
		$paramName = $param->getName();
		
		//Para parametros primitivos
		if ($param->getDeclaringClass() == null){

			foreach($request as $key=>$req ){
				if ($key == $paramName){
					if ($_REQUEST[$key] == ""){
						$obj = null;
					} else {
						$obj = $_REQUEST[$key];
					}
					unset($request[$key]);
				}
			}
			

		} else {
			//Para parametros não primitivos
			$className = $param->getDeclaringClass()->getName();
						
			foreach($request as $key=>$req ){
				if (strstr($key,$paramName)){
					if ($obj == null){
						$obj = new $className();
					}

					$attribute = str_replace($paramName."_","",$key);
					$obj->$attribute = $_REQUEST[$key];
					unset($request[$key]);
				}
			}

		}


		array_push($params_to_controller, $obj);
	}
}


if ( count($parts) > 2 ){

	for ($i = 0; $i < count($params_to_controller); $i++){
		if ($params_to_controller[$i] == null ){
			$params_to_controller[$i] = $parts[2+$i];
		}
	}

}


//$obj->$metodo();
call_user_func_array(array($controller, $metodo), $params_to_controller);