<?php

namespace App\Application\Aliwork\Service\AliworkForm;

use App\Application\Aliwork\Service\AliworkService;
use App\Application\Aliwork\Service\AliworkSettingService;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;

class AbstractAliworkForm
{
    protected string $form_id = '';

    protected string $user_id = '';

    protected array $fields = [];

    protected AliworkService $aliwork_service;

    #[Inject]
    protected AliworkSettingService $setting;

    /**
     * @param string $user_id
     */
    public function __construct(string $user_id = '')
    {
        if ($user_id !== '') {
            //指定用户userId
            $this->user_id = $user_id;
        } else {
            //默认配置中的userId
            $this->user_id = $this->setting->getAliworkSetting('user_id');
        }
        $this->aliwork_service = (new AliworkService($this->user_id));
    }

    /**
     * 格式化字段别名，方便调用方读取
     *
     * @param array $form_data
     * @return array
     */
    public function formatField(array $form_data = []): array
    {
        $res = [];
        foreach ($form_data as $key => $value) {
            $field_key = $this->fields[$key] ?? $key;
            $res[$field_key] = $this->formatValue($value, $field_key);
        }

        return $res;
    }

    /**
     * 具体格式方式，可以继承可以重写
     *
     * @param mixed $value
     * @param       $key
     * @return mixed|string
     */
    protected function formatValue(mixed $value, $key = '')
    {
        if ($key) {
            //TODO 对指定 key 格式化
        }

        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function formatInstanceValue($value, $key = 'title')
    {
        try {
            $res = Json::decode(Json::decode($value));

            if (is_array($res)) {
                if ($key) {
                    return $res[0][$key] ?? '';
                }

                return [
                    'title' => $res[0]['title'] ?? '',
                    'instance_id' => $res[0]['instanceId'] ?? '',
                ];
            }

            return [];
        } catch (\Throwable $throwable) {
            return [];
        }
    }

    public function getAliworkService(): AliworkService
    {
        return $this->aliwork_service;
    }


    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->form_id;
    }

    /**
     * @param string $form_id
     */
    public function setFormId(string $form_id): void
    {
        $this->form_id = $form_id;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @param string $user_id
     * @return $this
     */
    public function setUserId(string $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }
}