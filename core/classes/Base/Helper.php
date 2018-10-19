<?php
namespace Base;

class Helper
{
    /**
     * Returns a pretty version of an Array
     *
     * @param   $array
     * @return  mixed
     */
    static public function echoarray($array)
    {
        return print("<pre>".print_r($array,true)."</pre>");
    }

    /**
     * Unnecessary sanitization for Excel tables
     *
     * @param   $string
     * @return  string
     */
    static public function removeSpecialChars($string)
    {
        $string = preg_replace ( '/[^a-z0-9 ]/i', '', $string );

        return $string;
    }

    /**
     * Check if String is UTF-8 encoded
     *
     * @param   $string
     * @return  bool
     */
    static public function checkUtf8($string)
    {
        if (mb_detect_encoding($string,'UTF-8',true) === false) return false;
        else return true;
    }

    /**
     * Recursively goes through an array and encodes non-utf8 to utf8
     *
     * @param   $array
     * @return  array
     */
    static public function utf8Array($array)
    {
        foreach($array as $key => $value)
        {
            if(is_array($value))
            {
                $array[$key] = self::utf8Array($value);
            }
            else
            {
                if (self::checkUtf8($value) === false) $value = utf8_encode($value);
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Checks if the Haystack String starts with the
     * given Needle String
     *
     * @param   $haystack
     * @param   $needle
     * @return  bool
     */
    static public function startsWith($haystack,$needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Macht einen preg_match auf die $string Zeichenkette und gibt zurück was sich zwischen $start und $end befindet.
     *
     * @param $string
     * @param string $start
     * @param string $end
     * @return mixed
     */
    static public function getBetween($string, $start = "", $end = ""){
        preg_match("/(?<=".$start.").*?(?=".$end.")/s", $string, $result);

        return $result;
    }
}
