<?php

/**
 * A Model class for linkedin meta
 *
 * @author Faizan Ayubi
 */
class Meta extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * @index
     */
    protected $_property;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_property_id;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     */
    protected $_meta_key;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 60
     * @index
     */
    protected $_meta_value;
    
}
