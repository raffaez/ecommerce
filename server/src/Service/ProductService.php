<?php

namespace Service;

use InvalidArgumentException;
use Repository\ProductRepository;
use Util\ConstantsUtil;

class ProductService
{
    public const TABLE = 'tb_products';
    public const GET_RESOURCES = ['get'];
    public const DELETE_RESOURCES = ['delete'];
    public const POST_RESOURCES = ['create'];
    public const PUT_RESOURCES = ['update'];
    private array $data;
    private array $dataRequestBody;
    private object $ProductRepository;

    /**
     * @param $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
        $this->ProductRepository = new ProductRepository()
;    }

    /**
     * @return mixed
     */
    public function validateGet()
    {
        $return = null;
        $resource = $this->data['resource'];

        if(in_array($resource, self::GET_RESOURCES, true)) {
            $return = $this->data['sku'] !== null ? $this->getOneByKey() : $this->$resource();
        } else {
            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_RESOURCE_NOTFOUND);
        }

        $this->validateRequestReturn($return);

        return $return;
    }

    /**
     * @return mixed
     */
    public function validateDelete()
    {
        $return = null;
        $resource = $this->data['resource'];

        if(in_array($resource, self::DELETE_RESOURCES, true)) {
            $return = $this->validateSku($resource);
        } else {
            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_RESOURCE_NOTFOUND);
        }

        $this->validateRequestReturn($return);

        return $return;
    }

    /**
     * @return mixed
     */
    public function validatePost()
    {
        $return = null;
        $resource = $this->data['resource'];

        if(in_array($resource, self::POST_RESOURCES, true)) {
            $return = $this->$resource();
        } else {
            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_RESOURCE_NOTFOUND);
        }

        $this->validateRequestReturn($return);

        return $return;
    }

    /**
     * @return mixed
     */
    public function validatePut()
    {
        $return = null;
        $resource = $this->data['resource'];

        if(in_array($resource, self::PUT_RESOURCES, true)) {
            $return = $this->validateSku($resource);
        } else {
            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_RESOURCE_NOTFOUND);
        }

        $this->validateRequestReturn($return);

        return $return;
    }

    /**
     * @param $requestData
     * @return void
     */
    public function setDataRequestBody($requestData)
    {
        $this->dataRequestBody = $requestData;
    }

    /**
     * @return mixed
     */
    private function getOneByKey()
    {
        return $this->ProductRepository->getMySQL()->getOneByKey(self::TABLE, $this->data['sku']);
    }

    /**
     * @param $return
     * @return void
     */
    public function validateRequestReturn($return): void
    {
        if ($return === null) {
            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_GENERIC);
        }
    }

    /**
     * @param string $resource
     * @return mixed
     */
    public function validateSku(string $resource)
    {
        if ($this->data['sku'] !== null) {
            $return = $this->$resource();
        } else {
            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_SKU_NECESSARY);
        }
        return $return;
    }

    /**
     * @return array
     */
    private function get()
    {
        return $this->ProductRepository->getMySQL()->getAll(self::TABLE);
    }

    /**
     * @return string
     */
    private function delete()
    {
        return $this->ProductRepository->getMySQL()->delete(self::TABLE, $this->data['sku']);
    }

    /**
     * @return array
     */
    private function create()
    {
        [$sku, $name, $price, $type] = [
                                        $this->dataRequestBody['sku'],
                                        $this->dataRequestBody['name'],
                                        $this->dataRequestBody['price'],
                                        $this->dataRequestBody['type']
                                        ];

        if($sku && $name && $price && $type){
            if($this->ProductRepository->insertProduct($sku, $name, $price, $type)){
                $insertedId = $this->ProductRepository->getMySQL()->getDb()->lastInsertId();
                $this->ProductRepository->getMySQL()->getDb()->commit();
                return ['insertedId' => $insertedId];
            }

            $this->ProductRepository->getMySQL()->getDb()->rollBack();

            throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_GENERIC);
        }

        throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_INSUFFICIENT_DATA);
    }

    /**
     * @return string
     */
    private function update()
    {
        if($this->ProductRepository->updateProduct($this->data['sku'], $this->dataRequestBody) > 0){
            $this->ProductRepository->getMySQL()->getDb()->commit();
            return ConstantsUtil::MSG_SUCCESS_UPDATE;
        }
        $this->ProductRepository->getMySQL()->getDb()->rollback();

        throw new InvalidArgumentException(ConstantsUtil::MSG_ERROR_NOT_ALTERED);
    }
}