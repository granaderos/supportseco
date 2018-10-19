<?php
namespace Base;

class DbStorage
{
    public static function getDbData($part = 'all')
    {
        $return = array("test" => array('host'      => 'xxx.xxx.xxx.xxx',
                                        'dbname'    => 'dbname',
                                        'user'      => 'dbuser',
                                        'pass'      => 'dbuserpass',
                                        'port'      => 3306,
                                        'driver'    => 'mysql'),
                       );
        if (in_array($part,$return)) {
            return $return[$part];
        }

        return $return;
    }
}
