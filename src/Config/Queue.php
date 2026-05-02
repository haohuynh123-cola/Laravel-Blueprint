<?php

declare(strict_types=1);

namespace LaravelBlueprint\Config;

enum Queue: string
{
    case Sync = 'sync';
    case Database = 'database';
    case Redis = 'redis';
    case Beanstalkd = 'beanstalkd';
    case Sqs = 'sqs';
    case RabbitMQ = 'rabbitmq';

    public function label(): string
    {
        return match ($this) {
            self::Sync => 'Sync (no queue)',
            self::Database => 'Database',
            self::Redis => 'Redis',
            self::Beanstalkd => 'Beanstalkd',
            self::Sqs => 'AWS SQS',
            self::RabbitMQ => 'RabbitMQ',
        };
    }
}
