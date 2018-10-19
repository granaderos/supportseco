<?php
namespace Interfaces;

/**
 * Interface DbInterface
 * Interface for Database Connector classes
 *
 * @author      Enorion <enorion@supports.eco>
 * @version     1.0.0
 * @package     Interfaces
 */
Interface DbInterface
{
    /**
     * DbInterface constructor.
     *
     * @param   $db
     */
    function __construct($db);

    /**
     * Public function to get an existing connection or instantiate it.
     *
     * @param   $db
     * @param   $custom
     * @return  mixed
     */
    function getConnection($db,$custom);
}
