<?php namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;

class UsersModel{
  protected $db;

  public function __construct(ConnectionInterface &$db){
    $this->db =& $db;
  }

  function login($email, $password){
    $builder = $this->db->table('users');
    $builder->select('*');
    //$builder->where('password', $password);
    $builder->where('email', $email);
    $builder->where('is_approved', true);
    $query = $builder->get()->getResult();

    return $query;
  }

  function getUserList(){
    $builder = $this->db->table('users');
    $builder->select('id, 
      first_name,
      last_name,
      middle_name,
      suffix,
      email,
      user_type, 
      is_approved
    ');
    $query = $builder->get()->getResult();
    return $query;
  }

  function addUser($data){

    $builder = $this->db->table('users');
    $builder->where('email', $data->email);
    $query = $builder->get()->getResult();

    if(count($query)> 0){
      return -1;
    }
    $dataF = [
      'email' => $data->email,
      'password' => $data->password,
      'first_name' => $data->first_name,
      'middle_name' => $data->middle_name,
      'last_name' => $data->last_name,
      'user_type' => $data->user_type,
      'is_approved' => $data->is_approved
    ];

    $this->db->table('users')
      ->insert($dataF);
    $userId = $this->db->insertID();
    return $userId;
  }

  function updateUser($data){
    $dataF = [
      //'email' => $data->email,
      'first_name' => $data->first_name,
      'middle_name' => $data->middle_name,
      'last_name' => $data->last_name,
      'user_type' => $data->user_type,
      'is_approved' => $data->is_approved
    ];

    $builder = $this->db->table('users');
    $builder->where('id', $data->id);
    $builder->update($dataF);
  }
}