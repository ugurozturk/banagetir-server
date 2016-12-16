<?php

namespace Models\Verilerim;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;

class UserFavlist extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_favlist_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $urun_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $kayit_tarihi;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("banagetir");
        $this->belongsTo('urun_id', '\Urunler', 'urun_id', ['alias' => 'Urunler']);
        $this->belongsTo('user_id', '\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_favlist';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserFavlist[]|UserFavlist
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserFavlist
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
