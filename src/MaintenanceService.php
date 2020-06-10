<?php
namespace BretRZaun\MaintenanceBundle;

use DateTime;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class MaintenanceService implements MaintenanceServiceInterface
{
    /**
     * @var DateTime
     */
    private $currentDate;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger = null)
    {
        $this->setCurrentDate(new DateTime('now'));
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }
    }

    public function setCurrentDate(DateTime $date): void
    {
        $this->currentDate = $date;
    }

    public function isMaintenance(): bool
    {
        $maintenance = $this->parameterBag->get('maintenance');

        if (!isset($maintenance['enabled']) || $maintenance['enabled'] === false) {
            // date check
            $from = null;
            $until = null;
            if (isset($maintenance['from'])) {
                $from = DateTime::createFromFormat('d.m.Y H:i:s', $maintenance['from']);
                if (!$from) {
                    throw new RuntimeException('Maintenance: invalid date format "from" (expects d.m.Y H:i:s)');
                }
            }
            if (isset($maintenance['until'])) {
                $until = DateTime::createFromFormat('d.m.Y H:i:s', $maintenance['until']);
                if (!$until) {
                    throw new RuntimeException('Maintenance: invalid date format "until" (expects d.m.Y H:i:s)');
                }
            }
            if ($from && $until) {
                if ($from <= $until) {
                    if ($this->currentDate < $from || $this->currentDate > $until) {
                        return false;
                    }
                    $this->logger->notice(
                        'Maintenance mode is active from {from} to {until}: ',
                        [
                            'from' => $from->format('d.m.Y H:i:s'),
                            'until' => $until->format('d.m.Y H:i:s'),
                            'now' => $this->currentDate->format('d.m.Y H:i:s')
                        ]
                    );

                } else {
                    if ($this->currentDate < $from && $this->currentDate > $until) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
            if ($from) {
                if ($from !== false && $from > $this->currentDate) {
                    return false;
                }
                $this->logger->notice(
                    'Maintenance mode is active since: ' . $from->format('d.m.Y H:i:s'),
                    ['now' => $this->currentDate->format('d.m.Y H:i:s')]
                );
            }
            if ($until) {
                if ($until === false || $this->currentDate > $until) {
                    return false;
                }
                $this->logger->notice(
                    'Maintenance mode is active until: '.$until->format('d.m.Y H:i:s'),
                    ['now' => $this->currentDate->format('d.m.Y H:i:s')]
                );
            }
            if (!isset($maintenance['from']) && !isset($maintenance['until'])) {
                return false;
            }
        } else {
            $this->logger->info('Maintenance mode is permanently enabled');
        }
        return true;
    }

    /**
     * check for internal request
     *
     * @param Request|null $request
     * @return bool
     */
    public function isInternal(Request $request = null): bool
    {
        $maintenance = $this->parameterBag->get('maintenance');
        return $request &&
            isset($maintenance['allowed_ip']) &&
            $this->matchIp($maintenance['allowed_ip'], $request->getClientIp());
    }

    /**
     * check an IP-mask (including wildcards) with an ip address
     *
     * @param string|array $ipmask
     * @param string|null $remoteIp
     * @return bool
     */
    protected function matchIp($ipmask, $remoteIp): bool
    {
        if ($remoteIp === null) {
            return false;
        }
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
