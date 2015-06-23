<?php namespace FintechFab\Smstraffic;

use FintechFab\Smstraffic\Exceptions\SMSTrafficRequestFailedException;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Log;
use Psr\Http\Message\ResponseInterface;

class SmsTraffic
{
    private $url = 'https://www.smstraffic.ru/multi.php';

    private $guzzle;
    private $sendFrom;
    private $login;
    private $password;
    private $latin;
    private $pretend;

    public function __construct(Client $guzzle, $sendFrom, $login, $password, $latin = false, $pretend = false)
    {
        $this->guzzle = $guzzle;
        $this->sendFrom = $sendFrom;
        $this->login = $login;
        $this->password = $password;
        $this->latin = $latin;
        $this->pretend = $pretend;
    }

    /**
     * Send sms to one or many recipients
     *
     * @param string|array $receivers
     * @param string $message
     * @param string $sendDate
     * @param string|null $sendFrom
     * @param int|null $timeout
     *
     * @throws SMSTrafficRequestFailedException
     * @return int|bool
     */
    public function send($receivers, $message, $sendDate = null, $sendFrom = null, $timeout = null)
    {
        if (is_array($receivers)) {
            $receivers = implode(',', $receivers);
        } elseif (!is_string($receivers)) {
            throw new InvalidArgumentException('receivers param should be type of string or array');
        }

        $message = mb_convert_encoding($message, 'cp1251');

        $params = $this->params([
            'phones' => $receivers,
            'message' => $message,
            'start_date' => $sendDate,
            'timeout' => $timeout,
            'originator' => $this->sendFrom($sendFrom),
            'rus' => $this->latin ? 0 : 1,
            'want_sms_ids' => 1,
        ]);

        if ($this->pretend) {
            Log::debug('sms', ['message' => $message, 'receivers' => $receivers]);

            return true;
        }

        $response = $this->guzzle->post($this->url, ['body' => $params]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new SMSTrafficRequestFailedException('failed to send request to SMSTraffic: status code (' . $statusCode . ')');
        }

        $response = $this->parseResponse($response);

        if ($response->result == "ERROR") {
            throw new SMSTrafficRequestFailedException($response->description, (int)$response->code);
        }

        $id = (int)$response->message_infos->message_info->sms_id;

        return $id;
    }

    /**
     * Get status of a message
     *
     * @param int $id
     *
     * @throws SMSTrafficRequestFailedException
     * @return int
     */
    public function status($id)
    {
        $params = $this->params([
            'operation' => 'status',
            'sms_id' => $id,
        ]);

        $response = $this->guzzle->post($this->url, ['body' => $params]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new SMSTrafficRequestFailedException('failed to send request to SMSTraffic: status code (' . $statusCode . ')');
        }

        $response = $this->parseResponse($response);

        return SmsStatus::convert($response->status);
    }

    /**
     * Get account balance
     *
     * @throws SMSTrafficRequestFailedException
     * @return int|string
     */
    public function balance()
    {
        $params = $this->params([
            'operation' => 'account',
        ]);

        $response = $this->guzzle->post($this->url, ['body' => $params]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new SMSTrafficRequestFailedException('failed to send request to SMSTraffic: status code (' . $statusCode . ')');
        }

        $response = $this->parseResponse($response);
        $balance = current($response->account);

        if (is_string($balance)) {
            return (string)$balance;
        }

        return (int)$balance;
    }

    private function sendFrom($sendFrom = null)
    {
        return !is_null($sendFrom) ? $sendFrom : $this->sendFrom;
    }

    private function params(array $params = [])
    {
        $defaults = [
            'login' => $this->login,
            'password' => $this->password,
        ];

        return array_merge($params, $defaults);
    }

    /**
     * @param ResponseInterface $response
     * @return \SimpleXMLElement|mixed
     */
    private function parseResponse(ResponseInterface $response)
    {
        return simplexml_load_string($response->getBody()->getContents());
    }
}
