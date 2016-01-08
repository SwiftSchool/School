<?php
/**
 * The Fee Collection Model
 *
 * @author Hemant Mann
 */
class FeeCollection extends Shared\Model {
	
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
     * @type integer
     * @index
     *
     * @validate required, numeric
     */
    protected $_fee_id;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 10,2
     *
     * @validate required
     */
    protected $_amount;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 32
     */
    protected $_mode;

	/**
     * @column
     * @readwrite
     * @type text
     * @length 64
     */
    protected $_ref_id;
}