<?php
namespace App\Controllers;

use App\Models\UsersModel;

class UsersController extends BaseController
{
  public function login()
  {
    $db = db_connect();
    $model = new UsersModel($db);
    $email =  $this->request->getJSON()->email;
    $password = $this->request->getJSON()->password;
    $result = $model->login($email, $password);
    if(count($result)>0){
      return $this->response 
        ->setStatusCode(200)
        ->setJson($result);
    } else {
      return $this->response
        ->setStatusCode(500)
        ->setBody('Access Denied');
    }
  }
}