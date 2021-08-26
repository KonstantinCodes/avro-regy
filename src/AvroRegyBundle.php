<?php

declare(strict_types=1);

namespace Koco\AvroRegy;

use Koco\AvroRegy\DependencyInjection\Compiler\AddSerializerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AvroRegyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddSerializerCompilerPass());
    }
}
