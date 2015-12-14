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
     * @length 100
     * @index
     *
     * @validate required, min(5), max(100)
     */
    protected $_title;

    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required
     */
    protected $_description;    

    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * @index
     * 
     * @label MATH-101 etc
     * @validate required, min(5), max(20)
     */
    protected $_code;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     * 
     * @label 'Grade' | 'Marks'
     * @validate required
     */
    protected $_marking;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_school_id;

}
