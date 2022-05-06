<?php namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class ProductsModel{
  protected $db;

  public function __construct(ConnectionInterface &$db){
    $this->db =& $db;
  }

  function getHotProducts(){
    $builder = $this->db->table('hot_products');
    $builder->select('*');
    $builder->join('products', 'products.id = product_id');
    $query = $builder->get()->getResult();

    return $query;
  }

  function getProductCategories(){
    return $this->db->table('categories')
      ->orderBy('category_name')
      ->get()->getResult();
  }

  function getProductsWithCategories($categoryId){
    $builder = $this->db->table('products');
    $builder->where('product_category', $categoryId);
    $builder->orderBy('product_name');
    $query = $builder->get()->getResult();

    foreach($query as $key => $res){
      $query[$key]->product_image = base64_encode($query[$key]->product_image);
     }
    return $query;
  }

  function insertShoppingCart($data){
    $builder = $this->db->table('shopping_carts');
    $builder->where('product_id', $data->product_id);
    $builder->where('user_id', $data->user_id);
    $query = $builder->countAllResults();

    $isInsert = '1';
    if($query > 0){
      $isInsert = '0';
    } 
    $dataA = [
      'product_id' => $data->product_id,
      'user_id' => $data->user_id,
      'quantity' => $data->quantity,
      'is_selected' => $data->is_selected
    ];

    if($isInsert == '1'){
      //print_r('wewe');
      $this->db->table('shopping_carts')
        ->insert($dataA);
    } else {
      $builder = $this->db->table('shopping_carts');
      $builder->where('product_id', $data->product_id);
      $builder->where('user_id', $data->user_id);
      $builder->update($dataA);
    }
    
  }

  function getCartItems($userId){
    $builder = $this->db->table('shopping_carts');
    $builder->select('shopping_carts.id,
      products.product_name, 
      products.cur_price_a,
      shopping_carts.product_id,
      shopping_carts.user_id,
      shopping_carts.quantity,
      shopping_carts.is_selected,
      products.product_image');
    $builder->join('products', 'products.id = product_id');
    $builder->where('user_id', $userId);
    

    $query = $builder->get()->getResult();
    return $query;
  }
}