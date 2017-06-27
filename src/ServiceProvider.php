<?php
namespace DreamFactory\Core\TestDb;

use DreamFactory\Core\Components\ServiceDocBuilder;
use DreamFactory\Core\Components\DbSchemaExtensions;
use DreamFactory\Core\Enums\ServiceTypeGroups;
use DreamFactory\Core\Models\SystemTableModelMapper;
use DreamFactory\Core\Services\ServiceManager;
use DreamFactory\Core\Services\ServiceType;
use DreamFactory\Core\SqlDb\Database\Schema\MySqlSchema;
use DreamFactory\Core\SqlDb\Database\Schema\PostgresSchema;
use DreamFactory\Core\SqlDb\Database\Schema\SqliteSchema;
use DreamFactory\Core\SqlDb\Models\MySqlDbConfig;
use DreamFactory\Core\SqlDb\Models\PgSqlDbConfig;
use DreamFactory\Core\SqlDb\Models\SqlDbConfig;
use DreamFactory\Core\SqlDb\Models\SqliteDbConfig;
use DreamFactory\Core\SqlDb\Services\MySqlDb;
use DreamFactory\Core\SqlDb\Services\PostgreSqlDb;
use DreamFactory\Core\SqlDb\Services\SqliteDb;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    use ServiceDocBuilder;

    public function register()
    {
        // Add our database extensions.
        $this->app->resolving('db.schema', function (DbSchemaExtensions $db){
            $db->extend('sqlite', function ($connection){
                return new SqliteSchema($connection);
            });
            $db->extend('mysql', function ($connection){
                return new MySqlSchema($connection);
            });
            $db->extend('pgsql', function ($connection){
                return new PostgresSchema($connection);
            });
        });

        // Add our service types.
        $this->app->resolving('df.service', function (ServiceManager $df) {
            $df->addType(
                new ServiceType([
                    'name'            => 'test',
                    'label'           => 'MySQL',
                    'description'     => 'test service supporting MySQL connections.',
                    'group'           => ServiceTypeGroups::DATABASE,
                    'config_handler'  => MySqlDbConfig::class,
                    'default_api_doc' => function ($service) {
                        return $this->buildServiceDoc($service->id, MySqlDb::getApiDocInfo($service));
                    },
                    'factory'         => function ($config) {
                        return new MySqlDb($config);
                    },
                ])
            );
        });

        // Add our table model mapping
        $this->app->resolving('df.system.table_model_map', function (SystemTableModelMapper $df) {
            $df->addMapping('sql_db_config', SqlDbConfig::class);
        });
    }

    public function boot()
    {
        // add migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
