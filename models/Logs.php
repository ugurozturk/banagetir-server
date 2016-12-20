<?php

namespace Models\Verilerim;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;

class Logs extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $log_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $log_kategori_id;

     /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $log_detail;
    
    /**
     *
     * @var date
     * @Column(type="TIMESTAMP", nullable=false)
     */
    public $kayit_tarihi;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("banagetir");
        $this->belongsTo('log_kategori_id', '\LogKategori', 'log_kategori_id', ['alias' => 'LogKategori']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'logs';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Logs[]|Logs
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Logs
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
