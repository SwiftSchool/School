<?php

/**
 * The Batch Model
 *
 * @author Hemant Mann
 */
class Batch extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 10
     *
     * @value X, IX, IV etc
     */
    protected $_class;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     *
     * @value A|B|C
     */
    protected $_section;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 4
     *
     * @value 2015, 2014 etc
     */
    protected $_session;
}