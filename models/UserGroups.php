<?php

class UserGroups extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_group_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_group_name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("banagetir");
        $this->hasMany('user_group_id', 'Users', 'user_group_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_groups';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserGroups[]|UserGroups
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserGroups
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
