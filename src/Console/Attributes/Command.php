<?php

namespace WeStacks\Framework\Console\Attributes;

use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WeStacks\Framework\Contracts\Foundation\Application;
use WeStacks\Framework\Contracts\Foundation\Discovery\Bootable;

#[\Attribute]
class Command implements Bootable
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
    ) {}

    /** @param static|null $context */
    public static function boot(Application $app, \Reflector $reflection, $context = null): void
    {
        if (! $context) {
            return;
        }

        if (! $reflection instanceof \ReflectionMethod) {
            throw new \Exception('You can declare only class methods as console commands');
        }

        /** @var \Symfony\Component\Console\Application */
        $kernel = $app->get(\WeStacks\Framework\Contracts\Console\Kernel::class);

        // TODO: optional name, generate from class name + method
        $command = new ConsoleCommand($context->name);

        if ($context->description) {
            $command->setDescription($context->description);
        }

        foreach ($reflection->getParameters() as $parameter) {
            self::resolveParameter($parameter, $command, $context);
        }

        $command->setCode(static::makeCallable($reflection, $app));

        $kernel->addCommands([$command]);
    }

    protected static function resolveParameter(\ReflectionParameter $parameter, ConsoleCommand $command, Command $context)
    {
        $mode = array_sum(array_keys(array_filter([
            InputArgument::IS_ARRAY => $parameter->isVariadic(),
            InputArgument::OPTIONAL => $parameter->isOptional() || $parameter->isDefaultValueAvailable(),
            InputArgument::REQUIRED => ! $parameter->isOptional() && ! $parameter->isDefaultValueAvailable(),
        ], fn (bool $mode) => $mode === true)));

        foreach ($parameter->getAttributes(Argument::class) as $attribute) {
            $instance = $attribute->newInstance();

            return $command->addArgument(
                $parameter->getName(),
                $mode,
                $instance->description,
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            );
        }

        foreach ($parameter->getAttributes(Option::class) as $attribute) {
            $instance = $attribute->newInstance();

            return $command->addOption(
                $parameter->getName(),
                $instance->shortcuts,
                $mode,
                $instance->description,
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            );
        }

        return $command->addArgument(
            $parameter->getName(),
            $mode,
            '',
            $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
        );
    }

    protected static function makeCallable(\ReflectionMethod $method, Application $app): \Closure
    {
        $callback = $method->getClosure($app->get($method->class));
        $parameters = array_map(static fn ($param) => $param->getName(), $method->getParameters());

        return static function (InputInterface $input, OutputInterface $output) use ($callback, $parameters) {
            $parameters = array_intersect_key([...$input->getArguments(), ...$input->getOptions()], array_flip($parameters));

            return $callback(...$parameters);
        };
    }
}
