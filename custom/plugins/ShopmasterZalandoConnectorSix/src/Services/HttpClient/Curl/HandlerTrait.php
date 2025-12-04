<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace ShopmasterZalandoConnectorSix\Services\HttpClient\Curl;


use ShopmasterZalandoConnectorSix\Exception\HttpClient\ClientException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\AuthResponseException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\ErrorException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\RedirectException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\TooManyRequestsException;
use ShopmasterZalandoConnectorSix\Exception\HttpClient\ResponseExceptions\UnauthorizedResponseException;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;
use Symfony\Component\HttpFoundation\Response;

trait HandlerTrait
{
    /**
     * @param RequestStruct $request
     * @return array
     */
    private function getCurlHeadersByRequest(RequestStruct $request): array
    {
        $headers = [];
        $headers[] = "Accept: application/json";
        $headers[] = "Content-Type:" . $request->getContentType();
        if ($request->isUseZalandoToken()) {
            $headers[] = "Authorization: Bearer " . $request->getZOAuthToken();
        }
        return $headers;
    }


    /**
     * @param mixed $ch
     * @return ResponseStruct
     * @throws ClientException
     * @throws ResponseException
     * @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection
     */
    private function getResponseByCurl($ch): ResponseStruct
    {
        try {
            if (!is_resource($ch) && !($ch instanceof \CurlHandle)) {
                curl_close($ch);
                throw new ClientException('Curl is not resource');
            }
            
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 10000);
            $headers = [];
            curl_setopt($ch, CURLOPT_HEADERFUNCTION,
                function ($curl, $header) use (&$headers) {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) // ignore invalid headers
                        return $len;
                    $headers[strtolower(trim($header[0]))][] = trim($header[1]);
                    return $len;
                }
            );
            
            usleep(50000);
            $body = curl_exec($ch);
            
            // Check for cURL errors after execution
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new ClientException('cURL Error: ' . $error);
            }
            
            if ($body === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new ClientException('cURL execution failed: ' . $error);
            }
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $urlParse = $this->getUrlParse($url);
            $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            if ($urlParse['path'] === '/auth/token' && $httpCode !== Response::HTTP_OK) {
                throw new AuthResponseException($body, $httpCode);
            }

            switch ($httpCode) {
                case Response::HTTP_PERMANENTLY_REDIRECT :
                case Response::HTTP_TEMPORARY_REDIRECT :
                case Response::HTTP_MOVED_PERMANENTLY :
                case Response::HTTP_FOUND :
                    $this->logger->warning('redirect', ['url' => $url, 'body' => $body, 'code' => $httpCode, 'headers' => $headers]);
                    curl_close($ch);
                    throw new RedirectException($body, $httpCode);

                case Response::HTTP_TOO_MANY_REQUESTS:
                    $this->logger->warning('HTTP_TOO_MANY_REQUESTS', ['url' => $url, 'body' => $body, 'code' => $httpCode, 'headers' => $headers]);
                    curl_close($ch);
                    throw new TooManyRequestsException($headers['retry-after'][0], $body, $httpCode);

                case Response::HTTP_NOT_FOUND:
                case  Response::HTTP_FORBIDDEN:
                    throw new ErrorException($body, $httpCode);

                case Response::HTTP_UNAUTHORIZED:
                    throw new UnauthorizedResponseException($body, $httpCode);
//                default:
//                    echo 'can not find HTTP: ', $http_code, "\n";
            }
            $response = new ResponseStruct();
            $response->setStatus($httpCode);
            $response->setContent($body);
            curl_close($ch);
            usleep(50000);
            $this->logger->info('success', ['body' => $body, 'code' => $httpCode, 'headers' => $headers]);
            return $response;
        } catch (\Exception $exception) {
            // Ensure cURL handle is closed on any exception
            if (is_resource($ch) || ($ch instanceof \CurlHandle)) {
                curl_close($ch);
            }
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            throw $exception;
        }

    }

    public function getUrlParse(string $url): array
    {
        return parse_url($url);
    }
}