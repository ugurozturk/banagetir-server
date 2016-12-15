<?php

class Urunler extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $urun_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $bayi_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $kategori_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $marka_id;

    /**
     *
     * @var string
     * @Column(type="string", length=55, nullable=false)
     */
    public $urun_adi;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $birim_fiyat;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $kayit_tarihi;

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
        $this->hasMany('urun_id', 'Siparisler', 'urun_id', ['alias' => 'Siparisler']);
        $this->hasMany('urun_id', 'UrunYorumlar', 'urun_id', ['alias' => 'UrunYorumlar']);
        $this->hasMany('urun_id', 'UserFavlist', 'urun_id', ['alias' => 'UserFavlist']);
        $this->belongsTo('bayi_id', '\Bayiler', 'bayi_id', ['alias' => 'Bayiler']);
        $this->belongsTo('kategori_id', '\Kategoriler', 'kategori_id', ['alias' => 'Kategoriler']);
        $this->belongsTo('marka_id', '\Marka', 'marka_id', ['alias' => 'Marka']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'urunler';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Urunler[]|Urunler
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Urunler
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
