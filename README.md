# Orba Config

This module allows to manage Magento configuration (core_config_data) using csv files. It is meant as a replacement for native magento system including config.php, environment variables and commands `app:config:dump` and `app:config:import` due to their limitations.

### Basic Usage

1. Create csv file with configuration:

    |path|scope|code|value|state
    |---|---|---|---|---|
    |msp_securitysuite_recaptcha/backend/enabled|stores|italy|1|always

2. Keep it anywhere you want, preferably in git repository of your Magento project.

3. In your CI/CD process, use this command to import the file automatically:

    ``` bin/magento orba:config --files myConfiguration.csv ```
    
The command clears all caches automatically, so changes should be immediately visible.

### Csv format

CSV requirements:
- value separator: comma
- encoding: UTF-8 without BOM
- header row with column names is required
- the following columns are required: path, scope, code, value (or `value:<env>`, see below), state

Be careful about saving the file in Windows. File may be saved with semicolon as value separator and with Windows encoding due to user preferences.

### Scope and code

Possible scopes are: default, websites and stores. If you leave the scope field empty, default scope is used.

Scope code needs to be:
- website code - for scope websites
- store code - for scope stores
- <empty_string> - for scope default

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default| |1|always
|msp_securitysuite_recaptcha/backend/enabled|websites|b2c|1|always
|msp_securitysuite_recaptcha/backend/enabled|stores|italy|1|always

### State

State determines the import behaviour. Possible states:

- always
- absent
- ignored
- init
- once

Support for additional states may be added using di.xml.

#### Always

Import the config from csv to db (update if it exists in db, insert if it does not).

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
|msp_securitysuite_recaptcha/general/private_key|default| |{{env RECAPTCHA_KEY}}|always

RECAPTCHA_KEY=passAbc123 bin/magento orba:config --files myConfiguration.csv

Notice that this may be any environment variable. It needs not (and should not) be environment variable in Magento format, e.g. CONFIG__DEFAULT__CONTACT__EMAIL__RECIPIENT_EMAIL.

Notice that Magento allows you to set configuration using environment variables by default, e.g. CONFIG__DEFAULT__CONTACT__EMAIL__RECIPIENT_EMAIL="contact@example.com". However, native system makes it impossible to override value from environment in admin panel, whereas this tool allows this.

#### FILE

FILE expression allows you to set secret or environment-specific configuration using an external file.

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/general/private_key|default| |{{file .recaptcha_key}}|always

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
|msp_securitysuite_recaptcha/backend/enabled|default| |{{null}}|always

### Environment-specific values

It is possible to define different values for different environments in the following way:

|path|scope|code|value|value:dev|value:prod|state
|---|---|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default| |0|1|2|always

Based on parameters passed to orba:config command, the value saved in database will be as follows:

|command|value|
|---|---|
|orba:config configuration.csv|0|
|orba:config --env=dev configuration.csv|1|
|orba:config --env=prod configuration.csv|2|

Watchout: If you specify --env=dev, but value:dev is empty, the installer will not use default value. Empty value will be saved.

E.g.

When you run ```orba:config --env=dev configuration.csv``` with the following file:

|path|scope|code|value|value:dev|state
|---|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default| |1| |always

The value saved in database will be empty string, not "1".

### Different environments

If you need different configuration for production, test and dev environments, use one or a combination of the following mechanisms:

1. CLI parameter --env + multiple environment-specific values in common.csv file (described in another section). In CI/CD process, make sure the command is run with different --env argument based on environment.

2. Multiple files, e.g.:

    ```
    /common.csv
    /prod.csv
    /uat.csv
    /dev.csv
    ```
    
    The orba:config command allows you to specify multiple files - in such case, they are merged. In CI/CD process, make sure the command is run with different arguments based on environment:
    - prod: orba:config --files common.csv prod.csv
    - uat: orba:config --files common.csv uat.csv
    - dev: orba:config --files common.csv dev.csv

3. You may also define all configuration in common.csv, but load values from environment using "ENV" expression.

### Csv - database discrepancies

If you make changes in your configuration.csv file, make sure you mark the config as "absent" instead of removing it from csv file. If you just remove configuration from file, next import will leave database value intact and there will be a discrepancy between file and database.

Also, for the same reason, if you need to change configuration scope, do not just edit scope in the existing config entry. Instead, add new entry for new scope and mark the old scope as "absent".

Example: this is your configuration file before changes:

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|websites|b2c|1|always

This is how your configuration file should look like after changing scope:

|path|scope|code|value|state
|---|---|---|---|---|
|msp_securitysuite_recaptcha/backend/enabled|default| |1|always
|msp_securitysuite_recaptcha/backend/enabled|websites|b2c|1|absent

Notice that this config may have a stores scope value in database, set manually by admin user. This import tool will not remove this value. Consequently, it may appear that the import fails to import new value, because store value will obscure website or default value imported from csv file.

### Command

bin/magento orba:config --files file1.csv file2.csv [--env=dev] [--dry-run] [-v]

If you specify several files, they will be merged. Final value is taken from the last file in which the configuration appears, in this case: from file2.csv.

### Command Output

Command exits with code 0 in case of success and with code greater than 0 in case of error.

In case of success, the command prints summary of changes, e.g.

```
Added: 1
Updated: 0
Updated Hash: 0
Removed: 0
Ignored: 0
Total: 1
```

With increased verbosity (```bin/magento orba:config -v```), the command prints additionally the list of configs per operation, e.g.:

```
Added:
analytics/subscription/enabled  stores  italy

Updated:
sales/msrp/enabled default

Updated Hash:
sales/msrp/enabled websites b2c
```

Updated Hash means config in csv had the same value as config in database, but imported_value_hash was missing in database, so update was necessary.

Imported value hash is used to mark the config as imported using Orba_Config import.

You may run the command with --dry-run and increased verbosity to check how the import will affect database.

## Installation

```
composer require orba/module-config
bin/magento module:enable Orba_Config
bin/magento setup:upgrade
```
