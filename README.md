# Laravel Zoho API V3 Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/asciisd/zoho-v3.svg?style=flat-square)](https://packagist.org/packages/asciisd/zoho-v3)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/asciisd/zoho-v3/Check%20&%20fix%20styling?label=code%20style)](https://github.com/asciisd/zoho-v3/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/asciisd/zoho-v3.svg?style=flat-square)](https://packagist.org/packages/asciisd/zoho-v3)

This package used to integrate with the new Zoho V3 Api CRM

## Requirements

* PHP >= 8.0
* Laravel >= 8.0

## Registering a Zoho Client

Since Zoho CRM APIs are authenticated with OAuth2 standards, you should register your client app with Zoho. To register
your app:

1. Visit this page [https://api-console.zoho.com/](https://api-console.zoho.com)
2. Click on `ADD CLIENT`.
3. Choose a `Self Client`.
4. Enter `Scope: aaaserver.profile.READ,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL`.
5. Create grant token by providing the necessary scopes, time duration (the duration for which the generated token is
   valid) and Scope Description.
6. Your Client app would have been created and displayed by now.
7. Select the created OAuth client.

## Installation

You can install the package via `composer require`:

```bash
composer require asciisd/zoho-v3
```

You'll need to add the following variables to your .env file. Use the credentials previously obtained registering your
application.

```dotenv
ZOHO_CLIENT_ID="Code from Client Secrit Section"
ZOHO_CLIENT_SECRET="Code from Client Secrit Section"
ZOHO_REDIRECT_URI=https://APP_URL/zoho/oauth2callback
ZOHO_CURRENT_USER_EMAIL=admin@example.com
ZOHO_TOKEN="Code Generated from last step"

# available datacenters (USDataCenter, EUDataCenter, INDataCenter, CNDataCenter, AUDataCenter)
ZOHO_DATACENTER=USDataCenter
ZOHO_SANDBOX=true
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="zoho-v3-config"
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="zoho-v3-migrations"
php artisan migrate
```

### Environments
maybe in some cases you wish to enforce zoho to use one of zoho's environments, so you can go to `AppServiceProvider`
and use `Zoho::useEnvironment()` method

```php
Zoho::useEnvironment(EUDataCenter::DEVELOPER());
```

So that will override config settings.

## Usage

Imagine that you need to get all modules from Zoho system.

```php
use Asciisd\Zoho\ZohoManager;

$response = ZohoManager::make(self::TESTING_MODULE);
$modules  = $response->getAllModules();
```

## Model Can be used like this:-

Available only starting from **v1.1.0**

add `Zohoable` as extended class like this:-

```php
use Asciisd\Zoho\Zohoable;
use Asciisd\Zoho\ZohoManager;

class Invoice extends Zohoable {

    // this is your Zoho module API Name
    protected $zoho_module_name = 'Payments';

    public function searchCriteria(){
        // you should return string of criteria that you want to find current record in crm with.
        //EX:
        return ZohoManager::make('Payments')
                            ->where('PaymentID', $this->payment_id)
                            ->andWhere('Order_ID', $this->order_id)
                            ->getCriteria();
    }

    public function zohoMandatoryFields() {
        // you should return array of mandatory fields to create module from this model
        // EX:
        return ['Base_Currency' => $this->currency];
    }
}
```

so now you can use invoice like this

```php
$invoice = \App\Invoice::find(1);

// to check if has zoho id stored on local database or not
$invoice->hasZohoId();

// to return the stored zoho id
$invoice->zohoId();

// that will search on zoho with provided criteria to find the record and associated your model with returned id if exist
// if you provided an `id` that will be used instead of searching on Zoho
$invoice->createOrUpdateZohoId($id = null);

// you can also send current model to zoho
// that wil use `zohoMandatoryFields` method to Serialize model to zohoObject
// Also you can pass additional fields as array to this method
$invoice->createAsZohoable($options = []);
```

**Note:** To use the Invoice like this, you must have the `invoices` table in your database just like you would for any
Laravel model. This allows you to save data to the database and also be able to link it to the `zohos` table and use all
the functions in `Zohoable`. Use the CRUD functions below if you do not intend to use the Zohoable model this way.

## CRUD Can be used like this:-

#### READ

```php
use Asciisd\Zoho\ZohoManager;

// we can now deal with leads module
$leads = ZohoManager::useModule('Leads');

// OR

$leads = ZohoManager::make('Leads');

// find record by its ID
$lead = $leads->getRecord('3582074000002383003');
```

#### UPDATE

```php
$record = new Record();
$record->setId('3582074000002383003');

// Set value as field
$record->addFieldValue(Leads::FirstName(), 'Updated');
$record->addFieldValue(Leads::LastName(), 'Record');

// Set value as key value
$lead->setKeyValue('Phone', '5555555555552');

// Then call update() method
$response = $leads->update($record);
```

#### CREATE

```php
// create the record into zoho crm then get the created instance data
$response = $leads->create([
    'First_Name' => 'Amr',
    'Last_Name' => 'Emad',
    'Email' => 'test@asciisd.com',
    'Phone' => '012345678910',
]);

```

#### DELETE

```php
// delete record by its id
$lead = $leads->delete('3582074000002383003');

```

#### SEARCH

##### Word

```php
use Asciisd\Zoho\ZohoManager;

$records = ZohoManager::useModule('Leads')->searchRecordsByWord('word to be searched');
$first_record = $records[0];
```

##### Phone

```php
use Asciisd\Zoho\ZohoManager;

$records = ZohoManager::useModule('Leads')->searchRecordsByPhone('12345678910');
$first_record = $records[0];
```

##### Email

```php
use Asciisd\Zoho\ZohoManager;

$records = ZohoManager::make('Leads')->searchRecordsByEmail('nobody@asciisd.com');
$first_record = $records[0];
```

##### Criteria

```php
use Asciisd\Zoho\ZohoManager;

$records = ZohoManager::make('Leads')->searchRecordsByCriteria('(City:equals:NY) and (State:equals:Alden)')->get();
$first_record = $records[0];
```

##### Custom

```php
use Asciisd\Zoho\ZohoManager;

$records = ZohoManager::make('Leads')
                    ->where('City', 'NY')
                    ->andWhere('State','Alden')
                    ->get();

$first_record = $records[0];
```

## International Versions

If you're using zoho.com, you don't have to change anything.

If you're using zoho.eu, add to `.env`:

```
ZOHO_ACCOUNTS_URL=https://accounts.zoho.eu
ZOHO_API_BASE_URL=www.zohoapis.eu
```

If you're using zoho.com.cn, add to `.env`:

```
ZOHO_ACCOUNTS_URL=https://accounts.zoho.com.cn
ZOHO_API_BASE_URL=www.zohoapis.com.cn
```

## Token Persistence Method
### File Token Store
By Default this package uses the FileStore method. This will use a local file to persist and cache the created Tokens.
The actual file can be defined in the config as
```
token_persistence_path
```

### Database Token Store
If you want to use a Database to persist the tokens you can change the token_persistence_method in the config file.
It will use the default MySql Database connection of your application and the default TableName ```zoho_oauth_tokens```.

```
'token_persistence_method' => 'db',
```
in the config file (config/zoho.php)

You can change the table name in the config
```
'token_persistence_tablename' => 'zoho_oauth_tokens',
```

#### Database Table migration
Here is a migration you can use to create the table with the expected schema.
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zoho_oauth_tokens', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('user_mail', 255)->nullable(false);
            $table->string('client_id', 255)->nullable();
            $table->string('client_secret', 255)->nullable();
            $table->string('refresh_token', 255)->nullable();
            $table->string('access_token', 255)->nullable();
            $table->string('grant_token', 255)->nullable();
            $table->string('expiry_time', 20)->nullable();
            $table->string('redirect_url', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_oauth_tokens');
    }
};
```


## Support

Contact:<br>
[asciisd.com](https://asciisd.com)<br>
aemad@asciisd.com<br>
+2-010-1144-1444

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/asciisd/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [aemaddin](https://github.com/asciisd)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
