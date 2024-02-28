# SYSTOPIA's Remote Tools

This extension is meant to be a toolbox for integrating CiviCRM
with other (potentially) remote systems. An example for this
could be your website, and/or another database.

## Features

* Create a secure link between a remote system's user (e.g. Drupal user) and a CivCRM contact.
* Generate and validate customized/personalized secure tokens.
* Get, create, update, and delete entities via a special remote API with user based permission check.
* Forms with validation for create and update.
* Different implementations for the same entity using profiles.
* Remote entity fields can differ from the actual entity fields.

## Requirements

* PHP 7.4 or 8
* CiviCRM 5.57

## Installation

* Install the dependent extensions listed in `info.xml`:
  * [de.systopia.identitytracker](https://github.com/systopia/de.systopia.identitytracker)
  * [de.systopia.xcm](https://github.com/systopia/de.systopia.xcm)
* Add the extension folder to your CiviCRM `ext` directory.
* If your project uses composer: Add the requirements listed in `composer.json` to your composer project:
  * [systopia/expression-language-ext](https://github.com/systopia/expression-language-ext)
  * [systopia/opis-json-schema-ext](https://github.com/systopia/opis-json-schema-ext)
  * [webmozart/assert](https://github.com/webmozarts/assert)
* Otherwise: Run `composer update` inside the `de.systopia.remotetools` folder.
* Enable the extension. (Either via UI or `cv ext:enable de.systopia.remotetools`)

For the frontend part you can use the [CiviRemote Drupal module](https://github.com/systopia/civiremote).

## Usage

You can use this extension via other extensions that rely on Remote Tools:

* [Remote Events Extension](https://github.com/systopia/de.systopia.remoteevent)
* [Remote Activity Extension](https://github.com/systopia/de.systopia.remoteactivity)

### Providing own remote entities

If you want to provide an own remote entity that accesses the entity `MyEntity`,
you can do the following:

* Create the class `\Civi\Api4\RemoteMyEntity` (name can be freely chosen) that extends [`Civi\RemoteTools\Api4\AbstractRemoteEntity`](Civi/RemoteTools/Api4/AbstractRemoteEntity.php).
  * Implement the method `permissions()` to restrict access to your remote API user. (See [CiviCRM documentation](https://docs.civicrm.org/dev/en/latest/security/permissions/#apiv4).)
* Create a class that extends [`\Civi\RemoteTools\EntityProfile\AbstractRemoteEntityProfile`](Civi/RemoteTools/EntityProfile/AbstractRemoteEntityProfile.php). (For read only entities use [`\Civi\RemoteTools\EntityProfile\AbstractReadOnlyRemoteEntityProfile`](Civi/RemoteTools/EntityProfile/AbstractReadOnlyRemoteEntityProfile.php) instead.) The various methods are documented in [`\Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface`](Civi/RemoteTools/EntityProfile/RemoteEntityProfileInterface.php).
  * Add the public constants `NAME` (set to e.g. `my-profile`, `default`, or also an empty string), `ENTITY_NAME` (set to `MyEntity`), `REMOTE_ENTITY_NAME` (set to `RemoteMyEntity`).
  * Use the trait [`\Civi\RemoteTools\EntityProfile\Traits\ConstProfileMetadataTrait`](Civi/RemoteTools/EntityProfile/Traits/ConstProfileMetadataTrait.php).
* Register the class as service in the service container via [`hook_civicrm_container`](https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/) like this:
```php
$container->autowire(MyRemoteEntityProfile::class)
  ->addTag(MyRemoteEntityProfile::SERVICE_TAG);
```

Now you should be able to access data via APIv4, e.g.

```php
civicrm_api4('RemoteMyEntity', 'get', ['profile' => 'my-profile']);
```

(Use the APIv4 explorer to test.)

Most methods in the profile class have a parameter named `$contactId`. This
contains the remote contact ID resolved to a CiviCRM contact ID or `NULL` if no
remote contact ID was given. If a given remote contact ID could not be resolved,
access is denied. The resolving is done using the extension
[de.systopia.identitytracker](https://github.com/systopia/de.systopia.identitytracker).

You can find more information in the PHPDoc of the various classes, interfaces,
and methods.

All elements that can be safely used by third parties are marked with `@api`.
Their behavior will be stable within minor updates. Breaking changes may only
happen in major updates (semantic versioning). If a class or interface is marked
with `@api` then this applies to all methods within it. If you'd like to use
code that is not labeled as API, yet, please open an issue, so we can consider
to add it to the API.

#### Forms

Forms for creation and update are specified in a
[`\Civi\RemoteTools\Form\FormSpec\FormSpec`](Civi/RemoteTools/Form/FormSpec/FormSpec.php).
This specification will be converted to [JSON Forms](https://jsonforms.io/).
(Different output would be possible with a custom action handler.) The Drupal
module [civiremote_entity](https://github.com/systopia/civiremote) provides the
corresponding part that accesses the remote entity API and renders the forms as
Drupal forms.

# License

The extension is licensed under [AGPL-3.0](LICENSE.txt).
