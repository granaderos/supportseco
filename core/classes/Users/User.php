<?php
namespace Users;
use \Base\SECore;

class User extends SECore
{
    private $username, $donationAmountPublic, $donationAmountPrivate, $donations = array();    

    public function __construct($username)
    {
        $this->username = $username;
        $this->getUserdataByUsername();
        
        return true;   
    }
    
    /**
    * Adding Userdata by a given Username
    *
    * @author Enorion <enorion@supports.eco>
    * @return boolean
    **/
    private function getUserdataByUsername()
    {
        return true;        
    }
}
