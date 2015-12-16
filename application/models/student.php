<?php

/**
 * The Student Model
 *
 * @author Hemant Mann
 */
class Student extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, min(5), max(100)
     */
    protected $_father_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, min(5), max(100)
     */
    protected $_mother_name;

    /**
     * @column
     * @readwrite
     * @type date
     */
    protected $_dob;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * 
     * @validate required, min(10), max(255)
     */
    protected $_address;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_school_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, min(5), max(100)
     */
    protected $_roll_no;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * @index
     */
    protected $_class;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * @index
     */
    protected $_section;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;

}
