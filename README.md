ShopBundle
===============

Introduction
------------
This Symfony bundle offers a manage website Shop.

## Prerequisites

This version of the bundle requires Symfony Flex. 

##Installation

### Step 1: Download ShopBundle using composer
This library is available on [Packagist](http://packagist.org/packages/octopouce-mu/shop-bundle).
```
composer require octopouce-mu/shop-bundle
```
Composer will install the bundle to your project's `vendor/` directory.

### Step 3: Setting AdminBundle
You go to the documentation [OctopouceAdminBundle](http://packagist.org/packages/octopouce-mu/admin-bundle).

### Step 4: Import Octopouce Shop routing file
Now that you have activated and configured the bundle, all that is left to do is import the routing files.
```yaml
# config/routes/octopouce.yaml

_octopouce_shop:
    resource: "@OctopouceShopBundle/Resources/config/routing.yaml"
```

### Step 5: Publish the Assets
Now that you have activated and configured the bundle, all that is left to do is import the routing files.
```
php bin/console assets:install --symlink
```
