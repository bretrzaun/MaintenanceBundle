<?php
namespace BretRZaun\MaintenanceBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use BretRZaun\MaintenanceBundle\MaintenanceService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class MaintenanceServiceTest extends KernelTestCase
{

    #[DataProvider('parameterProvider')]
    public function testService(array $parameters, bool $maintenance, array $context = []): void
    {
        $container = new Container();
        $container->set('kernel', self::$kernel);
        $container->setParameter('maintenance', $parameters);

        $service = new MaintenanceService($container->getParameterBag());
        if (isset($context['currentDate'])) {
            $service->setCurrentDate($context['currentDate']);
        }


        if (isset($context['clientIp'])) {
            $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => $context['clientIp']]);
        } else {
            $request = new Request();
        }

        $result = $service->isMaintenance();
        if (isset($context['clientIp'])) {
            $result &= !$service->isInternal($request);
        }
        $this->assertEquals($maintenance, $result);
    }

    public static function parameterProvider(): array
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
            'maintenance starting tomorrow' =>
            [
                ['enabled' => false, 'from' => '18.10.2018 00:00:00'],
                false,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'maintenance since yesterday' =>
            [
                ['enabled' => false, 'from' => '16.10.2018 00:00:00'],
                true,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'maintenance until tomorrow' =>
            [
                ['enabled' => false, 'until' => '18.10.2018 00:00:00'],
                true,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'maintenance until yesterday' =>
            [
                ['enabled' => false, 'until' => '16.10.2018 00:00:00'],
                false,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'before closed maintenance' =>
            [
                ['enabled' => false, 'from' => '16.10.2018 08:00:00', 'until' => '16.10.2018 10:00:00'],
                false,
                ['currentDate' => new DateTime('16.10.2018 07:00:00')]
            ],
            'during closed maintenance' =>
            [
                ['enabled' => false, 'from' => '16.10.2018 08:00:00', 'until' => '16.10.2018 10:00:00'],
                true,
                ['currentDate' => new DateTime('16.10.2018 09:00:00')]
            ],
            'after closed maintenance' =>
            [
                ['enabled' => false, 'from' => '16.10.2018 08:00:00', 'until' => '16.10.2018 10:00:00'],
                false,
                ['currentDate' => new DateTime('16.10.2018 11:00:00')]
            ],
            'before open maintenance' =>
            [
                ['enabled' => false, 'until' => '16.10.2018 08:00:00', 'from' => '16.10.2018 10:00:00'],
                true,
                ['currentDate' => new DateTime('16.10.2018 07:00:00')]
            ],
            'during open maintenance' =>
            [
                ['enabled' => false, 'until' => '16.10.2018 08:00:00', 'from' => '16.10.2018 10:00:00'],
                false,
                ['currentDate' => new DateTime('16.10.2018 09:00:00')]
            ],
            'after open maintenance' =>
            [
                ['enabled' => false, 'until' => '16.10.2018 08:00:00', 'from' => '16.10.2018 10:00:00'],
                true,
                ['currentDate' => new DateTime('16.10.2018 11:00:00')]
            ],
            'request from ip-range' =>
            [
                ['enabled' => false, 'allowed_ip' => ['10.*.*.*']],
                false,
                ['clientIp' => '10.0.0.1']
            ],
            'request outside ip-range, but maintenance not enabled' =>
            [
                ['enabled' => false, 'allowed_ip' => ['10.*.*.*'], 'template' => 'foo'],
                false,
                ['clientIp' => '192.0.0.1']
            ],
            'request outside ip-range, with maintenance enabled' =>
            [
                ['enabled' => true, 'allowed_ip' => ['10.*.*.*'], 'template' => 'foo'],
                true,
                ['clientIp' => '192.0.0.1']
            ],
            'before maintenance date range' =>
            [
                ['enabled' => false, 'from' => '18.10.2018 00:00:00', 'until' => '19.10.2018 00:00:00'],
                false,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'within maintenance date range' =>
            [
                ['enabled' => false, 'from' => '18.10.2018 00:00:00', 'until' => '19.10.2018 00:00:00'],
                true,
                ['currentDate' => new DateTime('18.10.2018 09:53:00')]
            ],
            'after maintenance date range' =>
            [
                ['enabled' => false, 'from' => '18.10.2018 00:00:00', 'until' => '19.10.2018 00:00:00'],
                false,
                ['currentDate' => new DateTime('20.10.2018')]
            ]
        ];
    }
}
