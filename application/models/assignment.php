<?php

/**
 * The Assignment Model
 *
 * @author Hemant Mann
 */
class Assignment extends Shared\Model {
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
    protected $_teacher_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_subject_id;

}
