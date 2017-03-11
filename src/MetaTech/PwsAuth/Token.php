<?php
/*
 * This file is part of the PwsAuth package.
 *
 * (c) meta-tech.academy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MetaTech\PwsAuth;

/*!
 * PwsAuth token
 * 
 * @package         MetaTech\PwsAuth
 * @class           Token
 * @author          a-Sansara
 * @date            2016-05-02 13:16:01 CET
 */
class Token
{
    /*! @protected @var $type */
    protected $type  = null;
    /*! @protected @var $ident */
    protected $ident = null;
    /*! @protected @var $date */
    protected $date  = null;
    /*! @protected @var $token */
    protected $value = null;
    /*! @protected @var $noise */
    protected $noise = null;

    /*!
     * @constructor
     * @param       str     $type
     * @param       str     $ident
     * @param       str     $date
     * @param       str     $value
     * @param       str     $noise
     * @public
     */
    public function __construct($type, $ident, $date, $value, $noise)
    {
        $this->type  = $type;
        $this->ident = $ident;
        $this->date  = $date;
        $this->value = $value;
        $this->noise = $noise;
    }

    /*!
     * desc
     * 
     * @method      getType
     * @public
     * @return      str
     */
    public function getType()
    {
        return $this->type;
    }

    /*!
     * desc
     * 
     * @method      getIdent
     * @public
     * @return      str
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /*!
     * desc
     * 
     * @method      getDate
     * @public
     * @return      str
     */
    public function getDate()
    {
        return $this->date;
    }

    /*!
     * desc
     * 
     * @method      getValue
     * @public
     * @return      str
     */
    public function getValue()
    {
        return $this->value;
    }

    /*!
     * desc
     * 
     * @method      getNoise
     * @public
     * @return      str
     */
    public function getNoise()
    {
        return $this->noise;
    }

}
