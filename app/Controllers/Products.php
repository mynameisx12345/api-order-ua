<?php
namespace App\Controllers;

use App\Models\ProductsModel;

class Products extends BaseController
{
  public function hotProducts()
  {
    $db = db_connect();
    $model = new ProductsModel($db);
    $result = $model->getHotProducts();

    foreach($result as $key => $res){
     $result[$key]->product_image = base64_encode($result[$key]->product_image);
    }

    //print_r($result);
    return $this->response
      ->setStatusCode(200)
      ->setJson($result);
  }

  public function productCategories()
  {
    $db = db_connect();
    $model = new ProductsModel($db);
    $result = $model->getProductCategories();
    foreach($result as $key => $res){
      $result[$key]->category_image = base64_encode($result[$key]->category_image);
     }
    return $this->response
      ->setStatusCode(200)
      ->setJson($result);
  }

  public function products()
  {
    $this->category = $this->request->getGet('category');

    $db = db_connect();
    $model = new ProductsModel($db);
    $result = $model->getProductsWithCategories($this->category);
  
    return $this->response
      ->setStatusCode(200)
      ->setJson($result);
  }

  public function insertShoppingCart(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $data = $this->request->getJSON();
    $model->insertShoppingCart($data);

    return $this->response
      ->setStatusCode(200)
      ->setBody('Success');
  }

  public function retrieveCart(){
    $this->userId = $this->request->getGet('userId');
    $db = db_connect();
    $model = new ProductsModel($db);
    $result = $model->getCartItems($this->userId);

    foreach($result as $key => $res){
      $result[$key]->product_image = base64_encode($result[$key]->product_image);
     }

    return $this->response
      ->setStatusCode(200)
      ->setJson($result);
  }

}