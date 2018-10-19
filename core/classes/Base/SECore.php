<?php
namespace Base;

class SECore
{
    private $db, $cookieHandler, $languageHandler, $language;
    
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
    
    public function Language()
    {
        return $this->languageHandler;
    }
    
    public function Cookie()
    {
        return $this->cookieHandler;
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
        $this->languageHandler = new LanguageHandler($this->language);
    }
}
