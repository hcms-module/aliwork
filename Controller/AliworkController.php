<?php

declare(strict_types=1);

namespace App\Application\Aliwork\Controller;

use App\Annotation\Api;
use App\Annotation\View;
use App\Application\Admin\Middleware\AdminMiddleware;
use App\Application\Aliwork\Service\AliworkService;
use App\Application\Aliwork\Service\AliworkSettingService;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Middleware(AdminMiddleware::class)]
#[Controller(prefix: "/aliwork/aliwork")]
class AliworkController extends AbstractController
{

    #[Api]
    #[PostMapping("setting")]
    public function settingSubmit()
    {
        $setting = $this->request->input('setting', []);

        return (new AliworkSettingService())->setAliworkSetting($setting) ? [] : $this->returnErrorJson();
    }

    #[Api]
    #[GetMapping("setting/info")]
    public function settingInfo()
    {
        $setting = (new AliworkSettingService())->getAliworkSetting();

        return compact('setting');
    }

    #[View]
    #[GetMapping("setting")]
    public function setting()
    {

    }

    #[Api]
    #[GetMapping("index")]
    public function index()
    {
        return (new AliworkService())->getFormListInApp();
    }
}
