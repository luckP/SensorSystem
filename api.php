<?php
  include('entity.class.php');
  require('config.php');

  if (!empty($_GET['action'])){
    $action = $_GET['action'];
    switch ($action) {

      case 'register':{
          $email = $_POST['email'];
          $password = $_POST['password'];

          $user = new User($email, $password);
          $user->insert();

          echo $user->toJson();
        } break;


        case 'login':{
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = new User($email, $password);
            $user->selectByEmailPassword();

            $authTokens = new AuthTokens($user->getId());
            $authTokens->select_by_user_id();
            if($authTokens->getId() == 0){
              $authTokens->insert();
            }

            $authTokens->checkValidToken();
            echo $authTokens->toJson();
          } break;

          case 'logout':{
              $token = new AuthTokens(0);
              $token->setAuthToken($_GET['X-Forwarded-For']);
              $token->select_by_token();

              if(!$token->delete())
                echo 'logout: error';
              else
                echo 'logout: ok';
            } break;

      default:
        // code...
        break;
    }
  }

 ?>
