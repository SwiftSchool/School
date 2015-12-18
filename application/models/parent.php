<?php

/**
 * The Parent Model
 *
 * @author Hemant Mann
 */
class Parent extends Shared\Model {
	/**
     * @column
     * @readwrite
     * @type text
     * @length 20
     *
     * @value Father|Mother|Guardian
     */
    protected $_relation;
}
