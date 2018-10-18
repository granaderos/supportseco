<?php
namespace Base;

class SECore
{
    private $db, $cookieHandler, $language;
    
    public function __construct()
    {
        $this->db = new Db();
        $this->cookieHandler = new CookieHandler();
        
        $this->setLanguage();
        
        return true;
    }
    
    public function Db()
    {
        return $this->db;
    }
    
    private function setLanguage()
    {
        if ($this->cookieHandler->checkCookie("language")) {
            $this->language =  $this->getCookieValue("language");    
        } else {
            $this->language = "en_EN";
        }
        
        $this->updateLanguage();
    }
    
    private function updateLanguage()
    {
        $language = new LanguageHandler($this->language);
    }
}
