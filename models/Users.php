<?php

namespace Models\Verilerim;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;

class Users extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_group_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_kullaniciadi;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $user_sifre;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_adi;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_soyadi;

    /**
     *
     * @var string
     * @Column(type="string", length=25, nullable=false)
     */
    public $user_tel;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $user_email;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $user_dogumtarihi;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $user_adres;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $user_adreskodu;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $aktif;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $kayit_tarihi;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("banagetir");
        $this->hasMany('user_id', 'Siparisler', 'user_id', ['alias' => 'Siparisler']);
        $this->hasMany('user_id', 'UrunYorumlar', 'user_id', ['alias' => 'UrunYorumlar']);
        $this->hasMany('user_id', 'UserFavlist', 'user_id', ['alias' => 'UserFavlist']);
        $this->belongsTo('user_group_id', '\UserGroups', 'user_group_id', ['alias' => 'UserGroups']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]|Users
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
