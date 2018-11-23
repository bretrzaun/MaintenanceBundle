<?php
namespace BretRZaun\MaintenanceBundle\Tests;
// tests/AppKernel.php (you can define it in a subdirectory /Fixtures if you prefer)

use BretRZaun\MaintenanceBundle\MaintenanceBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array();
        if ('test' === $this->getEnvironment()) {
            $bundles[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
            $bundles[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
            $bundles[] = new MaintenanceBundle();
        }
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yml');
    }

    public function getRootDir()
    {
        return dirname(parent::getRootDir()) . '/';
    }
}
