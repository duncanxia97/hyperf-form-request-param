<?php
/**
 * @author XJ.
 * @Date   2023/12/5 0005
 */

namespace Fatbit\FormRequestParam;

use Fatbit\FormRequestParam\Middleware\FormRequestParamValidationMiddleware;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'middlewares' => [
                'http' => [
                    FormRequestParamValidationMiddleware::class,
                ],
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ]
            ],
        ];
    }
}