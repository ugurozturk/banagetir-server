<?php

class Siparisler extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $siparis_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $urun_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $siparis_tarihi;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $onay_tarihi;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $aktif;

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
        return 'siparisler';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Siparisler[]|Siparisler
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Siparisler
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
