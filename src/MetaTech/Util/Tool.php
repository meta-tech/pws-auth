<?php
/*
 * This file is part of the pws-auth package.
 *
 * (c) meta-tech.academy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MetaTech\Util;

/*!
 * @package   MetaTech\Util
 * @class     Tool
 * @static
 * @author    a-Sansara
 * @date      2014-12-11 17:46:29 CET
 */
class Tool
{
    /*! @var TIMESTAMP_SQLDATETIME default sqldatetime timestamp */
    const TIMESTAMP_SQLDATETIME = 'Y-m-d H:i:s';
    /*! @var TIMESTAMP_SQLDATE default sqldate timestamp */
    const TIMESTAMP_SQLDATE     = 'Y-m-d';

    /*!
     * @constructor
     * @protected
     */
    protected function __construct()
    {
        
    }


    /*!
     * @method      now
     * @public
     * @static
     * @param       bool    $full   full format
     * @return      str
     */
    public static function now($full = true)
    {
        return date($full ? self::TIMESTAMP_SQLDATETIME : self::TIMESTAMP_SQLDATE);
    }

   /*!
     * @method      formatDate
     * @public
     * @static
     * @param       str     $date
     * @param       str     $fromFormat
     * @param       str     $toFormat
     * @return      str
     */
    public static function formatDate($date, $fromFormat='d-m-Y', $toFormat='Y-m-d')
    {   
        $dt = \DateTime::createFromFormat($fromFormat, $date);
        return !$dt ? null : $dt->format($toFormat);
    }


    /*!
     * @public
     * @static
     * @param float $time
     * @return  string sqldatetime format
     */
    public static function dateFromTime($time=null)
    {
        return date(self::TIMESTAMP_SQLDATETIME, $time==null ? intval(microtime(true)) : intval($time));
    }

    /*!
     * concatenate various items in $list separate with specifyed separator $sep
     *
     * @method      concat
     * @public
     * @param       str     $sep    the used separator to concatenate items in $list
     * @param       [str]   $list   the list of items to concatenate
     * @return      str
     */
    public static function concat($sep, $list)
    {
        $value = array_shift($list);
        foreach ($list as $item) {
            $value .= $sep . $item;
        }
        return $value;
    }

    /*!
     * desc
     *
     * @method      compact
     * @public
     * @param       [str]   $source
     * @param       [str]   $fields
     * @return      str
     */
    public static function compact($source, $fields)
    {
        $data = array();
        foreach ($fields as $field) {
            if (isset($source[$field])) $data[$field] = $source[$field];
        }
        return $data;
    }
}
