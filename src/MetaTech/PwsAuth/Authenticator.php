<?php
/*
 * This file is part of the pws-auth package.
 *
 * (c) meta-tech.academy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MetaTech\PwsAuth;

use MetaTech\Util\Tool;
use MetaTech\PwsAuth\Token;
use MetaTech\PwsAuth\AuthenticateException;

/*!
 * a simple class to authenticate access throught webservices using Pluie\Auth\Token
 * and PwsAuth Protocol
 * 
 * @package     MetaTech\PwsAuth
 * @class       Authenticator
 * @author      a-Sansara
 * @date        2016-05-02 13:08:01 CET
 * 
 */
class Authenticator
{
    /*! @constant DATE_FORMAT */
    const DATE_FORMAT  = 'smHdiy';
    /*! @constant DATE_LENGTH */
    const DATE_LENGTH  = 12;
    /*! @constant DATE_LENGTH */
    const DEFAULT_ALGO = 'sha256';

    /*! @protected @var [assoc] $config */
    protected $config;

    /*!
     * @constructor
     * @public
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /*!
     * check if specified Token is a valid token
     * 
     * @method      isValid
     * @public
     * @param       MetaTech\PwsAtuh\Token  $token
     * @return      bool
     */
    public function isValid(Token $token = null)
    {
        return !is_null($token) && $token->getType() == $this->config['type'] && $this->checkObfuscatePart($token);
    }

    /*!
     * generate a unique signature at given time for specifyed user
     * 
     * @method      sign
     * @public
     * @param       str     $dtime  given time in sqldatetime format
     * @param       str     $login  the user login
     * @param       str     $key    the user key
     * @return      str
     */
    public function sign($dtime, $login, $key, $length=null)
    {
        $str = Tool::concat($this->config['hash']['sep'], [$dtime, $login, $this->getUserSalt($login), $key]);
        return substr(hash($this->config['hash']['algo'], $str), is_null($length) ? - $this->config['hash']['length'] : - $length);
    }

    /*!
     * generate the salt for a specific user
     * 
     * @method      getUserSalt
     * @public
     * @param       str     $login   the user login
     * @return      str
     */
    public function getUserSalt($login)
    {
        return substr(
            hash(self::DEFAULT_ALGO, $login . $this->config['salt']['common']), 
            $this->config['salt']['user.index'], 
            $this->config['salt']['user.length']
        );
    }

    /*!
     * generate noise to obfuscate token
     * 
     * @method      obfuscate
     * @orivate
     * @param       str     $data
     * @return      str
     */
    private function obfuscate($data, $date)
    {
        return substr(
            hash(self::DEFAULT_ALGO, $date . $data . $this->config['salt']['common']), 
            - $this->config['hash']['session.index']
        );
    }

    /*!
     * @method      formatDate
     * @private
     * @param       str     $date   sqldatetime
     * @return      str
     */
    private function formatDate($date)
    {
        return Tool::formatDate($date, Tool::TIMESTAMP_SQLDATETIME, self::DATE_FORMAT);
    }

    /*!
     * @method      formatDate
     * @private
     * @param       str     $formated   DATE_FORMAT
     * @return      str
     */
    private function unformatDate($formated)
    {
        return Tool::formatDate($formated, self::DATE_FORMAT, Tool::TIMESTAMP_SQLDATETIME);
    }

    /*!
     * check valid noise obfuscation
     * 
     * @method      checkObfuscatePart
     * @public
     * @param       MetaTech\PwsAtuh\Token  $token
     * @return      bool
     */
    public function checkObfuscatePart(Token $token)
    {
        $tokenValue = $token->getValue();
        return substr($tokenValue, 0, $this->config['hash']['session.index']) == $this->obfuscate($this->deobfuscate($tokenValue), $token->getDate());
    }

    /*!
     * deoffuscate token
     * 
     * @method      deobfuscate
     * @orivate
     * @param       str     $data
     * @return      str
     */
    private function deobfuscate($data)
    {
        return substr($data, $this->config['hash']['session.index']);
    }

    /*!
     * @method      getSessionId
     * @orivate
     * @param       MetaTech\PwsAtuh\Token  $token
     * @return      str
     */
    public function getSessionId(Token $token)
    {
        return $this->deobfuscate($token->getValue());
    }

    /*!
     * check validity of Token
     * 
     * @mehtod      check
     * @public
     * @param       MetaTech\PwsAtuh\Token  $token
     * @param       str                     $login
     * @return      bool
     */
    public function check(Token $token = null, $login = '')
    {
        return !is_null($token) && !empty($login) && $this->deobfuscate($token->getValue()) == $this->sign($token->getDate(), $login, $token->getIdent());
    }

    /*!
     * @method      generateNoise
     * @public
     * @param       str     $data
     * @return      str
     */
    public function generateNoise($data)
    {
        return substr(hash(self::DEFAULT_ALGO, str_shuffle($data)), - $this->config['hash']['noise.length']); 
    }

    /*!
     * @method      generateToken
     * @public
     * @param       str     $login
     * @param       str     $key
     * @param       str     $sessid|null
     * @return      MetaTech\PwsAuth\Token
     */
    public function generateToken($login, $key, $sessid=null)
    {
        $date       = Tool::now();
        $sessid     = is_null($sessid) ? $this->sign($date, $login, $key) : $sessid;
        $dt         = $this->formatDate($date);
        $tokenValue = $this->obfuscate($sessid, $date) . $sessid;
        $noise      = $this->generateNoise($tokenValue);
        return new Token($this->config['type'], $key, $date, $tokenValue, $noise);
    }

    /*!
     * @method      generateResponseHeader
     * @public
     * @param       MetaTech\PwsAuth\Token  $token
     * @param       str                     $login
     * @return      str
     */
    public function generateResponseHeader(Token $token, $login)
    {
        return hash(
            self::DEFAULT_ALGO, 
            $this->formatDate($token->getDate()) . $this->getUserSalt($login) . $token->getValue()
        );
    }

    /*!
     * @method      checkResponseHeader
     * @public
     * @param       MetaTech\PwsAuth\Token  $token
     * @param       str                     $login
     * @return      []
     */
    public function checkResponseHeader(Token $token, $login, $responseToken)
    {
        return $this->generateResponseHeader($token, $login) == $responseToken;
    }

    /*!
     * @method      generateHeader
     * @public
     * @param       str     $login
     * @param       str     $key
     * @param       str     $sessid
     * @return      []
     */
    public function generateHeader($login, $key, $sessid=null)
    {
        $token = $this->generateToken($login, $key, $sessid);
        $ndate = $this->formatDate($token->getDate());
        return array(
            $this->config['header']['auth'] .': ' . $token->getType() . ' ' . $ndate . $token->getValue() . $token->getNoise(),
            $this->config['header']['ident'].': ' . $token->getIdent()
        );
    }

    /*!
     * @method      generatePostVars
     * @public
     * @param       str     $login
     * @param       str     $key
     * @param       str     $tokenName
     * @param       str     $keyName
     * @return      []
     */
    public function generatePostVars($login, $key, $tokenName='apitkn', $keyName='apikey')
    {
        $token = $this->generateToken($login, $key, null);
        $ndate = $this->formatDate($token->getDate());
        return array(
            $tokenName => $ndate . $token->getValue() . $token->getNoise(),
            $keyName   => $key
        );
    }

    /*!
     * get token from specified $noisedToken for specified key.
     * 
     * @method      getTokenFromString
     * @public
     * @param       str     $noisedToken
     * @param       str     $key
     * @return      MetaTech\PwsAuth\Token
     */
    public function getTokenFromString($noisedToken, $key)
    {
        $date       = substr($noisedToken, 0, self::DATE_LENGTH);
        $tokenValue = substr($noisedToken, self::DATE_LENGTH, -$this->config['hash']['noise.length']);
        $noise      = substr($noisedToken, -$this->config['hash']['noise.length']);
        return new Token($this->config['type'], $key, $this->unformatDate($date), $tokenValue, $noise);
    }

    /*!
     * get token from specified $header or request headers.
     * 
     * @method      getToken
     * @public
     * @param       [assoc]     $headers
     * @throw       MetaTech\PwsAuth\AuthenticateException
     * @return      MetaTech\PwsAuth\Token
     */
    public function getToken($headers = null)
    {
        $token = null;
        try {
            if (is_null($headers)) {
                $headers = apache_request_headers();
            }
            if (isset($headers[$this->config['header']['auth']]) && isset($headers[$this->config['header']['ident']])) {
                $tokenValue = $headers[$this->config['header']['auth']];
                $ident      = $headers[$this->config['header']['ident']];
                if (preg_match('/(?P<type>[a-z\d]+) (?P<noised>.*)/i', $tokenValue, $rs)) {
                    $token  = $this->getTokenFromString($rs['noised'], $ident);
                    if ($token->getType() != $rs['type']) {
                        throw new \Exception('wrong type');
                    }
                }
            }
            else {
                throw new \Exception('missing required headers');
            }
        }
        catch(\Exception $e) {
            throw new AuthenticateException("invalid authentication protocol : ".$e->getMessage());
        }
        return $token;
    }

    /*!
     * read header generate by generateHeader
     *
     * @method      readHeader
     * @public
     * @param       [str]   $arrHeaders
     * @return      [assoc]
     */
    public function readHeader($arrHeaders)
    {
        $headers = [];
        if (is_array($arrHeaders)) {
            foreach($arrHeaders as $h) {
                $rs = preg_split('/:/', $h);
                if (count($rs)==2) {
                    $headers[$rs[0]] = trim($rs[1]);
                }
            }
        }
        return $headers;
    }
}
