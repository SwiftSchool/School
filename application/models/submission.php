<?php

/**
 * The Submission Model
 *
 * @author Hemant Mann
 */
class Submission extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @validate required, numeric
     */
    protected $_assignment_id;

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
     * @type text
     * @length 100
     *
     * @value File Name
     * @validate required, max(100)
     */
    protected $_response;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @value 5 (best), 1 (worst)
     * @validate required, numeric, max(1), min(1)
     */
    protected $_grade;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     *
     * @validate max(255)
     */
    protected $_remarks;
}