<?php

namespace Asciisd\Zoho\Concerns;

use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\modules\APIException;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\SuccessfulConvert;
use com\zoho\crm\api\record\ConvertBodyWrapper;
use com\zoho\crm\api\record\DeleteRecordsParam;
use com\zoho\crm\api\record\ConvertActionWrapper;

trait ManagesActions
{
    public function create(array $args = []): SuccessResponse|array
    {
        $recordOperations = new RecordOperations();
        $bodyWrapper = new BodyWrapper();
        $record = new Record();

        foreach ($args as $key => $value) {
            $record->addKeyValue($key, $value);
        }

        $bodyWrapper->setData([$record]);

        return $this->handleActionResponse(
            $recordOperations->createRecords($this->module_api_name, $bodyWrapper)
        );
    }

    public function update(Record $record): SuccessResponse|array
    {
        $recordOperations = new RecordOperations();
        $request = new BodyWrapper();
        $request->setData([$record]);

        return $this->handleActionResponse(
            $recordOperations->updateRecords($this->module_api_name, $request)
        );
    }

    public function deleteRecord(string $record_id): SuccessResponse|array
    {
        return $this->delete([$record_id]);
    }

    public function delete(array $recordIds): SuccessResponse|array
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();

        foreach ($recordIds as $id) {
            $paramInstance->add(DeleteRecordsParam::ids(), $id);
        }

        return $this->handleActionResponse(
            $recordOperations->deleteRecords($this->module_api_name, $paramInstance)
        );
    }

    public function convertLead(string $id, $data)
    {

        $recordOperations = new RecordOperations();
        $body = new ConvertBodyWrapper();
        $body->setData($data);

        return $this->handleConvertResponse(
            $recordOperations->convertLead($id, $body)
        );
    }

    private function handleActionResponse($response): SuccessResponse|array
    {
        if ($response != null) {
            if (in_array($response->getStatusCode(), array(204, 304))) {
                logger()->error($response->getStatusCode() == 204 ? "No Content" : "Not Modified");

                return [];
            }

            if ($response->isExpected()) {
                $responseHandler = $response->getObject();

                if ($responseHandler instanceof ActionWrapper) {
                    $actionResponse = $responseHandler->getData()[0];

                    if ($actionResponse instanceof SuccessResponse) {
                        return $actionResponse;

                    } else if(! empty($actionResponse)) {
                        // This is a failure case in which we are getting some errors from Zoho that we need to show on frontend.
                        if (property_exists($actionResponse, 'status')) {
                            $status = $actionResponse->getStatus()->getValue();
                        } else {
                            $status = 'unknown';
                        }

                        if (property_exists($actionResponse, 'message')) {
                            $message = $actionResponse->getMessage()->getValue();
                        } else {
                            $message = 'Unknown error';
                        }

                        $responseArray = [
                            'status' => $status,
                            'message' => $message,
                        ];
                        return $responseArray;
                    }
                }

                if ($responseHandler instanceof APIException) {
                    logger()->error($responseHandler->getMessage()->getValue());
                }
            }
        }

        return [];
    }

    private function handleConvertResponse($response): SuccessfulConvert|array
    {
        if ($response != null) {
            if (in_array($response->getStatusCode(), array(204, 304))) {
                logger()->error($response->getStatusCode() == 204 ? "No Content" : "Not Modified");

                return [];
            }

            if ($response->isExpected()) {
                $responseHandler = $response->getObject();

                if ($responseHandler instanceof ConvertActionWrapper) {
                    $actionResponse = $responseHandler->getData()[0];

                    if ($actionResponse instanceof SuccessfulConvert) {
                        return $actionResponse;
                    }
                }

                if ($responseHandler instanceof APIException) {
                    logger()->error($responseHandler->getMessage()->getValue());
                }
            }
        }

        return [];
    }
}
