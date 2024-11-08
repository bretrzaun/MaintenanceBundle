<?php
namespace BretRZaun\MaintenanceBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ControllerTest extends WebTestCase
{
    public function testMaintenanceEnabled(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('<h1>Maintenance</h1>', $content);
        } else {
            $this->assertContains('<h1>Maintenance</h1>', $content);
        }
    }
}
