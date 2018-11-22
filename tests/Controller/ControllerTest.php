<?php
namespace BretRZaun\MaintenanceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ControllerTest extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        require_once __DIR__ . '/../AppKernel.php';
        $kernel = new \AppKernel('test', true);
        $kernel->boot();
        return $kernel;
    }

    public function testMaintenanceEnabled(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertContains('<h1>Maintenance</h1>', $content);
    }
}
