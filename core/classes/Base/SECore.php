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

    /**
     * Return the Database Instance
     *
     * @author Enorion <enorion@supports.eco>
     * @return Db
     */
    public function Db()
    {
        return $this->db;
    }

    /**
     * Checking the language Cookie and setting the Language via the Handler
     * while using English as a fallback
     *
     * @author Enorion <enorion@supports.eco>
     * @return bool
     */
    private function setLanguage()
    {
        if ($this->cookieHandler->checkCookie("language")) {
            $this->language =  $this->getCookieValue("language");
        } else {
            $this->language = "en_EN";
        }

        $this->updateLanguage();

        return true;
    }

    /**
     * Instantiating the Language Handler and passing it into the class scope
     *
     * @author Enorion <enorion@supports.eco>
     * @return bool
     */
    private function updateLanguage()
    {
        $this->language = new LanguageHandler($this->language);

        return true;
    }
}
