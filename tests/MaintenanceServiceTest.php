<?php
namespace BretRZaun\MaintenanceBundle\Tests;

use BretRZaun\MaintenanceBundle\MaintenanceService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

class MaintenanceServiceTest extends KernelTestCase
{

    /**
     * @param array|null $parameters
     * @param bool $maintenance
     * @param array $context
     * @dataProvider parameterProvider
     */
    public function testService(array $parameters, bool $maintenance, array $context = []): void
    {
        $container = new Container();
        $container->set('kernel', self::$kernel);
        $container->setParameter('maintenance', $parameters);

        $service = new MaintenanceService($container->getParameterBag());
        if (isset($context['currentDate'])) {
            $service->setCurrentDate($context['currentDate']);
        }

        $this->assertEquals($maintenance, $service->isMaintenance());

    }

    public function parameterProvider(): array
    {
        return [
            [
                [],
                false
            ],
            [
                ['enabled' => true, 'template' => 'foo'],
                true,
            ],
            [
                ['enabled' => false],
                false
            ],
            # maintenance starting tomorrow
            [
                ['enabled' => false, 'from' => '18.10.2018 00:00:00'],
                false,
                ['currentDate' => new \DateTime('17.10.2018')]
            ],
            # maintenance since yesterday
            [
                ['enabled' => false, 'from' => '16.10.2018 00:00:00'],
                true,
                ['currentDate' => new \DateTime('17.10.2018')]
            ],
            # maintenance until tomorrow
            [
                ['enabled' => false, 'until' => '18.10.2018 00:00:00'],
                true,
                ['currentDate' => new \DateTime('17.10.2018')]
            ],
            # maintenance until yesterday
            [
                ['enabled' => false, 'until' => '16.10.2018 00:00:00'],
                false,
                ['currentDate' => new \DateTime('17.10.2018')]
            ]
        ];
    }
}
