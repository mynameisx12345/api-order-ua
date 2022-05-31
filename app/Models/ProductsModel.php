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

  private function formatOrdDtl($ordDtl,$hdrId){
    $dataDtl = array();
    foreach($ordDtl as $key=>$value){
      $dataDtl[] = [
        //'id' => $ordDtl[$key]->id,
        'order_hdr_id' => $hdrId,
        'product_id' => $ordDtl[$key]->product_id,
        'quantity' => $ordDtl[$key]->quantity,
        'price' =>$ordDtl[$key]->price
      ];
    }
    return $dataDtl;
  }

  function saveOrder($data){
    //$this->db->trans_start();

    date_default_timezone_set('Asia/Singapore');
    $curDate = date('y-m-d h:i:s');
    if($data->status === "Q"){
      $data->dt_queued = $curDate;
      if($data->dt_checkout == null){
        $data->dt_checkout = $curDate;
      }
    } else if ($data->status === "C"){
      $data->dt_checkout = $curDate;
    } else if ($data->status === "S"){
      $data->dt_served = $curDate;
    } else if ($data->status === "P"){
      $data->dt_paid = $curDate;
      if($data->dt_served == null){
        $data->dt_served = $curDate;
      }
    } else if($data->status === "X"){
      $data->dt_cancelled = $curDate;
    }

    $dataHdr = [
      'id' => $data->id,
      'user_id' => $data->user_id,
      'sub_total' => $data->sub_total,
      'discount' => $data->discount,
      'total' => $data->total,
      'status' => $data->status,
      'dt_checkout' => $data->dt_checkout,
      'dt_queued' => $data->dt_queued,
      'dt_served' => $data->dt_served,
      'dt_paid' => $data->dt_paid,
      'dt_cancelled' => $data->dt_cancelled
    ];

    $dataDtl = array();
   
    $hdrId = $data->id;
    if($data->id == null){ //INSERT ORDER
      $this->db->table('order_hdr')
        ->insert($dataHdr);
      $hdrId = $this->db->insertID();

      if($hdrId != null){ 
        $dataDtl = $this->formatOrdDtl($data->dtl,$hdrId);

        $this->db->table('order_dtl')
          ->insertBatch($dataDtl);
      } 

      //delete shopping cart
      $this->db->table('shopping_carts')
        ->where('user_id', $data->user_id)
        ->delete();

    } else { //UPDATE ORDER
      $this->db->table('order_hdr')
        ->where('id', $hdrId)
        ->update($dataHdr);
      
      $dataDtl = $this->formatOrdDtl($data->dtl,$hdrId);
      $this->db->table('order_dtl')
        ->where('order_hdr_id', $hdrId)
        ->delete();
      $this->db->table('order_dtl')
        ->insertBatch($dataDtl);
    }

    

    return $hdrId;

    // if($this->db->trans_status() === FALSE){
    //   $this->db->trans_rollback();
    // } else {
    //   $this->db->trans_commit();
    // }
    //$this->db->trans_complete();
  }

  function getOrders($status){
    $builder = $this->db->table('order_hdr');
    $builder->select('order_hdr.id, 
      order_hdr.total,
      users.first_name, 
      users.last_name,
      order_hdr.user_id');
    $builder->join('users', 'users.id = order_hdr.user_id');
    $builder->where('order_hdr.status',$status);
    $builder->orderBy('order_hdr.id');
    $query = $builder->get()->getResult();
    return $query;
  }

  function getOrdersDetailed($userId,$orderId){
    $builder = $this->db->table('order_hdr');
    $builder->select('order_hdr.id,
      order_hdr.sub_total,
      order_hdr.total,
      order_hdr.discount,
      order_hdr.status,
      order_hdr.dt_checkout,
      order_hdr.dt_queued,
      order_hdr.dt_served,
      order_hdr.dt_paid,
      order_hdr.dt_cancelled,
      order_dtl.product_id,
      order_dtl.quantity,
      order_dtl.price,
      products.product_name,
      products.product_image,
      order_hdr.status');
    $builder->join('order_dtl','order_dtl.order_hdr_id=order_hdr.id');
    $builder->join('products','order_dtl.product_id=products.id');
    if(!empty($orderId)){
      $builder->where('order_hdr.id', $orderId);
    }
   
    if(!empty($userId)){
      $builder->where('order_hdr.user_id', $userId);
    }
    
    $builder->orderBy('order_hdr.id','DESC');
    $query = $builder->get()->getResult();

    return $query;
  }

  function updateOrderStatus($orderId,$status){
    date_default_timezone_set('Asia/Singapore');
    $curDate = date('y-m-d h:i:s');

    $builder = $this->db->table('order_hdr');
    if($status === 'S'){
      $builder->set('dt_served',$curDate);
    }

    if($status === 'P'){
      $builder->set('dt_paid',$curDate);
    }
    
    $builder->set('status',$status);
    $builder->where('id', $orderId);
    return $builder->update();
  }

  function searchProducts($searchString){
    $builder = $this->db->table('products');
    $builder->select('products.id,
      products.product_name,
      products.product_category,
      products.cur_price_a,
      products.product_image,
      categories.category_name');
    $builder->join('categories', 'products.product_category = categories.id');
    $builder->like('products.product_name',$searchString);
    $builder->orWhere('products.cur_price_a',$searchString);
    $builder->orLike('categories.category_name',$searchString);
    $builder->orderBy('products.id','DESC');
    $query = $builder->get()->getResult();
    return $query;
  }

  function saveProduct($data){
    $this->db->table('products')
      ->insert($data);
    $productId = $this->db->insertID();
    return $productId;
  }

  function saveCategory($data){
    $this->db->table('categories')
      ->insert($data);
    $categoryId = $this->db->insertID();
    return $categoryId;
  }

  function deleteCategory($id){
   return $this->db->table('categories')
      ->where('id', $id)
      ->delete();
  }

  function addHotProduct($data){
    $dataF = [
      'product_id' => $data->product_id
    ];
    $this->db->table('hot_products')
      ->insert($dataF);
    $hotProductId = $this->db->insertID();
    return $hotProductId;

  }
}