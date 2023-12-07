<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Endpoints;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Client\BaseApiClient;

class Dealers implements ApiInterface
{
    protected BaseApiClient $baseApiClient;

    public function __construct(BaseApiClient $baseApiClient)
    {
        $this->baseApiClient = $baseApiClient;
    }

    public function getDealersList(): array
    {
        dump("getDealerList method called");
        $client = $this->baseApiClient->createApiClient([]);
        dump($client);

        $errorMsg = null;
        try {
            $response = $client->get($this->constructEndpoint(), []);
            dump($response);
            dump($response->getStatusCode());
            dump($response->getBodyAsString());
        } catch (RestException $e) {
            dump($e);
            dump(get_class_methods($e));
            dump($e->getResponse());
            dump(get_class_methods($e->getResponse()));
            dump($e->getResponse()->getStatusCode());
            $responseBody = json_decode($e->getResponse()->getBodyAsString());
            dump($responseBody);

            dump($e->getMessage());

            // Crafting the error message depending on the response body
            if (isset($responseBody->title) && isset($responseBody->status)) {
                $errorMsg = 'Error: ' . $responseBody->status . ' status - ' . $responseBody->title;
                if (isset($responseBody->errors->Username[0])) {
                    $errorMsg .= ' ' . $responseBody->errors->Username[0];
                } elseif (isset($responseBody->errors->Password[0])) {
                    $errorMsg .= ' ' . $responseBody->errors->Password[0];
                } else {
                    $errorMsg .= ' Verify credentials are correct or if Acumatica API server is down.';
                }
            }

            if (isset($responseBody->message)) {
                $errorMsg = 'Error: - ' . $responseBody->message;
            }

            if (isset($responseBody->exceptionMessage)) {
                $errorMsg = $responseBody->exceptionMessage;
            }

            throw new RestException("Error with contacting Dealer endpoint: " . $errorMsg);
        }

        if ($response->getStatusCode() == 200) {
            return ['isSuccessful' => true, 'responseBody' => $response->getBodyAsString()];
        } else {
            return ['isSuccessful' => false, 'errorMessage' => $errorMsg];
        }
    }

    public function constructEndpoint(): string
    {
        // /store-front/api/{storeHash}/dealers?location={location}&radius={radius}
        return sprintf(
            '/store-front/api/%s/dealers?location=%s&radius=%s',
            $this->baseApiClient->getStoreHash(), '75044', '30'
        );
    }
}
