<?php

namespace Asciisd\Zoho\Concerns;

use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\APIException;
use com\zoho\crm\api\record\RecordOperations;

trait ManagesBulkActions
{
    public function bulkCreate(array $records = []): ActionWrapper|array
    {
        $recordOperations = new RecordOperations();
        $bodyWrapper = new BodyWrapper();

        $bodyWrapper->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $bodyWrapper->setTrigger($trigger);

        return $this->handleBulkActionResponse(
            $recordOperations->createRecords($this->module_api_name, $bodyWrapper)
        );
    }

    private function handleBulkActionResponse($response): ActionWrapper|array
    {
        if ($response != null) {
            if ($response->isExpected()) {
                $responseHandler = $response->getObject();

                if ($responseHandler instanceof ActionWrapper) {
                    return $responseHandler->getData();
                } elseif ($responseHandler instanceof APIException) {
                    logger()->error($responseHandler->getMessage()->getValue());
                }
            }
        }

        return [];
    }
}
