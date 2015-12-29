<?php
/**
 * The ExamResult Model
 *
 * @author Hemant Mann
 */
class ExamResult extends Shared\Model {
	
    /**
     * @column
     * @readwrite
     * @type text
     * @length 30
     * @index
     *
     * @value FA|SA|Mid Term|End Term
     */
    protected $_exam_id;

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
     * @length 10
     */
    protected $_marks;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_grade_id;
}
