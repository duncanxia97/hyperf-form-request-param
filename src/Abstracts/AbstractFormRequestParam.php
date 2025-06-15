<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Abstracts;

use Fatbit\FormRequestParam\Traits\FormRequestParam;
use Fatbit\FormRequestParam\Traits\ParamModelMapping;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface as ValidationFactory;

abstract class AbstractFormRequestParam extends AbstractParam implements FormRequestParamInterface, ParamModelMappingInterface
{
    use FormRequestParam, ParamModelMapping;

    /**
     * 获取已验证的参数
     *
     * @author XJ.
     * @Date   2025/6/15
     * @return static
     */
    public static function validatedAndGet(): static
    {
        if (ApplicationContext::getContainer()
                              ->has(static::class)) {
            return ApplicationContext::getContainer()
                                     ->get(static::class);
        }

        return Context::getOrSet(
            'validatedGet:' . static::class,
            function () {
                $factory = make(ValidationFactory::class);
                $request = make(RequestInterface::class);

                $data          = array_merge_recursive($request->all(), $request->getUploadedFiles());
                $validatedData = $factory
                    ->make(
                        $data,
                        static::getRules(),
                        static::getMessages(),
                        static::getAttributes(),
                    )
                    ->validated();
                $data          = [];
                foreach (static::getFieldMapping() as $key => $toKey) {
                    if (isset($validatedData[$key])) {
                        $data[$toKey] = $validatedData[$key];
                    }
                }

                return new static($data);
            },
        );
    }
}