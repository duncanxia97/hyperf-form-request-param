<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Middleware;

use Closure;
use FastRoute\Dispatcher;
use Fatbit\FormRequestParam\Abstracts\FormRequestParamInterface;
use Hyperf\Context\Context;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Validation\Contract\ValidatorFactoryInterface as ValidationFactory;
use Hyperf\Validation\UnauthorizedException;
use Hyperf\Validation\ValidationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use function Hyperf\Support\make;

class FormRequestParamValidationMiddleware implements MiddlewareInterface
{

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (!$dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        Context::set(ServerRequestInterface::class, $request);

        if ($this->shouldHandle($dispatched)) {
            try {
                [$requestHandler, $method] = $this->prepareHandler($dispatched->handler->callback);
                if ($method) {
                    $reflectionMethod = ReflectionManager::reflectMethod($requestHandler, $method);
                    $parameters       = $reflectionMethod->getParameters();
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() === null) {
                            continue;
                        }
                        /** @var FormRequestParamInterface $className */
                        $className = $parameter->getType()->getName();
                        if (in_array(FormRequestParamInterface::class, class_implements($className))) {
                            /** @var ValidatorInterface $instance */
                            $instance = $this->getValidatorInstance($className);

                            if ($instance->fails()) {
                                $this->failedValidation($instance);
                            }
                            $data = $instance->validated();
                            $data = array_combine(
                                array_map(fn($v) => $className::getFieldMapping()[$v] ?? $v, array_keys($data)),
                                array_values($data)
                            );
                            $this->container->set($className, new $className($data));
                        }
                    }
                }
            } catch (UnauthorizedException $exception) {
                return $this->handleUnauthorizedException($exception);
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param UnauthorizedException $exception Keep this argument here even this argument is unused in the method,
     *                                         maybe the user need the details of exception when rewrite this method
     */
    protected function handleUnauthorizedException(UnauthorizedException $exception): ResponseInterface
    {
        return Context::override(ResponseInterface::class, fn(ResponseInterface $response) => $response->withStatus(403));
    }

    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && !$dispatched->handler->callback instanceof Closure;
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     */
    protected function prepareHandler(array|string $handler): array
    {
        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            if (!isset($array[1]) && class_exists($handler) && method_exists($handler, '__invoke')) {
                $array[1] = '__invoke';
            }

            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new RuntimeException('Handler not exist.');
    }

    protected function getContextValidatorKey(string $formRequestParam, string $key): string
    {
        return sprintf('%s:%s', $formRequestParam, $key);
    }


    /**
     * Get the validator instance for the request.
     *
     * @param string|FormRequestParamInterface $formRequestParam
     *
     * @return ValidatorInterface
     */
    protected function getValidatorInstance(string $formRequestParam): ValidatorInterface
    {
        return Context::getOrSet(
            $this->getContextValidatorKey($formRequestParam, ValidatorInterface::class),
            function () use ($formRequestParam) {
                $factory   = $this->container->get(ValidationFactory::class);
                $validator = $this->createDefaultValidator($factory, $formRequestParam);

                if (method_exists($this, 'withValidator')) {
                    $this->withValidator($validator);
                }

                return $validator;
            }
        );
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws \Hyperf\Validation\ValidationException
     */
    protected function failedValidation(ValidatorInterface $validator)
    {
        throw new ValidationException($validator);
    }

    /**
     * Create the default validator instance.
     *
     * @param ValidationFactory                $factory
     * @param string|FormRequestParamInterface $formRequestParam
     *
     * @return ValidatorInterface
     */
    protected function createDefaultValidator(ValidationFactory $factory, string $formRequestParam): ValidatorInterface
    {
        return $factory->make(
            $this->validationData(),
            $formRequestParam::getRules(),
            $formRequestParam::getMessages(),
            $formRequestParam::getAttributes()
        );
    }

    /**
     * Get data to be validated from the request.
     */
    protected function validationData(): array
    {
        $request = make(RequestInterface::class);

        return array_merge_recursive($request->all(), $request->getUploadedFiles());
    }
}