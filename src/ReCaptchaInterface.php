<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.07.18
 * Time: 11:46
 */

namespace Vf92\ReCaptcha;

use Bitrix\Main\SystemException;
use Vf92\ReCaptcha\Exception\NotFountSecretKey;

interface ReCaptchaInterface
{
    /**
     * ReCaptchaService constructor.
     *
     * @param array $parameters
     *
     * @throws NotFountSecretKey
     */
    public function __construct(array $parameters);

    /**
     * @param string $additionalClass
     *
     * @param bool   $isAjax
     *
     * @return string
     */
    public function getCaptcha($additionalClass = '', $isAjax = false);

    public static function addJs();

    public static function addJsAsync();

    /**
     * @param string $key
     * @param string $additionalClass
     * @param bool   $isAjax
     *
     * @return string
     */
    public static function getCaptchaStatic($key, $additionalClass = '', $isAjax = false);

    /**
     * @return string
     */
    public static function getJs();

    /**
     * @param string $recaptcha
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @return bool
     */
    public function check($recaptcha = '');

    /**
     * @param string $recaptcha
     * @param string $secretKey
     * @param string $serviceUri
     *
     * @return bool
     */
    public static function checkCaptcha($secretKey, $recaptcha = '', $serviceUri = '');

    /**
     * @return string
     */
    public function getKey();
}