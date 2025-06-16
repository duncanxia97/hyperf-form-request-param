<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Abstracts;

use Fatbit\FormRequestParam\Traits\FormRequestParam;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Engine\Contract\Http\V2\RequestInterface;
use Hyperf\Validation\Validator;
use function Hyperf\Support\make;

abstract class AbstractFormRequestParam extends AbstractParam implements FormRequestParamInterface
{
    use FormRequestParam;

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
                $factory = make(Validator::class);
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