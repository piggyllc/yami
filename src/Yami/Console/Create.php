<?php declare(strict_types=1);

namespace Yami\Console;

use Console\{CommandInterface, Args, Decorate};
use Yami\Config\Bootstrap;
use DateTime;

class Create implements CommandInterface
{

    public function execute(Args $args): void
    {
        $args->setAliases([
            'c' => 'config',
            'e' => 'env',
            'm' => 'migration',
        ]);

        if (!$args->migration) {
            echo Decorate::color(sprintf("Migration name not supplied. Please use parameter --migration=NameOfMigration or -m NameOfMigration.\n\n"), 'red');
            exit(1);
        }

        $args->migration = preg_replace('/[^A-Za-z0-9]/', '', $args->migration);

        if (!preg_match('/^[A-Za-z]{1}/', $args->migration)) {
            echo Decorate::color(sprintf("Migration name is not valid. Please use CamelCase with letters and numbers only. The first character must be a letter.\n\n"), 'red');
            exit(1);
        }

        $filename = (new DateTime())->format('Y_m_d_His') . preg_replace_callback('/[A-Z]/', function($m) {
            return '_' . strtolower($m[0]);
        }, $args->migration);

        $environment = Bootstrap::getEnvironment($args);

        file_put_contents($environment->path . '/' . $filename . '.php', str_replace('{{ClassName}}', $args->migration, file_get_contents(__DIR__ . '/templates/migration.template')));

        echo Decorate::color(sprintf("Created %s\n\n", $environment->path . '/' . $filename . '.php'), 'white');
    }

}