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
      user_type
    ');
    $query = $builder->get()->getResult();
    return $query;
  }

  function addUser($data){
    $dataF = [
      'email' => $data->email,
      'password' => $data->password,
      'first_name' => $data->first_name,
      'middle_name' => $data->middle_name,
      'last_name' => $data->last_name,
      'user_type' => $data->user_type
    ];

    $this->db->table('users')
      ->insert($dataF);
    $userId = $this->db->insertID();
    return $userId;
  }
}