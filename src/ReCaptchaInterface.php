<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.07.18
 * Time: 11:46
 */

namespace Vf92\Recaptcha;

use Bitrix\Main\SystemException;

interface ReCaptchaInterface
{
    /**
     * @param string $additionalClass
     *
     * @param bool   $isAjax
     *
     * @return string
     */
    public function getCaptcha($additionalClass = '', $isAjax = false);

    /**
     * @return array
     */
    public function getParams();

    public function addJs();

    public function addJsAsync();

    /**
     * @return string
     */
    public function getJs();

    /**
     * @param string $recaptcha
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @return bool
     */
    public function checkCaptcha($recaptcha = '');
}