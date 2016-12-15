<?php

class UrunYorumlar extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $urun_yorumlari_id;

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
    public $puan_hiz;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $puan_paketleme;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $puan_lezzet;

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
        return 'urun_yorumlar';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UrunYorumlar[]|UrunYorumlar
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UrunYorumlar
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
