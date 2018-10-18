<?php
namespace Users;
use \Base\SECore;
class User extends SECore
{
    private $username, $donationAmountPublic, $donationAmountPrivate, $donations = array();

    public function __construct($username)
    {
        parent::__construct();
        $this->username = $username;
        $this->getUserdataByUsername();

        return true;
    }

    /**
     * Getting User Data by a Username
     *
     * @author Enorion <enorion@supports.eco>
     * @return bool
     */
    private function getUserdataByUsername()
    {
        return true;
    }
}
