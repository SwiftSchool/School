<?php

/**
 * The Location Model
 *
 * @author Faizan Ayubi
 */
class Location extends Shared\Model {

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
    protected $_address;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_city;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 18, 15
     */
    protected $_latitude;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 18, 15
     */
    protected $_longitude;

}
