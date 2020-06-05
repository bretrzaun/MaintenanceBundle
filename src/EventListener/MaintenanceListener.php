<?php
namespace BretRZaun\MaintenanceBundle\EventListener;

use BretRZaun\MaintenanceBundle\MaintenanceService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

class MaintenanceListener
{
    private $parameters;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var MaintenanceService
     */
    private $maintenanceService;

    public function __construct(
        ParameterBagInterface $parameters,
        Environment $twig,
        MaintenanceService $maintenanceService
    ) {
        $this->parameters = $parameters;
        $this->twig = $twig;
        $this->maintenanceService = $maintenanceService;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
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
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
}
