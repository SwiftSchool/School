<?php

/**
 * The Course Model
 *
 * @author Hemant Mann
 */
class Course extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * @index
     *
     * @validate required, min(5), max(50)
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
    protected $_grade_id;

}
