<?php

/**
 * The User Model
 *
 * @author Hemant Mann
 */
class User extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required, min(5), max(100)
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required, min(3), max(100)
     */
    protected $_username;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     * 
     * @validate max(255)
     * @label email address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     * 
     * @validate max(255)
     * @label Type of Account - student | teacher | teacher + admin = true (school's admin) | central + admin = true
     */
    protected $_type = "student";

    /**
     * @column
     * @readwrite
     * @type text
     * @length 13
     * 
     * @validate min(10), max(13)
     */
    protected $_phone;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, alpha, min(8), max(32)
     * @label password
     */
    protected $_password;
    
    /**
    * @column
    * @readwrite
    * @type boolean
    */
    protected $_admin = false;

}
