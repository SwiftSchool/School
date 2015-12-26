<?php

/**
 * The School Model
 *
 * @author Hemant Mann
 */
class Organization extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, min(5), max(100)
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_location_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 13
     * 
     * @validate required, min(10), max(13)
     */
    protected $_phone;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_logo;

}
