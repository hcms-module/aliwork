<?php

namespace App\Application\Aliwork\Service\AliworkForm;

class AliworkProductForm extends AbstractAliworkForm
{
    protected string $form_id = 'FORM-J7966ZA1ZBOARW32FWDV786QEZMQ3XMNUBNHLN';

    public function getLists()
    {
        $data = [];
        for ($i = 1; $i <= 1; $i++) {
            $res = $this->aliwork_service->getDataByFormId($this->form_id, 100, $i);
            $data = array_merge($data, $res['data'] ?? []);
        }

        return $data;
    }

    public function getOpenUrl(string $fileUrl): array
    {
        return $this->aliwork_service->getOpenUrl($fileUrl);
    }
}