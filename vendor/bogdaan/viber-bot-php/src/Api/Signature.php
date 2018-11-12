<?php

namespace Viber\Api;

/**
 * Api signature
 *
 * @author Novikov Bogdan <hcbogdan@gmail.com>
 */

if(!function_exists('hash_equals'))
{
    function hash_equals($str1, $str2)
    {
        if(strlen($str1) != strlen($str2))
        {
            return false;
        }
        else
        {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--)
            {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}

class Signature
{
    /**
     * Make signature value
     *
     * @param  string $messageBody request body
     * @param  string $token       bot token
     * @return string              signature
     */
    public static function make($messageBody, $token)
    {
        return hash_hmac('sha256', $messageBody, $token);
    }

    /**
     * Is message signatore valid?
     *
     * @param  string  $sign        from request headers
     * @param  string  $messageBody from request body
     * @param  string  $token       bot access token
     * @return boolean              valid or not
     */
    public static function isValid($sign, $messageBody, $token)
    {
        return hash_equals($sign, self::make($messageBody, $token));
    }
}
