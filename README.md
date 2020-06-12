# Orba Config

This module allows to manage Magento configuration (core_config_data) using csv files. It is meant as a replacement for native magento system including config.php, environment variables and commands `app:config:dump` and `app:config:import` due to their limitations.

### Basic Usage

1. Create csv file with configuration:

    |path|scope|code|value|state
    |---|---|---|---|---|
    |msp_securitysuite_recaptcha/backend/enabled|store|italy|1|always

2. Keep it anywhere you want, preferably in git repository of your Magento project.

3. In your CI/CD process, use this command to import the file automatically:

    ``` bin/magento orba:config --files myConfiguration.csv ```
    
The command clears all cache automatically, so changes should be immediately visible.

### Csv format

CSV requirements:
- value separator: comma
- encoding: UTF-8 without BOM
- header row with column names is required
- the following columns are required: path, scope, code, value, state

Be careful about saving the file in Windows. File may be saved with semicolon as value separator and with Windows encoding due to user preferences.

### Scope and code

Possible scopes are: default, website and store. If you leave the scope field empty, default scope is used.

Scope code needs to be:
- website code - for scope website
- store code - for scope store
- <empty_string> - for scope default

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default| |1|always
|msp_securitysuite_recaptcha/backend/enabled|websites|b2c|1|always
|msp_securitysuite_recaptcha/backend/enabled|store|italy|1|always

### State

State determines the import behaviour. Possible states:

- always
- absent
- ignored
- init
- once

Support for additional states may be added using di.xml.

#### Always

Import the config from csv to db (no matter if config exists in db or not).

#### Absent
 
Remove the config from db.

#### Ignored

Ignore the config (as if it was not listed in the csv file).

#### Init

Import the config only if it does not exist in database. This means csv value has lower priority than config specified in admin panel and will never override value set manually by admin user.

Watchout: when you change one config in Magento admin panel, the whole section is saved (database is filled with default values). As a result, there is pretty good chance that config already exists in database even if you never changed it.

#### Once

Import the config only if this config and value combination has not been imported from csv to database.

Example:
- admin user sets config ABC to 1. Value in database is 1
- you import csv with value 2 and state once. The command finds out this config has never been imported from csv yet (based on new database column: imported_value_hash), so csv config is imported. Value in database is 2
- Admin user changes the config to 1. Value in database is 1
- You once more import csv with value 2 and state once - the command finds out that this config has already been imported to database using value 2, so import is skipped. Value in database is 1
- You once more import csv, but this time with value 3 - the command finds out that this config has already been imported to database, but previous import used a different value, so csv config is imported. Value in database is 3.

### Value

In most cases, value should be a string with raw value. However, there are some special cases:
- instead of raw value, you may use expression, e.g. {{env MY_ENV_VAR}}
- for configs with backendType Encrypted, you should specify plain text value (consequently, you should use expression "env" for security reasons)
- for configs with backendType ArraySerialized, you should use json string, e.g. {"name": "abc","code":"xyz"}

### Expressions

The following expressions may be used as value:
- env
- file
- null

Support for additional expressions may be added using di.xml.

#### ENV

ENV expression allows you to set secret or environment-specific configuration using environment variable.

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/general/private_key|default|{env RECAPTCHA_KEY}|1|always

RECAPTCHA_KEY=passAbc123 bin/magento orba:config --files myConfiguration.csv

Notice that this may be any environment variable. It needs not (and should not) be environment variable in Magento format, e.g. CONFIG__DEFAULT__CONTACT__EMAIL__RECIPIENT_EMAIL.

Notice that Magento allows you to set configuration using environment variables by default, e.g. CONFIG__DEFAULT__CONTACT__EMAIL__RECIPIENT_EMAIL="contact@example.com". However, native system makes it impossible to override value from environment in admin panel, whereas this tool allows this.

#### FILE

FILE expression allows you to set secret or environment-specific configuration using an external file.

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/general/private_key|default|{file .recaptcha_key}|1|always

```
touch .recaptcha_key
chmod 600 .recaptcha_key
echo "passAbc123" > .recaptcha_key
bin/magento orba:config --files myConfiguration.csv
```

#### NULL

NULL expression allows you to set configuration to null (when it is important to distinguish null from empty string).

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default|{null}|1|always

### Different environments

If you need different configuration for you production, test and dev environments, you may use the following file structure:

```
/common.csv
/prod.csv
/uat.csv
/dev.csv
```

The orba:config command allows you to specify multiple files - in such case, they are merged. In you CI/CD process, make sure the command is run with different arguments based on environment:
- prod: orba:config --files common.csv prod.csv
- uat: orba:config --files common.csv uat.csv
- dev: orba:config --files common.csv dev.csv

You may also define all configuration in common.csv, but load values from environment using "ENV" expression.

### Csv - database discrepancies

If you make changes in your configuration.csv file, make sure you mark the config as "absent" instead of removing it from csv file. If you just remove configuration from file, next import will leave database value intact and there will be a discrepancy between file and database.

Also, for the same reason, if you need to change configuration scope, do not just edit scope in the existing config entry. Instead, add new entry from new scope and mark the old scope as "absent".

Example: this is your configuration file before changes:

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|website|b2c|1|always

This is how your configuration file should look like after changing scope:

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default| |1|always
|msp_securitysuite_recaptcha/backend/enabled|website|b2c|1|absent

Notice that this config may have a store scope value in database, set manually by admin user. This import tool will not remove this value. Consequently, it may appear that the import fails to import new value, because store value will obscure website or default value imported from csv file.

### Command

bin/magento orba:config --files file1.csv file2.csv [--env=dev] [--dry-run]

If you specify several files, they will be merged. Final value is taken from the last file in which the configuration appears, in this case: from file2.csv.

## Installation

```
composer require orba/module-config
bin/magento module:enable Orba_Config
bin/magento setup:upgrade
```
