<?php namespace Vf92\ReCaptcha;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Vf92\ReCaptcha\Exception\NotFountSecretKey;

/**
 * Class ReCaptcha
 * @package Vf92\ReCaptcha
 */
class ReCaptcha implements ReCaptchaInterface
{
    /**
     * @var ClientInterface
     */
    protected $guzzle;

    protected $parameters;

    protected static $serviceUri = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * ReCaptchaService constructor.
     *
     * @param array $parameters
     *
     * @throws NotFountSecretKey
     */
    public function __construct(array $parameters)
    {
        $client = new Client();
        $this->guzzle = $client;
        if (!empty($parameters['key']) || $parameters['secretKey']) {
            throw new NotFountSecretKey('Не установлен ключ(key) или секретный ключ(secretKey)');
        }
        if (!isset($parameters['serviceUrl'])) {
            $parameters['serviceUrl'] = static::$serviceUri;
        }
        $this->parameters = $parameters;
    }

    /**
     * @param string $additionalClass
     *
     * @param bool   $isAjax
     *
     * @return string
     */
    public function getCaptcha($additionalClass = '', $isAjax = false)
    {
        if (!$isAjax) {
            $script = '';
            static::addJs();
        } else {
            $script = static::getJs();
        }

        return $script . '<div class="g-recaptcha' . $additionalClass . '" data-sitekey="' . $this->parameters['key']
            . '"></div>';
    }

    /**
     * @param string $key
     * @param string $additionalClass
     * @param bool   $isAjax
     *
     * @return string
     */
    public static function getCaptchaStatic($key, $additionalClass = '', $isAjax = false)
    {
        if (!$isAjax) {
            $script = '';
            static::addJs();
        } else {
            $script = static::getJs();
        }

        return $script . '<div class="g-recaptcha' . $additionalClass . '" data-sitekey="' . $key . '"></div>';
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return ['sitekey' => $this->parameters['key']];
    }

    /**
     *
     */
    public static function addJs()
    {
        Asset::getInstance()->addJs('https://www.google.com/recaptcha/api.js?hl=ru');
    }

    /**
     *
     */
    public static function addJsAsync()
    {
        Asset::getInstance()->addString(static::getJs(), true, AssetLocation::AFTER_JS_KERNEL);
    }

    /**
     * @return string
     */
    public static function getJs()
    {
        return '<script data-skip-moving=true async src="https://www.google.com/recaptcha/api.js?hl=ru"></script>';
    }

    /**
     * @param string $recaptcha
     *
     * @return bool
     */
    public function check($recaptcha = '')
    {
        return static::baseCheck($recaptcha, $this->parameters['secretKey'], $this->parameters['serviceUri'],
            $this->guzzle);
    }

    /**
     * @param string          $recaptcha
     * @param string          $secretKey
     * @param string          $serviceUri
     * @param ClientInterface $client
     *
     * @return bool
     */
    public static function checkCaptcha($secretKey, $recaptcha = '', $serviceUri = '', ClientInterface $client = null)
    {
        if ($client === null) {
            $client = new Client();
        }
        if (empty($serviceUri)) {
            $serviceUri = static::$serviceUri;
        }
        return static::baseCheck($recaptcha, $secretKey, $serviceUri, $client);
    }

    /**
     * @param                 $recaptcha
     * @param                 $secretKey
     * @param                 $serviceUri
     * @param ClientInterface $client
     *
     * @return bool
     */
    protected static function baseCheck($recaptcha, $secretKey, $serviceUri, ClientInterface $client)
    {
        try {
            $context = Application::getInstance()->getContext();
        } catch (SystemException $e) {
            return false;
        }
        if (empty($recaptcha)) {
            $recaptcha = (string)$context->getRequest()->get('g-recaptcha-response');
        }
        $uri = new Uri($serviceUri);
        $uri->addParams(
            [
                'secret'   => $secretKey,
                'response' => $recaptcha,
                'remoteip' => $context->getServer()->get('REMOTE_ADDR'),
            ]
        );
        if (!empty($recaptcha)) {
            try {
                $res = $client->request('get', $uri->getUri());
            } catch (GuzzleException $e) {
                return false;
            }
            if ($res->getStatusCode() === 200) {
                $data = \json_decode($res->getBody()->getContents());
                if ($data && $data->success) {
                    return true;
                }
            }
        }

        return false;
    }
}
