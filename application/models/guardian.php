<?php

/**
 * The Parent Model
 *
 * @author Hemant Mann
 */
class Guardian extends Shared\Model {

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
    protected $_scholar_user_id;

	/**
     * @column
     * @readwrite
     * @type text
     * @length 20
     *
     * @value Father|Mother|Guardian
     */
    protected $_relation;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_occupation;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_qualification;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_location_id;
}
