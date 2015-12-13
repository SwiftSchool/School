<?php

/**
 * The Events Model
 *
 * @author Hemant Mann
 */
class Event extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
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
     * @type datetime
     */
    protected $_start;

    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_end;

    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_allDay;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_school_id;

}
