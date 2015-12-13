<?php

/**
 * The Enrollment Model
 *
 * @author Hemant Mann
 */
class Enrollment extends Shared\Model {
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

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     *
     * @label passed|current|failed
     */
    protected $_status;

}
