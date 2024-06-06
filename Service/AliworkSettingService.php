<?php
/**
 * Created by: zhlhuang (364626853@qq.com)
 * Time: 2023/3/26 21:05
 * Blog: https://www.yuque.com/huangzhenlian
 */

declare(strict_types=1);

namespace App\Application\Aliwork\Service;

use App\Service\AbstractSettingService;

class AliworkSettingService extends AbstractSettingService
{
    public function getAliworkSetting(string $key = '', $default = '')
    {
        return $this->getSettings('aliwork', $key, $default);
    }

    public function setAliworkSetting($setting): bool
    {
        return $this->saveSetting($setting, 'aliwork');
    }
}