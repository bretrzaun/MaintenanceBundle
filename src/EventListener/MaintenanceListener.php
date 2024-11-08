<?php
namespace BretRZaun\MaintenanceBundle\EventListener;

use BretRZaun\MaintenanceBundle\MaintenanceServiceInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

class MaintenanceListener
{
    public function __construct(
        private readonly ParameterBagInterface $parameters,
        private readonly Environment $twig,
        private readonly MaintenanceServiceInterface $maintenanceService
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) { 
            return;
        }
        if (!$this->parameters->has('maintenance')) {
            return;
        }
        $maintenance = $this->parameters->get('maintenance');

        if ($this->maintenanceService->isMaintenance() &&
            !$this->maintenanceService->isInternal($event->getRequest())) {
            $template = $this->twig->render($maintenance['template']);

            // We send our response with a 503 response code (service unavailable)
            $event->setResponse(new Response($template, Response::HTTP_SERVICE_UNAVAILABLE));
            $event->stopPropagation();
        }
    }
}
