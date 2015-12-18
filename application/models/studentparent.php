<?php

/**
 * The Parent Model
 *
 * @author Hemant Mann
 */
class StudentParent extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 20
     *
     * @value Father|Mother|Guardian
     */
    protected $_relation;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     * @index
     *
     * @value required, min(10), max(10)
     */
    protected $_phone;
}
