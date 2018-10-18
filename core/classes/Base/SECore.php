<?php
namespace Base;

class SECore
{
    private $db;
    
    public function __construct()
    {
        $this->db = new Db();
        
        return true;
    }
    
    public function Db()
    {
        return $this->db;
    }
}
