<?php

/**
 * The ClassRoom Model
 *
 * @author Hemant Mann
 */
class Classroom extends Shared\Model {

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
     * @length 4
     * @index
     *
     * @validate required, min(4), max(4)
     */
    protected $_year;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_grade_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 7
     * @index
     *
     * @validate required, min(1), max(7)
     */
    protected $_section;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     *
     * @validate max(255)
     */
    protected $_remarks;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_educator_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_timetable = null;

}
