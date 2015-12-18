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
    protected $_assignement_id;

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
     * @type text
     *
     * @value File Name | Text etc
     */
    protected $_response;
}