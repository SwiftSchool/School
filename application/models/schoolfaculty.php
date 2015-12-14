<?php

/**
 * A School's Faculty model
 *
 * @author Hemant Mann
 */
class SchoolFaculty extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_school_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;
}