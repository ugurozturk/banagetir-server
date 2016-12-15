<?php

namespace Models\Verilerim;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;


class Bayiler extends Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $bayi_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $bayi_kullaniciadi;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $bayi_sifre;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $bayi_adi;

    /**
     *
     * @var string
     * @Column(type="string", length=25, nullable=false)
     */
    public $bayi_tel;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $bayi_email;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $bayi_adres;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $bayi_adreskodu;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $vergi_numarasi;

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
        $this->hasMany('bayi_id', 'Urunler', 'bayi_id', ['alias' => 'Urunler']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'bayiler';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Bayiler[]|Bayiler
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Bayiler
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'bayi_kullaniciadi',
            new UniquenessValidator([
                'model' => $this,
                'message' => 'Kullanıcı Adı zaten kullanımda',
            ])
        );

        $validator->add(
            'bayi_email',
            new UniquenessValidator([
                'model' => $this,
                'message' => 'Email zaten kullanımda',
            ])
        );

        return $this->validate($validator);
    }

}
