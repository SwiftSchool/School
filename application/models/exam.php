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
     * @type text
     * @length 30
     * @index
     */
    protected $_course_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 30
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
    protected $_commence;
}
