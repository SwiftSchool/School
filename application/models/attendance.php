<?php

/**
 * The Attendance Model
 *
 * @author Hemant Mann
 */
class Attendance extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_organization_id;

	/**
     * @column
     * @readwrite
     * @type date
     *
     * @validate required, min(4), max(4)
     */
    protected $_date;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_remarks;
}
