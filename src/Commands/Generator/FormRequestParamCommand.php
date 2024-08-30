<?php
/**
 * @author XJ.
 * @Date   2023/8/2 0002
 */

namespace Fatbit\FormRequestParam\Commands\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Utils\Str;

#[Command]
class FormRequestParamCommand extends GeneratorCommand
{
    protected string $classSuffix = 'RequestParam';

    public function __construct()
    {
        parent::__construct('gen:'.Str::snake($this->classSuffix, '-'));
    }

    public function qualifyClass($name): string
    {
        $name = implode(
            '/',
            array_map(
                fn($v) => Str::studly(Str::snake($v)),
                explode('/', Str::replace('\\', '/', $name))
            )
        );
        $name = Str::studly(Str::snake($name));
        $name = parent::qualifyClass($name);

        return $name . $this->classSuffix;
    }

    public function configure()
    {
        $this->setDescription('Create a new request param class');

        parent::configure();
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/request_param.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return 'App\\RequestParams';
    }
}
