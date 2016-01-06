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
     * @type integer
     * @index
     *
     * @validate required, numeric
     * @value FA|SA|Mid Term|End Term
     */
    protected $_exam_id;

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
     * @type decimal
     * @length 10,2
     *
     * @validate required, min(1), max(5)
     */
    protected $_marks;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @validate required, numeric
     */
    protected $_grade_id;
}
