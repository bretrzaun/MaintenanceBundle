# Maintenance Bundle

## Installation

```
composer require bretrzaun/maintenance-bundle
```

Register bundle in `config/bundles.php`:

```
\BretRZaun\MaintenanceBundle\MaintenanceBundle::class => ['all' => true]
```

## Configuration

Create the following configuration file

```
# config/packages/maintenance.yaml
maintenance:
    enabled: false
    template: 'maintenance.html.twig'
    #from: 01.12.2018 00:00:01
    #until: 03.12.2018 00:00:01
    allowed_ip: ['10.*.*.*']
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
```
    {% if not maintenance_mode() %}
        ...
    {% endif %}
```

If the option "allowed_ip" is used, certain users can access the application even it is in maintennce mode.
To make these users aware of this you can add the following to the layout template:

```
{% if maintenance_mode_allowed() %}
    <div class="alert alert-warning">Maintenance mode is activated!</div>
{% endif %}
```
