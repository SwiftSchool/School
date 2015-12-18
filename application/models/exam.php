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
     * @index
     */
    protected $_start_date;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 30
     *
     */
    protected $_course_id;

}
