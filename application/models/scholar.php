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
     *
     * @validate required, numeric
     */
    protected $_user_id;
    
    /**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required
     */
    protected $_dob;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @validate required, numeric
     */
    protected $_location_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @validate required, numeric
     */
    protected $_organization_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * @index
     * 
     * @validate min(5), max(50)
     */
    protected $_roll_no;
}
