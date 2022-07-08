# Predic CPT metadata

This is the plugin that extends the WP metadata API to support saving the CPT data to custom DB tables.

When you register the support for some post type, using the filter/class mentioned below, the plugin 
will be able to create a custom table in database to store the metadata for this custom post type.

This doesn't happen automatically, you need to use the API class or action and filters hooks, 
provided by this plugin to perform CRUD operations.

Example: If we register the post type 'book', the DB table (DB prefix `wp`) that will be created is:
`wp_predic_cpt_metadata_bookmeta` with the columns:
* meta_id
* predic_cpt_metadata_book_id
* meta_key
* meta_value

## Plugin setup
Please follow these steps:
* Install and activate the plugin
* Visit the plugin settings page in admin menu: CPT metadata setup
* If you registered post types to be used with this plugin, you will se a table in the settings page (See Integration with other plugins below)
* Each registered CPT will have either a checkmark or red "Fix me!" button
* All you need todo is to click "Fix me" buttons, one by one, and that will create custom DB tables for this plugins
* Once these DB tables are created, there is no more setup
* You can use the settings page to debug data

**IMPORTANT: If you registered post types to be used by this plugin, but the tables are missing in DB, 
the Admin Notice will be displayed in the wp-admin.** 

This is very important as the plugin will not work if we don't have the tables created in the DB.

**IMPORTANT**: By activating the plugin and setting up the DB tables, you haven't completed the 
final setup for the plugin todo CRUD operations. You need to use the class API to manually implement this
into your code. For more info see the Integration with other plugins section below.

## Integration with other plugins
In the plugin root, we added an example PHP class that you can use for easier integration
with other plugins: `exampleIntegration/src/Services/CptMetadataService.php`

### Integration steps

#### 1. Copy the exampleIntegration folder we provided

You need to copy everything that is in the plugins folder `exampleIntegration` and change these lines in the `CptMetadataService` class:

* line 3: `namespace {Namespace}\Services;`
* line 5: `use {Namespace}\Contracts\CptMetadataServiceInterface;`
* line 6: `use {Namespace}\Contracts\ServicePluginInterface;`
* line 18: `'post-type-id'`. Add your CPT ids here.
* Call `CptMetadataService` class method `registerCustomPostTypes` as in the example below and you are all done.
* Call the method in your functions.php file, or where you want it

```php
$cptMetadataService = new CptMetadataService();
$cptMetadataService->registerCustomPostTypes();
```

This class contains all methods to perform the CRUD you need to in your plugin.

**Important** To be able to use the clas CRUD methods, you need to run this methods earliest at `init` hook, the priority `100`.
See example below:

```php
add_action('init', function () {
    $class = new CptMetadataService();
    $class->updateMeta('book', 5, 'meta_key', 'meta_value');
}, 100);
```

#### 2. Create a new tables in DB
Once you do all the steps in the point 1, when you load your site wp-admin, you will notice a admin notice with the message:
`Error: A very important DB table is missing. Please visit this page to fix this issue!!!`.

* Click the link in the message and you will be redirected to a plugin settings page (Admin -> CPT metadata setup). 
* Now just click all **Red buttons with text `Fix issue!`**.
* All done. 
* You only need to click these buttons once or each time you add a new CPT support in the `CptMetadataService` class you copied.

#### 3. Handle exceptions the way you want

In the copied class `CptMetadataService`, apply your solution to handle exceptions if you need it.
Those lines are marked with this comment: `// Log Exception if you wish :)`


## Settings / Debug data
Visit the plugin settings page, the `CPT metadata settings` admin menu.

There we will have a list of all registered post types that will use this plugin.

* If you see a green checkmark, next to the post type ID, then all is ok and this plugin is
integrated good. 
* If you see a red "Fix it" button, then the DB table is missing and you need to click it to fix this.

### Check all data for a post ID
On the settings page, under every post type, we have a form with the label: "Debug data".
To see all metadata in this custom table, for the given post ID, enter post ID in the input field
and press the "Debug" button.

### Clean leftover data
When the post is deleted we remove the data for this post automatically, so if you ever feel that some data was not 
removed just go to your settings page and click the clean button, and it will verify this or remove the leftover data.

What we do here is: We basically get all post ids in custom table, get all existing post ids for that CPT and compare if we have 
some extra records to remove in our custom db tables.

## Removing data on custom post type delete from WP admin
If you delete the CPT from the WP admin, the `after_delete_post` action hook will run, WP default behaviour,
and then this plugin will automatically remove all data from the custom DB table for that post id.

## Partial integration with other plugins
If you don't want to use the example class, mentioned above, you can integrate only the features
you need for your plugin.

Add these filters into your plugin to interact with the CPT metadata plugin CRUD.

**IMPORTANT: All filters must be used after the init hook at priority 100.** 

This is very important as before this
WP life cycle we can't execute code that needs post type already registered into the WordPress.

**IMPORTANT: You must register all custom post types that will use this plugin.**

Example: If you want to use the filter hook below to register the supported post types this would be the code.
```php
add_action(
    'init',
    function() {
        add_filter('predic_cpt_metadata_post_type_objects', function (array $postTypes): array {
            return array_merge(
                $postTypes,
                ['post-type-id', 'post-type-id-2'...] // Array
            );
        });
    },
    100
);
```

### Here is the list of all filters to use in your plugin.

#### Register custom post types that will be supported by this plugin.
```php
add_filter('predic_cpt_metadata_post_type_objects', function (array $postTypes): array {
    return array_merge(
        $postTypes, // Array
        ['post-type-id', 'post-type-id-2'...] // Array
    );
});
```

#### Get all meta from the database for the given post ID.
```php
return apply_filters(
    'predic_cpt_metadata_post_type_get_all_meta',
    $postTypeId, // string
    $postId // int
);
```

#### Get the single meta key from the database for the given post ID.
```php
return apply_filters(
    'predic_cpt_metadata_post_type_get_meta',
    $postTypeId, // string
    $postId, // int
    $metaKey // string
);
```

#### Return all results for the meta key for the post type.
```php
return apply_filters(
    'predic_cpt_metadata_post_type_get_all_meta_for_meta_key',
    $postTypeId, // string
    $metaKey // string
);
```

#### Return all meta for given post ids. For the post type.
```php
return apply_filters(
    'predic_cpt_metadata_post_type_get_all_meta_by_ids',
    $postTypeId, // string
    $ids // Array
);
```

#### Update or create the data for the meta key
```php
do_action(
    'predic_cpt_metadata_post_type_update_meta',
    $postTypeId, // string
    $postId, // int
    $metaKey, // string
    $data // mixed
);
```

#### Delete the data for the meta key and post ID
```php
do_action(
    'predic_cpt_metadata_post_type_delete_meta',
    $postTypeId, // string
    $postId, // int
    $metaKey // string
);
```

## Change plugin version
To change plugin version we need to edit `predic-cpt-metadata.php` - Plugin main root file header

## Future todo tasks with very low priority
* Extend WP_Query to search custom meta tables for meta query
* Remove custom tables on site delete so we have no leftovers in DB
* Refactor `LoggerHelper` to use `\Monolog\Handler\RotatingFileHandler` so we are safe from bigger log files

## Changelog

#### Version 1.0.0
* Date: 08.06.2022.
* Description: 
  * First official release