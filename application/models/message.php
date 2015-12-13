<?php

/**
 * The Message Model
 *
 * @author Hemant Mann
 */
class Message extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     *
     * @label 'User' | 'Teacher' | 'Admin'
     */
    protected $_from;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_from_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     *
     * @label 'Student' | 'Teacher' | 'Admin' | '*' (For all)
     */
    protected $_to;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     *
     * @label All (for all students|parents|teacher) | specific_id (generally case of a message)
     */
    protected $_to_id;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 12
     *
     * @label 'Message|Notification'
     */
    protected $_type;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 10
     *
     * @label 'Read|Unread'
     */
    protected $_status;

    /**
     * @column
     * @readwrite
     * @type text
     *
     * @label Subject of the message
     */
    protected $_subject;    

    /**
     * @column
     * @readwrite
     * @type text
     *
     * @label can contain perfectly valid HTML markup
     */
    protected $_message;

}
