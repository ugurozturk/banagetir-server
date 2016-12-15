<?php

class LogKategori extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $log_kategori_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $log_kategori_adi;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("banagetir");
        $this->hasMany('log_kategori_id', 'Logs', 'log_kategori_id', ['alias' => 'Logs']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'log_kategori';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return LogKategori[]|LogKategori
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return LogKategori
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
