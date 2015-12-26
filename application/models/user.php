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
     * @length 15
     * 
     * @validate numeric, min(10), max(15)
     */
    protected $_phone;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 64
     * @index
     * 
     * @validate required, alpha, min(8), max(64)
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
