<?php

/**
 * The Enrollment Model
 *
 * @author Hemant Mann
 */
class Enrollment extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_scholar_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_classroom_id;

}
