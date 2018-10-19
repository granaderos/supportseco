<?php
namespace Base;

class CookieHandler
{
    private $cookies;
    public function __construct()
    {
        $this->cookies = $_COOKIES;
        $this->sanitizeCookies();

        return true;
    }

    /**
     * Sanitizing the Cookies Array to prevent Injection
     *
     * @author Enorion <enorion@supports.eco>
     * @return boolean
     */
    private function sanitizeCookies()
    {
        foreach($this->cookies as $name => $cookie) {
            $this->cookies[$name] = htmlspecialchars($cookie);
        }

        return true;
    }

    /**
     * Checks a given name exists in the Cookies Array
     *
     * @author Enorion <enorion@supports.eco>
     * @return boolean
     */
    public function checkCookie($name)
    {
        if (array_key_exists($name,$this->cookies)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the value of a given name from the Cookies Array
     *
     * @author Enorion <enorion@supports.eco>
     * @todo Implement $this->checkCookie($name)
     * @return mixed
     */
    public function getCookieValue($name)
    {
        return $this->cookies[$name];
    }
}   
