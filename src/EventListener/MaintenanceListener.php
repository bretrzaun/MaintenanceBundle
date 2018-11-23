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

        // IP-Prüfung durchführen
        if (isset($maintenance['allowed_ip']) &&
            $this->matchIp($maintenance['allowed_ip'], $event->getRequest()->getClientIp())
        ) {
            return;
        }

        if (!$this->maintenanceService->isMaintenance()) {
            return;
        }

        $template = $this->twig->render($maintenance['template']);

        // We send our response with a 503 response code (service unavailable)
        $event->setResponse(new Response($template, 503));
        $event->stopPropagation();
    }

    /**
     * check an IP-mask (including wildcards) with an ip address
     *
     * @param string|array $ipmask
     * @param string $remoteIp
     * @return bool
     */
    protected function matchIp($ipmask, string $remoteIp): bool
    {
        if (\is_string($ipmask)) {
            $ipmask = [$ipmask];
        }
        foreach ($ipmask as $entry) {
            $pattern = '/^' . str_replace(['*', '.'], ['[0-9]{1,3}', '\.'], $entry) . '$/';
            preg_match($pattern, $remoteIp, $matches);
            if (\count($matches) > 0) {
                return true;
            }
        }
        return false;
    }
}
