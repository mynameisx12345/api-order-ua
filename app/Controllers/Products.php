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

  public function saveOrder(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $data = $this->request->getJSON();
    $result = $model->saveOrder($data);
    $returnBody =[
      'id' => $result,
      'message' => 'Success'
    ];
    return $this->response
      ->setStatusCode(200)
      ->setJson($returnBody);
  }

  public function getOrders(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $status = $this->request->getGet('status');

    $result = $model->getOrders($status);

    return $this->response
      ->setStatusCode(200)
      ->setJson($result);
  }

  private function findObjectById($sourceArray, $id){
    foreach($sourceArray as $arr){
      if($arr->id === $id){
        return $arr;
      } 
    }

    return false;
  }

  public function getOrdersDetailed(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $userId = $this->request->getGet('userId');
    $orderId = $this->request->getGet('orderId');
    

    $result = $model->getOrdersDetailed($userId,$orderId);
    foreach($result as $key => $res){
      $result[$key]->product_image = base64_encode($result[$key]->product_image);
     }

     //format data
    $formattedResult = array();
    foreach($result as $res){
      $found = $this->findObjectById($formattedResult, $res->id);
      if($found === FALSE){
        $res->products = array();
        $res->products[0]['product_id'] = $res->product_id;
        $res->products[0]['product_name']= $res->product_name;
        $res->products[0]['quantity'] = $res->quantity;
        $res->products[0]['price'] = $res->price;
        $res->products[0]['product_image'] = $res->product_image;
        unset($res->product_id);
        unset($res->product_name);
        unset($res->quantity);
        unset($res->price);
        unset($res->product_image);
        $formattedResult[]=$res;
      } else {
        $found->products[] = (object) ['product_id' => $res->product_id,
          'product_name' => $res->product_name,
          'quantity' => $res->quantity,
          'price' => $res->price,
          'product_image' => $res->product_image
        ];
      }
    }

    return $this->response
      ->setStatusCode(200)
      ->setJson($formattedResult);
  }

  public function updateOrderStatus(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $orderId = $this->request->getJSON()->id;
    $status = $this->request->getJSON()->status;
    $isForced = false;
    if(isset($this->request->getJSON()->isForced)){
      $isForced = $this->request->getJSON()->isForced;
    }
    

    //validate order
    if($status === 'S' && !$isForced){
      $result = $model->getOrders('Q');
      $onQueue = array_slice($result, 0, 5, true);
      // print_r($onQueue);
      $isOnQueue = $this->findObjectById($onQueue, $orderId);
      if($isOnQueue === FALSE){
        return $this->response
          ->setStatusCode(400)
          ->setJson(['message'=>'NOT ON QUEUE']);
      }
    }
    
    
    $model->updateOrderStatus($orderId, $status);

    $returnBody = (object) ['message'=>'Success'];

    return $this->response
      ->setStatusCode(200)
      ->setJson($returnBody);
  }

  public function searchProducts(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $searchString = $this->request->getGet('searchString');
    $result = $model->searchProducts($searchString);
    foreach($result as $key => $res){
      $result[$key]->product_image = base64_encode($result[$key]->product_image);
     }
 
    return $this->response
      ->setStatusCode(200)
      ->setJson($result);

  }

  public function saveProduct(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $productName = $this->request->getPost('product_name');
    $productCategory = $this->request->getPost('product_category');
    $price = $this->request->getPost('price');
    $image = $this->request->getFile('product_image');
    if($image !== null){
      $image = file_get_contents($image);
    }
    $data = [
      'product_name' => $productName,
      'product_category' => $productCategory,
      'cur_price_a' => $price,
      'product_image' => $image
    ];
    $productId = $model->saveProduct($data);
    
    return $this->response
      ->setStatusCode(200)
      ->setJson(['id'=>$productId, 'message' =>'Success']);
  }

  public function saveCategory(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $image = $this->request->getFile('category_image');
    if($image !== null){
      $image = file_get_contents($image);
    }
    $categoryName = $this->request->getPost('category_name');
    $data = [
      'category_name' => $categoryName,
      'category_image' => $image
    ];
    //print_r($data);
   $categoryId = $model->saveCategory($data);

   $categoryId = 1;

    return $this->response
      ->setStatusCode(200)
      ->setJson(['id'=>$categoryId, 'message' =>'Success']);
  }

  public function deleteCategory(){
    $db = db_connect();
    $model = new ProductsModel($db);
    
  }

  public function addHotProduct(){
    $db = db_connect();
    $model = new ProductsModel($db);
    $data = $this->request->getJSON();

    $hotProductId = $model->addHotProduct($data);
    
    return $this->response
      ->setStatusCode(200)
      ->setJson(['id'=>$hotProductId, 'message' =>'Success']);
  }


}