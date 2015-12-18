<?php

/**
 * The Grade | Class Model
 *
 * @author Hemant Mann
 */
class Grade extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * @index
     *
     * @validate required, min(5), max(20)
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
    protected $_school_id;
}