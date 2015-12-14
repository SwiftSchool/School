<?php

/**
 * The Performance Model
 *
 * @author Hemant Mann
 */
class Performance extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 8
     * 
     * @validate required
     */
    protected $_score;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_student_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_subject_id;

}
