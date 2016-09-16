# Behat Drupal Extension

[![Build Status](https://travis-ci.org/nuvoleweb/drupal-behat.svg?branch=1.0.x)](https://travis-ci.org/nuvoleweb/drupal-behat)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nuvoleweb/drupal-behat/badges/quality-score.png?b=1.0.x)](https://scrutinizer-ci.com/g/nuvoleweb/drupal-behat/?branch=1.0.x)

Nuvole's Behat Drupal Extension extends the popular [Behat Drupal Extension](https://www.drupal.org/project/drupalextension)
in order to provide the following features:

- Additional step definitions covering contributed modules, core functionality and popular third-party plugins (see below
  for an exhaustive list).
- Base raw contexts class can access the global Behat service container.  
- Developers can organize their test using dependency injection by declaring their services in a YAML file and/or 
  override default Behat services.

## Installation
Follow the [Quick start](https://github.com/jhedstrom/drupalextension#quick-start) section on the Behat Drupal Extension
page, only use `NuvoleWeb\Drupal\DrupalExtension` instead of the native `Drupal\DrupalExtension` as in your `behat.yml`
as shown below:

```yaml
default:
  suites:
    default:
      paths:
        - %paths.base%/../../features
      contexts:
        - Drupal\DrupalExtension\Context\DrupalContext
        - NuvoleWeb\Drupal\DrupalExtension\Context\DrupalContext
        ...
  extensions:
    Behat\MinkExtension:
      goutte: ~
      ...
    # Use "NuvoleWeb\Drupal\DrupalExtension" instead of "Drupal\DrupalExtension".
    NuvoleWeb\Drupal\DrupalExtension:
      api_driver: "drupal"
      ...
      services: "tests/my_services.yml"
      text:
        node_submit_label: "Save and publish"
```

## Extension settings
Nuvole's Behat Drupal Extension accepts all parameters of the original Behat Drupal Extension and it provides the 
following additional ones:

| Setting | Description |
|---------|-------------|
| `services: "tests/my_services.yml"` | Path to your custom service definition YAML file. |
| `text.node_submit_label: "Save and publish"` | Label of node form submit button (different in Drupal 7/6 and Drupal 8). |
