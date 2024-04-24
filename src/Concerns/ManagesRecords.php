<?php

namespace Asciisd\Zoho\Concerns;

use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\APIException;
use com\zoho\crm\api\record\GetRecordsParam;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\record\ResponseWrapper as RecordResponseWrapper;

trait ManagesRecords
{
    public function getRecord(string $record_id): Record
    {
        if(empty($record_id)){
            logger()->error( "getRecord: Record ID must not be empty or null.\n");
        }
        $recordData = (new RecordOperations())->getRecord($record_id, $this->module_api_name);

        // Check if record data is empty or if an error occurred
        if (empty($recordData) || !is_array($recordData) || !isset($recordData[0])) {
            logger()->error( "getRecord: Failed to fetch record from Zoho.\n");
        }

        return $this->handleRecordResponse($recordData)[0];
    }

    /**
     * get the records array of given module api name
     */
    public function getRecords($page = 1, $perPage = 200): array
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();

        $paramInstance->add(GetRecordsParam::page(), $page);
        $paramInstance->add(GetRecordsParam::perPage(), $perPage);

        return $this->handleRecordResponse(
            $recordOperations->getRecords($this->module_api_name, $paramInstance)
        );
    }

    public function searchRecordsByCriteria(string $criteria, $page = 1, $perPage = 200): array
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();

        $paramInstance->add(SearchRecordsParam::criteria(), $criteria);
        $paramInstance->add(GetRecordsParam::page(), $page);
        $paramInstance->add(GetRecordsParam::perPage(), $perPage);

        return $this->handleRecordResponse(
            $recordOperations->searchRecords($this->module_api_name, $paramInstance)
        );
    }

    public function searchRecordsByWord(string $word, $page = 1, $perPage = 200): array
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();

        $paramInstance->add(SearchRecordsParam::word(), $word);
        $paramInstance->add(GetRecordsParam::page(), $page);
        $paramInstance->add(GetRecordsParam::perPage(), $perPage);

        return $this->handleRecordResponse(
            $recordOperations->searchRecords($this->module_api_name, $paramInstance)
        );
    }

    public function searchRecordsByPhone(string $phone, $page = 1, $perPage = 200): array
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();

        $paramInstance->add(SearchRecordsParam::phone(), $phone);
        $paramInstance->add(GetRecordsParam::page(), $page);
        $paramInstance->add(GetRecordsParam::perPage(), $perPage);

        return $this->handleRecordResponse(
            $recordOperations->searchRecords($this->module_api_name, $paramInstance)
        );
    }

    public function searchRecordsByEmail(string $email, $page = 1, $perPage = 200): array
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();

        $paramInstance->add(SearchRecordsParam::email(), $email);
        $paramInstance->add(GetRecordsParam::page(), $page);
        $paramInstance->add(GetRecordsParam::perPage(), $perPage);

        return $this->handleRecordResponse(
            $recordOperations->searchRecords($this->module_api_name, $paramInstance)
        );
    }

    private function handleRecordResponse($response): array
    {
        if ($response != null) {
            if (in_array($response->getStatusCode(), array(204, 304))) {
                logger()->error($response->getStatusCode() == 204 ? "No Content" : "Not Modified");

                return [];
            }

            if ($response->isExpected()) {
                $responseHandler = $response->getObject();

                if ($responseHandler instanceof RecordResponseWrapper) {
                    return $responseHandler->getData();
                } elseif ($responseHandler instanceof APIException) {
                    logger()->error($responseHandler->getMessage()->getValue());
                }
            }
        }

        return [];
    }
}
