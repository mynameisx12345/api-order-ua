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
}