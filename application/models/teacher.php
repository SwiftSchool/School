<?php

/**
 * The Teacher Model
 *
 * @author Hemant Mann
 */
class Teacher extends Shared\Model {
    
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_school_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;

}
