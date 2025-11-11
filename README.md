# Maintenance Bundle

[![Tests](https://github.com/bretrzaun/MaintenanceBundle/actions/workflows/tests.yml/badge.svg)](https://github.com/bretrzaun/MaintenanceBundle/actions/workflows/tests.yml) 
[![Latest Stable Version](https://poser.pugx.org/bretrzaun/maintenance-bundle/v/stable)](https://packagist.org/packages/bretrzaun/maintenance-bundle)
[![Total Downloads](https://poser.pugx.org/bretrzaun/maintenance-bundle/downloads)](https://packagist.org/packages/bretrzaun/maintenance-bundle)
[![License](https://poser.pugx.org/bretrzaun/maintenance-bundle/license)](https://packagist.org/packages/bretrzaun/maintenance-bundle)

## Installation

```
composer require bretrzaun/maintenance-bundle
```

Register bundle in `config/bundles.php`:

```php
\BretRZaun\MaintenanceBundle\MaintenanceBundle::class => ['all' => true]
```

## Configuration

Create the following configuration file

```yaml
# config/packages/maintenance.yaml
maintenance:
    enabled: false
    template: 'maintenance.html.twig'
    #from: 01.12.2018 00:00:01
    #until: 03.12.2018 00:00:01
    
    # IP addresses allowed to access during maintenance
    # Supports:
    # - Wildcards: 10.*.*.* or 192.168.*.*
    # - CIDR notation: 192.168.1.0/24
    # - IPv6: 2001:db8::/32
    # - Exact IPs: 192.168.1.1
    allowed_ip:
      - '10.*.*.*'           # All 10.x.x.x IPs
      - '192.168.1.0/24'     # Entire /24 network
      - '2001:db8::/32'      # IPv6 network
      - '203.0.113.42'       # Single IP
```

### Options

- **enabled**: if set to `true` manually activates the maintenance mode
- **template**: template to render, when maintenance mode is activated
- **from**: begin maintenance mode at the given date/time (only when 'enabled' is false)
- **until**: end maintenance mode at the given date/time (only when 'enabled' is false)
- **allowed_ip**: list of IP addresses who can access the application even in maintenance mode

## Template

The bundle has a default maintenance template (see `src/Resources/views/maintenance.html.twig`).

You can use your own template (see configuration). In case your maintenance template extends from a parent layout
you might want to exclude certain parts while in maintenance (e.g. a menu).
This can be done with like so:
```twig
    {% if not maintenance_mode() %}
        ...
    {% endif %}
```

If the option "allowed_ip" is used, certain users can access the application even it is in maintenance mode.
To make these users aware of this you can add the following to the layout template:

```twig
{% if maintenance_mode_allowed() %}
    <div class="alert alert-warning">Maintenance mode is activated!</div>
{% endif %}
```
