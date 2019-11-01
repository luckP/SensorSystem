<?php
  include('entity.class.php');
  require('config.php');

  if (!empty($_GET['action'])){
    $action = $_GET['action'];
    switch ($action) {

      case 'register':{ register(); } break;
      case 'login':{ login(); } break;
      case 'logout':{ logout();} break;

      case 'createCompany':{ createCompany();} break;
      case 'createSensor':{ createSensor();} break;
      case 'joinUserToCampany':{ joinUserToCampany();} break;
      case 'addSensorValue':{ addSensorValue();} break;
      // case 'logout':{ logout();} break;

      default:
        // code...
        break;
    }
  }

function checkToken(){
  $token = new AuthTokens(0);
  $token->setAuthToken($_GET['X-Forwarded-For']);
  $token->select_by_token();

  return  $token;
}

function login(){
  $email = $_POST['email'];
  $password = $_POST['password'];

  $user = new User($email, $password, 1);
  $user->selectByEmailPassword();


  if($user->getId() != 0){

  $authTokens = new AuthTokens($user->getId());
  $authTokens->select_by_user_id();
    if($authTokens->getId() == 0){
      $authTokens->insert();
    }

    $authTokens->checkValidToken();
    echo '{"X-Forwarded-For": "'.$authTokens->getAuthToken().'"}';
  }
  else{
    echo 'login: error';
  }
}

function logout(){
  $token = checkToken();

  if($token->getId()!=0 && !$token->delete())
    echo 'logout: error';
  else
    echo 'logout: ok';
}

function register(){
  $email = $_POST['email'];
  $password = $_POST['password'];

  $user = new User($email, $password, 1);
  $user->insert();

  echo $user->toJson();
}

function createCompany(){

  $token = checkToken();
  if($token->getId()!=0){
    $user = $token->getUserByToken();
    if($user){
      $company = new Company($_POST['name']);
      // CHECK IF COMPANY EXIST
      $company->insert();
      echo $company->toJson();
    }

    else
      echo 'Error: user id';
  }
  else{
    echo 'Error: auth tokens';
  }
}

function createSensor(){
  $token = checkToken();
  if($token->getId()!=0){
    $user = $token->getUserByToken();
    if($user){
      $sensor = new Sensor($_POST['name'], $_POST['raw_min_val'], $_POST['raw_max_val'], $_POST['min_val'], $_POST['max_val'], $_POST['measure_units'], $_POST['company_id']);
      // CHECK IF SENSOR EXIST
      $sensor->insert();
      echo $sensor->toJson();
    }

    else
      echo 'Error: user id';
  }
  else{
    echo 'Error: auth tokens';
  }
}

function joinUserToCampany(){
  $token = checkToken();
  if($token->getId()!=0){
    $user = $token->getUserByToken();
    if($user){
      $userCompany = new UserCompany($_POST['user_id'], $_POST['company_id'], 0);
      // CHECK IF SENSOR EXIST
      $userCompany->insert();
      echo $userCompany->toJson();
    }
    else
      echo 'Error: joinUserToCampany';
  }
  else{
    echo 'Error: auth tokens';
  }
}

function addSensorValue(){
  $token = checkToken();
  if($token->getId()!=0){
    $user = $token->getUserByToken();
    if($user){
      $sensorVal = new SensorVal($_POST['sensor_id'], $_POST['val']);
      // CHECK IF SENSOR EXIST
      $sensorVal->insert();
      echo $sensorVal->toJson();
    }

    else
      echo 'Error: user id';
  }
  else{
    echo 'Error: auth tokens';
  }
}

 ?>
