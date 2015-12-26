<?php

/**
 * The Student Model
 *
 * @author Hemant Mann
 */
class Scholar extends Shared\Model {

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
     * @type date
     */
    protected $_dob;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_location_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_organization_id;

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
}