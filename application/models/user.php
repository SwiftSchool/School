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
     * @length 20
     * @index
     * 
     * @validate max(20)
     * @label Type of Account - student | educator | educator + admin = true (school's admin) | central (Top level admin)
     */
    protected $_type = "student";

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     * 
     * @validate numeric, min(10), max(10)
     */
    protected $_phone;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 32
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
