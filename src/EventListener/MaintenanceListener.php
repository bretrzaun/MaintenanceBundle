<?php
namespace BretRZaun\MaintenanceBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class MaintenanceListener
{
    private $container;
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var \DateTime
     */
    private $currentDate;

    public function __construct(ContainerInterface $container, Environment $twig)
    {
        $this->container = $container;
        $this->twig = $twig;
        $this->setCurrentDate(new \DateTime('now'));
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
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

        // Manueller Schalter
        if ($maintenance['enabled'] === false) {
            // Datumsprüfung
            if (isset($maintenance['from'])) {
                $from = \DateTime::createFromFormat('d.m.Y H:i:s', $maintenance['from']);
                if (!$from) {
                    throw new \RuntimeException('Maintenance: invalid date format "from" (expects d.m.Y H:i:s)');
                }
                if ($from !== false && $from > $this->currentDate) {
                    return;
                }
            } elseif (isset($maintenance['until'])) {
                $until = \DateTime::createFromFormat('d.m.Y H:i:s', $maintenance['until']);
                if (!$until) {
                    throw new \RuntimeException('Maintenance: invalid date format "until" (expects d.m.Y H:i:s)');
                }
                if ($until === false || $this->currentDate > $until) {
                    return;
                }
            } else {
                return;
            }
        }

        // This will detect if we are in dev environment
        $debug = $this->container->get( 'kernel')->getEnvironment() === 'dev';

        if ($maintenance && !$debug) {
            $template = $this->twig->render($maintenance['template']);

            // We send our response with a 503 response code (service unavailable)
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }

    public function setCurrentDate(\DateTime $date): void
    {
        $this->currentDate = $date;
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
