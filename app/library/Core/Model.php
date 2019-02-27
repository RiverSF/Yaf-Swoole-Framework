<?php
namespace Library\Core;

use PDO;
use Yaf_Registry;
use PDOException;
use Medoo\Medoo;

class Model extends Medoo
{
    public static $db = null;

    public function __construct($options = null)
    {
        $db = Yaf_Registry::get('config')['mysql']->toArray();
        $options['database_type'] = 'mysql';
        $options['server'] = $db['host'];
        $options['database_name'] = $db['dbname'];
        $options['username'] = $db['username'];
        $options['password'] = $db['password'];
        $options['port'] = $db['port'];
        $options['charset'] = 'utf8';
        $options['logging'] = true;
        $options['option'] = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,            //PDO 开启异常模式
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        //PDO 数据返回格式
        ];

        $this->connectDB($options);
    }

    //重写Medoo 构造方法 实现数据库连接单例
    private function connectDB($options = null)
    {
        if (!is_array($options)) {
            return false;
        }

        if (isset($options['database_type'])) {
            $this->type = strtolower($options['database_type']);
        }

        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        if (isset($options['option'])) {
            $this->option = $options['option'];
        }

        if (isset($options['logging']) && is_bool($options['logging'])) {
            $this->logging = $options['logging'];
        }

        if (isset($options['command']) && is_array($options['command'])) {
            $commands = $options['command'];
        } else {
            $commands = [];
        }

        if (isset($options['dsn'])) {
            if (is_array($options['dsn']) && isset($options['dsn']['driver'])) {
                $attr = $options['dsn'];
            }

            return false;
        } else {
            if (
                isset($options['port']) &&
                is_int($options['port'] * 1)
            ) {
                $port = $options['port'];
            }

            $is_port = isset($port);

            switch ($this->type) {
                case 'mariadb':
                case 'mysql':
                    $attr = [
                        'driver' => 'mysql',
                        'dbname' => $options['database_name']
                    ];

                    if (isset($options['socket'])) {
                        $attr['unix_socket'] = $options['socket'];
                    } else {
                        $attr['host'] = $options['server'];

                        if ($is_port) {
                            $attr['port'] = $port;
                        }
                    }

                    // Make MySQL using standard quoted identifier
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    break;

                case 'pgsql':
                    $attr = [
                        'driver' => 'pgsql',
                        'host' => $options['server'],
                        'dbname' => $options['database_name']
                    ];

                    if ($is_port) {
                        $attr['port'] = $port;
                    }

                    break;

                case 'sybase':
                    $attr = [
                        'driver' => 'dblib',
                        'host' => $options['server'],
                        'dbname' => $options['database_name']
                    ];

                    if ($is_port) {
                        $attr['port'] = $port;
                    }

                    break;

                case 'oracle':
                    $attr = [
                        'driver' => 'oci',
                        'dbname' => $options['server'] ?
                            '//' . $options['server'] . ($is_port ? ':' . $port : ':1521') . '/' . $options['database_name'] :
                            $options['database_name']
                    ];

                    if (isset($options['charset'])) {
                        $attr['charset'] = $options['charset'];
                    }

                    break;

                case 'mssql':
                    if (isset($options['driver']) && $options['driver'] === 'dblib') {
                        $attr = [
                            'driver' => 'dblib',
                            'host' => $options['server'] . ($is_port ? ':' . $port : ''),
                            'dbname' => $options['database_name']
                        ];
                    } else {
                        $attr = [
                            'driver' => 'sqlsrv',
                            'Server' => $options['server'] . ($is_port ? ',' . $port : ''),
                            'Database' => $options['database_name']
                        ];
                    }

                    // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                    $commands[] = 'SET QUOTED_IDENTIFIER ON';

                    // Make ANSI_NULLS is ON for NULL value
                    $commands[] = 'SET ANSI_NULLS ON';
                    break;

                case 'sqlite':
                    $attr = [
                        'driver' => 'sqlite',
                        $options['database_file']
                    ];

                    break;
            }
        }

        $driver = $attr['driver'];

        unset($attr['driver']);

        $stack = [];

        foreach ($attr as $key => $value) {
            $stack[] = is_int($key) ? $value : $key . '=' . $value;
        }

        $dsn = $driver . ':' . implode($stack, ';');

        if (
            in_array($this->type, ['mariadb', 'mysql', 'pgsql', 'sybase', 'mssql']) &&
            isset($options['charset'])
        ) {
            $commands[] = "SET NAMES '" . $options['charset'] . "'";
        }

        try {
            if (is_null(self::$db)) {
                self::$db = $this->pdo = new PDO(
                    $dsn,
                    isset($options['username']) ? $options['username'] : null,
                    isset($options['password']) ? $options['password'] : null,
                    $this->option
                );
            } else {
                $this->pdo = self::$db;
                $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO);
            }

            foreach ($commands as $value) {
                $this->pdo->exec($value);
            }
        } catch (PDOException $e) {
            if(strpos($e->getMessage(), 'MySQL server has gone away')!==false){
                self::$db = $this->pdo = null;
                self::$db = $this->pdo = new PDO(
                    $dsn,
                    isset($options['username']) ? $options['username'] : null,
                    isset($options['password']) ? $options['password'] : null,
                    $this->option
                );
            } else {
                throw new PDOException($e->getMessage());
            }
        }
    }

    public function __destruct()
    {
        \Log::debug('DB',$this->log());
        self::$db = $this->pdo = null;
    }
}