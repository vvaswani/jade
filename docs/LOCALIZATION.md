# Localization

## Overview

Localization is implemented through the use of PHP's `intl` extension and individual translation files, which are named using the [ICU standards](http://userguide.icu-project.org/locale). For example, `en_GB` refers to UK English, `fr_FR` to French, and so on.

Translation files are structured as [PHP arrays](http://zendframework.github.io/zend-i18n/translation/#supported-formats) and should include a `language.name` key, which contains the localized name of the language. The system will automatically detect new translation files and update the application's user interface accordingly.

On every request, the system computes the default locale and language and applies it to strings, dates and times and numbers. This default locale and language is computed from the configured locale (mandatory, specified at install-time) and the user-specified locale (optional).

* An administrator may define the default locale using the `translator.locale` configuration key.
* A user may override the `translator.locale` configuration key using the language switcher in the user interface.

## Default Locale and Language

The default locale and language is computed on every request using the following rules:

* The system will first look up the `translator.locale` configuration key and check for a corresponding translation file. This is set as the default.
* The system will then check for a user-specified locale. This is the locale specified by the user through the language switcher in the application's user interface. If found, this is set as the new default.

The locales specified in PHP's `intl.default_locale` variable or in `Accept-Language` client HTTP headers  are intentionally ignored, as these are not guaranteed to be present in all cases.

## Language Switcher

The application interface contains a language switcher, which can be used to override (on a per-client basis) the default locale and language specified in the `translator.locale` configuration key. This user-selected locale is stored for 30 days as a session cookie on the client.

The language switcher is only visible when more than one translation file exists.

## Additional Locales and Languages

Support for additional locales and languages can be added by creating corresponding translation files, as follows:

* Copy the `module/Application/language/en_GB.php` translation file and rename it using the ICU code for the locale and language you wish to support.
* Update the `language.name` key in the translation file to reflect the local name of the language.
* Translate the strings in the translation file to their localized counterparts. A reference example is provided below.

The locale and language should now appear in the language switcher in the application's user interface. To set the new locale and language as the default, update the `translator.locale` key with the correct locale code.

### Sample Translation File

```
<?php

// module/Application/language/fr_FR.php

namespace Application;

return array(

	// language
	'language.name' => "Français",

	'common.create' => "Créer un nouveau",
	'common.view' => "Voir",
	'common.edit' => "Modifier",
	'common.delete' => "Supprimer",
	'common.save' => "Enregistrer",
	'common.download' => "Télécharger",
	'common.cancel' => "Annuler",
	'common.generate' => "Générer",
	...
);
```