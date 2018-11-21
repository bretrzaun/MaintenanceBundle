<?php
namespace BretRZaun\MaintenanceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function index(): Response
    {
        return new Response('content');
    }
}
