<?php

/**
 * The School Model
 *
 * @author Hemant Mann
 */
class School extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, min(5), max(100)
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * 
     * @validate required, min(10), max(255)
     */
    protected $_address;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 13
     * 
     * @validate required, min(10), max(13)
     */
    protected $_phone;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_logo;

}
