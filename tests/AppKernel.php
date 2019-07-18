<?php
namespace BretRZaun\MaintenanceBundle\Tests;
// tests/AppKernel.php (you can define it in a subdirectory /Fixtures if you prefer)

use BretRZaun\MaintenanceBundle\MaintenanceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [];
        if ('test' === $this->getEnvironment()) {
            $bundles[] = new FrameworkBundle();
            $bundles[] = new TwigBundle();
            $bundles[] = new MaintenanceBundle();
        }
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yml');
    }
}
