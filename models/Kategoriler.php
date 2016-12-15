<?php

class Kategoriler extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $kategori_id;

    /**
     *
     * @var string
     * @Column(type="string", length=55, nullable=false)
     */
    public $kategori_adi;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $ust_kategori_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $ust_kategori_id_1;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("banagetir");
        $this->hasMany('kategori_id', 'Kategoriler', 'ust_kategori_id_1', ['alias' => 'Kategoriler']);
        $this->hasMany('kategori_id', 'Urunler', 'kategori_id', ['alias' => 'Urunler']);
        $this->belongsTo('ust_kategori_id_1', '\Kategoriler', 'kategori_id', ['alias' => 'Kategoriler']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'kategoriler';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Kategoriler[]|Kategoriler
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Kategoriler
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
