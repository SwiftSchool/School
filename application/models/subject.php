<?php

/**
 * The Subject Model
 *
 * @author Hemant Mann
 */
class Subject extends Shared\Model {
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
     * @type text
     * @length 3
     * 
     * @validate required
     */
    protected $_max_score;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 2
     * 
     * @validate required
     */
    protected $_min_score;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_school_id;

}
