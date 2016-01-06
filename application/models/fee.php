<?php
/**
 * The Fee Model
 *
 * @author Hemant Mann
 */
class Fee extends Shared\Model {
	
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
     * @type integer
     * @index
     *
     * * @validate required, numeric
     */
    protected $_organization_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required, min(3), max(100)
     */
    protected $_category;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_description;

    /**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required
     */
    protected $_start;

    /**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required
     */
    protected $_end;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 10,2
     *
     * @validate required
     */
    protected $_amount;
}