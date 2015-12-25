<?php

/**
 * The Assignment Model
 *
 * @author Hemant Mann
 */
class Assignment extends Shared\Model {

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
     * @type integer
     * @index
     */
    protected $_organization_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_description;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_classroom_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_course_id;

    /**
     * @column
     * @readwrite
     * @type date
     */
    protected $_deadline;

}
