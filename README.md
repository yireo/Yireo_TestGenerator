# Yireo TestGenerator for Magento 2

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-test-generator)
<!-- badges.specs.end -->


**Use this module to kickstart unit tests or integration tests for your existing module.**

### Installation
```bash
composer require yireo/magento2-test-generator
bin/magento module:enable Yireo_TestGenerator
```

### Usage
Generate integration tests for the module `Yireo_Example` if they don't exist yet:
```bash
bin/magento yireo:test:generate Yireo_Example
```

Or generate integration tests, overriding existing tests as well (DANGEROUS) unless there is a token `@test-generator-skip-override` found in the test file contents:
```bash
bin/magento yireo:test:generate Yireo_Example --override-existing=1
```

Or generate unit tests:
```bash
bin/magento yireo:test:generate Yireo_Example --type=unit
```

Generate unit tests, overriding the original test, for a specific class only:
````bash
bin/magento yireo:test:generate --override-existing 1 --type unit -- 'LokiCheckout_AccountType' '\Loki\CheckoutAccountType\Config\Source\AccountTypeOptions'
````

### AI generated tests
This module also offers to generate unit tests or integration tests by using AI. The tests generated might not always be perfect and might not even work. To the least, they offer you a kickstart instead of needing to code things yourself.

The following coding AIs are currently supported:

- OpenAI (ChatGPT)
- Anthropic (Claude AI)

Both require an API key to be used. Note that these AI platforms will bill you according to your usage. 

In the Store Configuration under **Yireo > Test Generator** you can enable each AI and configure an API key.

### Extending test types
The Integration Tests can be created by using a custom generator. For instance, there is a `ConfigTestGenerator` which allows for a
specific config test case to be created, as long as the PHP class name matches with `*\Config\Config`.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTestGeneratorListing">
        <arguments>
            <argument name="testGenerators" xsi:type="array">
                <item name="custom" xsi:type="object">YireoTraining\ExampleTestGenerator\Generator\IntegrationTest\CustomTestGenerator</item>
            </argument>
        </arguments>
    </type>
</config>
```

This generator class needs to implement `\Yireo\TestGenerator\Generator\IntegrationTest\SourceTestGeneratorInterface`. See the
`\Yireo\TestGenerator\Generator\IntegrationTest\ConfigTestGenerator` for a full example.

## Todo
- Move the AI mechanisms in separate modules
- Allow to configure the AI prompt yourself
- Add a dry-run flag

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_TestGenerator/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_TestGenerator/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_TestGenerator/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_TestGenerator/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_TestGenerator/compile.yml?label=compile)
<!-- badges.test.end -->
