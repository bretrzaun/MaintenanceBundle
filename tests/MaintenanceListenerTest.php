<?php
namespace BretRZaun\MaintenanceBundle\Tests;

use BretRZaun\MaintenanceBundle\MaintenanceService;
use DateTime;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use BretRZaun\MaintenanceBundle\EventListener\MaintenanceListener;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class MaintenanceListenerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    /**
     * @param array|null $parameters
     * @param bool $maintenance
     * @param array $context
     * @dataProvider parameterProvider
     */
    public function testListener(array $parameters, bool $maintenance, array $context = []): void
    {
        $parameterBag = new ParameterBag(['maintenance' => $parameters]);
        $twig = $this->createMock(Environment::class);
        if ($parameters) {
            if ($maintenance && isset($parameters['template'])) {
                $twig->expects($this->once())
                    ->method('render')
                    ->with('foo')
                    ->willReturn('maintenance template content')
                ;
            }
        }

        $maintenanceService = new MaintenanceService($parameterBag);
        if (isset($context['currentDate'])) {
            $maintenanceService->setCurrentDate($context['currentDate']);
        }

        $listener = new MaintenanceListener($parameterBag, $twig, $maintenanceService);
        $server = [];
        if (isset($context['clientIp'])) {
          $server['REMOTE_ADDR'] = $context['clientIp'];
        }
        $request = new Request([], [], [], [] ,[], $server);
        $event = new RequestEvent(self::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $listener->onKernelRequest($event);

        $response = $event->getResponse();

        if ($maintenance) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
            $this->assertEquals('maintenance template content', $response->getContent());
        } else {
            $this->assertNull($event->getResponse());
        }
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
            'maintenance until tomorrow' =>
            [
                ['enabled' => false, 'from' => '18.10.2018 00:00:00'],
                false,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'maintenance since yesterday' =>
            [
                ['enabled' => false, 'from' => '16.10.2018 00:00:00', 'template' => 'foo'],
                true,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'maintenance until tomorrow (with template)' =>
            [
                ['enabled' => false, 'until' => '18.10.2018 00:00:00', 'template' => 'foo'],
                true,
                ['currentDate' => new DateTime('17.10.2018')]
            ],
            'maintenance until yesterday' =>
            [
                ['enabled' => false, 'until' => '16.10.2018 00:00:00'],
                false,
                ['currentDate' => new DateTime('17.10.2018')]
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
            ]
        ];
    }
}
