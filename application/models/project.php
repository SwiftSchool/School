<?php

/**
 * The Project Model
 *
 * @author Hemant Mann
 */
class Project extends Shared\Model {
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
     * @length 20
     *
     * @label marks|grade scored
     */
    protected $_score;

}
