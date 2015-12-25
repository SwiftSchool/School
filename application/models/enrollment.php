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
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_classroom_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_organization_id;

}
