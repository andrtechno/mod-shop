<?php

namespace panix\mod\shop\api\v1;

use yii\rest\Serializer as BaseSerializer;

class Serializer extends BaseSerializer
{
    public $collectionEnvelope = 'items';

    protected function serializeDataProvider($dataProvider)
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        }
        $result['success'] = true;
        $result[$this->collectionEnvelope] = $models;

        if ($pagination !== false) {
            return array_merge($result, $this->serializePagination($pagination));
        }

        return $result;
    }


    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result['success'] = false;
        foreach ($model->getFirstErrors() as $name => $message) {
            $result['errors'][] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return $result;
    }
}
