<?php
/**
 * Created by: zhlhuang (364626853@qq.com)
 * Time: 2023/3/26 21:55
 * Blog: https://www.yuque.com/huangzhenlian
 */

declare(strict_types=1);

namespace App\Application\Aliwork\Service;

use AlibabaCloud\SDK\Dingtalk\Vedu_1_0\Models\GetOpenCoursesRequest;
use AlibabaCloud\SDK\Dingtalk\Vhrm_1_0\Models\MasterDataSaveRequest\body;
use AlibabaCloud\SDK\Dingtalk\Vswform_1_0\Models\GetFormInstanceHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Dingtalk;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\BatchGetFormDataByIdListHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\BatchGetFormDataByIdListRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\CreateOrUpdateFormDataHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\CreateOrUpdateFormDataRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetFormDataByIDHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetFormDataByIDRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetFormDataByIDResponseBody;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetFormListInAppHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetFormListInAppRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetOpenUrlHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\GetOpenUrlRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\SearchFormDataSecondGenerationNoTableFieldHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\SearchFormDataSecondGenerationNoTableFieldRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\SearchFormDatasHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\SearchFormDatasRequest;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\UpdateFormDataHeaders;
use AlibabaCloud\SDK\Dingtalk\Vyida_1_0\Models\UpdateFormDataRequest;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Exception\TeaUnableRetryError;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use App\Exception\ErrorException;
use Darabonba\OpenApi\Models\Config;
use GuzzleHttp\Client;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Codec\Json;
use Hyperf\Di\Annotation\Inject;

class AliworkService
{
    protected Dingtalk $client;

    #[Inject]
    protected AliworkSettingService $setting;

    protected string $user_id = '';


    public function getAppType(): string
    {
        return $this->setting->getAliworkSetting('app_type', '');
    }


    public function __construct(string $user_id)
    {
        $config = new Config();
        $config->protocol = "https";
        $config->regionId = "central";
        $this->client = new Dingtalk($config);
        $this->user_id = $user_id;
    }

    /**
     * 获取文件下载的url
     *
     * @param string $fileUrl
     * @return array
     * @throws ErrorException
     */
    public function getOpenUrl(string $fileUrl): array
    {
        $getOpenUrlHeaders = new GetOpenUrlHeaders([]);
        $getOpenUrlHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $getOpenUrlRequest = new GetOpenUrlRequest($this->getCommonParams() + compact('fileUrl'));
        $getOpenUrlResponse = $this->client->getOpenUrlWithOptions($this->getAppType(), $getOpenUrlRequest,
            $getOpenUrlHeaders, new RuntimeOptions([]));

        return $getOpenUrlResponse->body->toMap();
    }

    /**
     * 单个实例更新
     *
     * @param array  $formDataJson
     * @param string $formInstanceId
     * @param bool   $useLatestVersion 是否按照最新版本更新
     * @return array
     * @throws ErrorException
     */
    public function updateFormDataData(
        array $formDataJson,
        string $formInstanceId,
        bool $useLatestVersion = true
    ): array {
        $updateFormDataHeaders = new UpdateFormDataHeaders([]);
        $updateFormDataHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $updateFormDataRequest = new UpdateFormDataRequest($this->getCommonParams() + [
                'updateFormDataJson' => Json::encode($formDataJson),
                'formInstanceId' => $formInstanceId,
                'useLatestVersion' => $useLatestVersion
            ]);
        try {
            $response = $this->client->updateFormDataWithOptions($updateFormDataRequest, $updateFormDataHeaders,
                new RuntimeOptions([]));

            return $response->toMap();
        } catch (\Throwable $err) {
            if (!($err instanceof TeaError)) {
                $err = new TeaError([], $err->getMessage(), $err->getCode(), $err);
            }
            if (!Utils::empty_($err->code) && !Utils::empty_($err->message)) {
                // err 中含有 code 和 message 属性，可帮助开发定位问题
                throw new ErrorException($err->code . "：" . $err->message);
            }
            throw new ErrorException($err->getMessage());
        }
    }

    /**
     * 批量实例更新或新增
     *
     * @param array  $formDataJson
     * @param string $formUuid
     * @param        $searchCondition
     * @return array
     * @throws ErrorException
     */
    public function createOrUpdateFormData(array $formDataJson, string $formUuid, $searchCondition = []): array
    {
        if (empty($searchCondition)) {
            throw new ErrorException('添加条件不能为空');
        }
        $createOrUpdateFormDataHeaders = new CreateOrUpdateFormDataHeaders([]);
        $createOrUpdateFormDataHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $createOrUpdateFormDataRequest = new CreateOrUpdateFormDataRequest($this->getCommonParams() + [
                'formDataJson' => Json::encode($formDataJson),
                'formUuid' => $formUuid,
                'searchCondition' => Json::encode($searchCondition)
            ]);
        try {
            $response = $this->client->createOrUpdateFormDataWithOptions($createOrUpdateFormDataRequest,
                $createOrUpdateFormDataHeaders, new RuntimeOptions([]));

            return $response->body->toMap();
        } catch (\Throwable $err) {
            if (!($err instanceof TeaError)) {
                $err = new TeaError([], $err->getMessage(), $err->getCode(), $err);
            }
            if (!Utils::empty_($err->code) && !Utils::empty_($err->message)) {
                // err 中含有 code 和 message 属性，可帮助开发定位问题
                throw new ErrorException($err->code . "：" . $err->message);
            }
            throw new ErrorException($err->getMessage());
        }
    }


    /**
     * 获取指定多个实例数据
     *
     * @param string $formUuid
     * @param array  $formInstanceIdList
     * @return array
     * @throws ErrorException
     */
    public function getDataByInstanceIds(string $formUuid, array $formInstanceIdList = []): array
    {
        $batchGetFormDataByIdListHeaders = new BatchGetFormDataByIdListHeaders([]);
        $batchGetFormDataByIdListHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $batchGetFormDataByIdListRequest = new BatchGetFormDataByIdListRequest($this->getCommonParams() + [
                'formInstanceIdList' => $formInstanceIdList,
                'formUuid' => $formUuid,
            ]);

        try {
            $response = $this->client->batchGetFormDataByIdListWithOptions($batchGetFormDataByIdListRequest,
                $batchGetFormDataByIdListHeaders, new RuntimeOptions([]));

            return $response->body->toMap();
        } catch (\Exception $err) {
            if (!($err instanceof TeaError)) {
                $err = new TeaError([], $err->getMessage(), $err->getCode(), $err);
            }
            if (!Utils::empty_($err->code) && !Utils::empty_($err->message)) {
                // err 中含有 code 和 message 属性，可帮助开发定位问题
                throw new ErrorException($err->code . "：" . $err->message);
            }
            throw new ErrorException($err->getMessage());
        }
    }


    /**
     * 获取单个实例数据
     *
     * @param string $formInstId
     * @return GetFormDataByIDResponseBody
     * @throws ErrorException
     */
    public function getDataByInstanceId(string $formInstId): GetFormDataByIDResponseBody
    {
        $getFormDataByIDHeaders = new GetFormDataByIDHeaders([]);
        $getFormDataByIDHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $getFormDataByIDRequest = new GetFormDataByIDRequest($this->getCommonParams());

        try {
            $response = $this->client->getFormDataByIDWithOptions($formInstId, $getFormDataByIDRequest,
                $getFormDataByIDHeaders, new RuntimeOptions([]));
            if (!$response->body->formInstId) {
                throw new ErrorException("找不到该记录");
            }

            return $response->body;
        } catch (\Exception $err) {
            if (!($err instanceof TeaError)) {
                $err = new TeaError([], $err->getMessage(), $err->getCode(), $err);
            }
            if (!Utils::empty_($err->code) && !Utils::empty_($err->message)) {
                // err 中含有 code 和 message 属性，可帮助开发定位问题
                throw new ErrorException($err->code . "：" . $err->message);
            }
            throw new ErrorException($err->getMessage());
        }
    }

    /**
     * 获取分页数据列表
     *
     * @param string $formUuid
     * @param int    $pageSize
     * @param int    $page
     * @param array  $searchCondition
     * @param array  $orderConfigJson
     * @param string $modifiedToTimeGMT
     * @return array
     * @throws ErrorException
     */
    public function getDataByFormId(
        string $formUuid,
        int $pageSize = 20,
        int $page = 1,
        array $searchCondition = [],
        array $orderConfigJson = [],
        string $modifiedToTimeGMT = ''
    ): array {
        $searchFormDataSecondGenerationNoTableFieldHeaders = new  SearchFormDataSecondGenerationNoTableFieldHeaders([]);
        $searchFormDataSecondGenerationNoTableFieldHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $request_data = $this->getCommonParams() + [
                "formUuid" => $formUuid,
                'pageSize' => $pageSize,
                'pageNumber' => $page,
                'searchCondition' => Json::encode($searchCondition),
                'orderConfigJson' => Json::encode($orderConfigJson),
            ];
        if ($modifiedToTimeGMT) {
            $request_data['modifiedToTimeGMT'] = $modifiedToTimeGMT;
        }
        $searchFormDataSecondGenerationNoTableFieldRequest = new SearchFormDataSecondGenerationNoTableFieldRequest($request_data);
        try {
            $response = $this->client->searchFormDataSecondGenerationNoTableFieldWithOptions($searchFormDataSecondGenerationNoTableFieldRequest,
                $searchFormDataSecondGenerationNoTableFieldHeaders, new RuntimeOptions([]));

            return $response->body->toMap();
        } catch (\Exception $err) {
            if (!($err instanceof TeaError)) {
                $err = new TeaError([], $err->getMessage(), $err->getCode(), $err);
            }
            if (!Utils::empty_($err->code) && !Utils::empty_($err->message)) {
                // err 中含有 code 和 message 属性，可帮助开发定位问题
                throw new ErrorException($err->code . "：" . $err->message);
            }
            throw new ErrorException($err->getMessage());
        }
    }

    /**
     * 获取引用所有表单
     *
     * @return array
     * @throws ErrorException
     */
    public function getFormListInApp(): array
    {
        $getFormListInAppHeaders = new GetFormListInAppHeaders();
        $getFormListInAppHeaders->xAcsDingtalkAccessToken = $this->getToken();
        $getFormListInAppRequest = new GetFormListInAppRequest($this->getCommonParams());
        try {
            $response = $this->client->getFormListInAppWithOptions($getFormListInAppRequest, $getFormListInAppHeaders,
                new RuntimeOptions([]));
            $res = $response->body->toMap();
            $status = $res['success'] ?? false;

            if ($status) {
                return $res['result'] ?? [];
            }
            throw new ErrorException('请求失败');
        } catch (\Exception $err) {
            if (!($err instanceof TeaError)) {
                $err = new TeaError([], $err->getMessage(), $err->getCode(), $err);
            }
            if (!Utils::empty_($err->code) && !Utils::empty_($err->message)) {
                // err 中含有 code 和 message 属性，可帮助开发定位问题
                throw new ErrorException($err->code . "：" . $err->message);
            }
            throw new ErrorException($err->getMessage());
        }
    }

    /**
     * @return array
     */
    private function getCommonParams(): array
    {
        return [
            "systemToken" => $this->setting->getAliworkSetting('system_token'),
            "appType" => $this->setting->getAliworkSetting('app_type'),
            "userId" => $this->getUserId(),
        ];
    }

    #[Cacheable(prefix: "getToken", ttl: 7000)]
    private function getToken()
    {
        $client = new Client();
        $appkey = $this->setting->getAliworkSetting('appkey');
        $appsecret = $this->setting->getAliworkSetting('appsecret');
        $response = $client->get("https://oapi.dingtalk.com/gettoken?appkey={$appkey}&appsecret={$appsecret}");
        $result = Json::decode($response->getBody()
            ->getContents());
        $errcode = $result['errcode'] ?? -1;
        if ($errcode === 0) {
            return $result['access_token'] ?? '';
        }
        throw new ErrorException($result['errmsg'] ?? '获取Token失败');
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