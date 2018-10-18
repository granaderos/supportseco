<?php
namespace Base;

class LanguageHandler
{
    private $languageCode, $strings;
    
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
        return = yaml_parse_file('/core/languages/'.$this->languageCode.'.yml');
    }
}
