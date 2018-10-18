<?php
namespace Base;

class LanguageHandler
{
    private $languageCode, $strings = array();
    
    public function __construct($languageCode)
    {
        if ($this->checkLanguageCode($languageCode)) {
            $this->languageCode = $languageCode;
            if ($this->parseYml()) {
                return true;
            }
        }
        
        return false;
    }
    
    private function checkLanguageCode($languageCode)
    {
        $directory = '/core/languages';
        $files = array();
        $found = false;

        foreach (scandir($directory) as $file) {
            if ('.' === $file) continue;
            if ('..' === $file) continue;

            if ($file == $languageCode."yml") {
                $found = true;
                exit;
            }
        }
        
        if ($found == true) {
            return true;
        } else {
            return false;
        }
    }
    
    private function parseYml()
    {
        $this->strings = yaml_parse_file('/core/languages/'.$this->languageCode.'.yml');
        
        return true;
    }
    
    public function getByKey($key,$bindings = array())
    {
        if (in_array($key,$this->strings)) {
            $baseString = $this->strings[$key];
            if (!empty($bindings)) {
                 foreach($bindings AS $bindingName => $bindingValue) {
                     $baseString = str_replace("%".$bindingName."%",$bindingValue,$baseString);    
                 }
            }
            
            return $baseString;
        } else {
            return false;    
        }
    } 
    
    public function getAll()
    {
        return $this->strings;
    }
}
