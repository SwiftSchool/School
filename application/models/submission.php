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
     */
    protected $_assignment_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_scholar_id;

    /**
     * @column
     * @readwrite
     * @type text
     *
     * @value File Name | Text etc
     */
    protected $_response;
}