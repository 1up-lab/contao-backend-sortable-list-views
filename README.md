Contao backend with sortable list views
=========================================

Adds the missing sorting mode to Contao: custom sort without the need for a parent table.

## Installation
### Require bundle
```bash
composer require oneup/contao-backend-sortable-list-views
```

### Add routes
```yaml
# config/routes.yaml

OneupContaoBackendSortableListViewsBundle:
    resource: "@OneupContaoBackendSortableListViewsBundle/config/routes.yaml"
```

## Configuration
```php
# contao/dca/tl_my_custom_table.php

// Add sorting flag
$GLOBALS['TL_DCA']['tl_my_custom_table']['list']['sorting']['sortableListView'] = true;

// Add database field
$GLOBALS['TL_DCA']['tl_my_custom_table']['fields']['sorting']['sql'] = 'int(10) unsigned NOT NULL default 0';
```

Run a Contao migration / database update and enjoy happy drag&drop sorting!

## Development
### Backend
#### Install dependencies
`composer install`

#### Run the code style fixer
`php composer cs-fixer`

#### Run the static analyzer
`php composer phpstan`

#### Run the unit tests
`php composer phpunit`

### Frontend
#### Install dependencies
`npm install`

#### Build frontend assets
`npm run build`
