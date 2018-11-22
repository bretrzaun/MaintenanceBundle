<?php
namespace BretRZaun\MaintenanceBundle\Tests;

use BretRZaun\MaintenanceBundle\MaintenanceService;
use BretRZaun\MaintenanceBundle\Twig\Extension;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{

    public function testMaintenance(): void
    {
        $maintenanceService = $this->getMockBuilder(MaintenanceService::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMaintenance'])
            ->getMock()
        ;
        $maintenanceService->expects($this->once())
            ->method('isMaintenance')
            ->willReturn(true);

        $extension = new Extension($maintenanceService);
        $this->assertTrue($extension->isMaintenance());
    }
}
