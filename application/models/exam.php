<?php
/**
 * The Exam Model
 *
 * @author Hemant Mann
 */
class Exam extends Shared\Model {

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
    protected $_course_id;

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
     * @length 30
     * @index
     *
     * @value FA|SA|Mid Term|End Term
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type date
     */
    protected $_start_date;

    /**
     * @column
     * @readwrite
     * @type time
     */
    protected $_start_time;

    /**
     * @column
     * @readwrite
     * @type time
     */
    protected $_end_time;

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
     * @length 4
     */
    protected $_year;
}
