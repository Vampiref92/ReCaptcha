<?php namespace Vf92\Recaptcha;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Vf92\Recaptcha\Exception\NotFountSecretKey;

class ReCaptcha implements ReCaptchaInterface
{
    /**
     * @var ClientInterface
     */
    protected $guzzle;

    private $parameters;

    /** @noinspection SpellCheckingInspection */

    /**
     * ReCaptchaService constructor.
     *
     * @param ClientInterface $client
     *
     * @param array           $parameters
     *
     * @throws \RuntimeException
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
            $parameters['serviceUrl'] = 'https://www.google.com/recaptcha/api/siteverify';
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
            $this->addJs();
        } else {
            $script = $this->getJs();
        }

        return $script . '<div class="g-recaptcha' . $additionalClass . '" data-sitekey="' . $this->parameters['key']
            . '"></div>';
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return ['sitekey' => $this->parameters['key']];
    }

    public function addJs()
    {
        Asset::getInstance()->addJs('https://www.google.com/recaptcha/api.js?hl=ru');
    }

    public function addJsAsync()
    {
        Asset::getInstance()->addString($this->getJs(), true, AssetLocation::AFTER_JS_KERNEL);
    }

    /**
     * @return string
     */
    public function getJs()
    {
        return '<script data-skip-moving=true async src="https://www.google.com/recaptcha/api.js?hl=ru"></script>';
    }

    /**
     * @param string $recaptcha
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @return bool
     */
    public function checkCaptcha($recaptcha = '')
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $context = Application::getInstance()->getContext();
        if (empty($recaptcha)) {
            $recaptcha = (string)$context->getRequest()->get('g-recaptcha-response');
        }
        $uri = new Uri($this->parameters['serviceUri']);
        $uri->addParams(
            [
                'secret'   => $this->parameters['secretKey'],
                'response' => $recaptcha,
                'remoteip' => $context->getServer()->get('REMOTE_ADDR'),
            ]
        );
        if (!empty($recaptcha)) {
            try {
                $res = $this->guzzle->request('get', $uri->getUri());
            } catch (GuzzleException $e) {
                return false;
            }
            if ($res->getStatusCode() === 200) {
                $data = json_decode($res->getBody()->getContents());
                if ($data && $data->success) {
                    return true;
                }
            }
        }

        return false;
    }
}
