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
}