<?php

/**
 * The Teach Model - Contains which teacher teaches which course
 *
 * @author Hemant Mann
 */
class Teach extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * @index
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_type_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_teacher_id;

}
