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

    /**
     * Checking if a language exists as a translation yml file
     *
     * @author Enorion <enorion@supports.eco>
     * @param string $languageCode
     * @return bool
     */
    private function checkLanguageCode($languageCode)
    {
        $directory = '/core/languages';
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

    /**
     * Parsing the YML file for the scope language
     *
     * @author Enorion <enorion@supports.eco>
     * @return bool
     */
    private function parseYml()
    {
        $this->strings = yaml_parse_file('/core/languages/'.$this->languageCode.'.yml');

        return true;
    }

    /**
     * Getting a scope translation string by key and binding placeholders if passed
     *
     * @author Enorion <enorion@supports.eco>
     * @param string $key
     * @param array $bindings
     * @return bool|mixed
     */
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

    /**
     * Returns the full array of translations from the scope
     *
     * @author Enorion <enorion@supports.eco>
     * @return array
     */
    public function getAll()
    {
        return $this->strings;
    }
}
