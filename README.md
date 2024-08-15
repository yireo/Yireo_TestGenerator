# Yireo TestGenerator for Magento 2

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

Or generate integration tests, overriding existing tests as well (DANGEROUS):
```bash
bin/magento yireo:test:generate Yireo_Example --override-existing=1
```

Or generate unit tests:
```bash
bin/magento yireo:test:generate Yireo_Example --type=unit
```
