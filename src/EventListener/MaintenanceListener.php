<?php
namespace BretRZaun\MaintenanceBundle\EventListener;

use BretRZaun\MaintenanceBundle\MaintenanceService;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

class MaintenanceListener
{
    private $container;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var MaintenanceService
     */
    private $maintenanceService;

    public function __construct(ContainerInterface $container, Environment $twig, MaintenanceService $maintenanceService)
    {
        $this->container = $container;
        $this->twig = $twig;
        $this->maintenanceService = $maintenanceService;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if (!$this->container->hasParameter('maintenance')) {
            return;
        }
        $maintenance = $this->container->getParameter('maintenance');

        if ($this->maintenanceService->isMaintenance() &&
            !$this->maintenanceService->isInternal($event->getRequest())) {
            $template = $this->twig->render($maintenance['template']);

            // We send our response with a 503 response code (service unavailable)
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
}
