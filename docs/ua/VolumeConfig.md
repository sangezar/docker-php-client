# VolumeConfig

Клас `VolumeConfig` є частиною простору імен `Sangezar\DockerClient\Config` і надає зручний інтерфейс для налаштування та створення томів Docker.

## Простір імен

```php
namespace Sangezar\DockerClient\Config;
```

## Опис

Клас `VolumeConfig` дозволяє створювати та налаштовувати конфігурації томів Docker, які можуть бути використані API клієнтом для створення нових томів. 

## Створення екземпляру

```php
$volumeConfig = VolumeConfig::create();
```

## Методи

### create(): self

Статичний метод для створення нового екземпляра конфігурації тому.

```php
$volumeConfig = VolumeConfig::create();
```

### setName(string $name): self

Встановлює ім'я тому.

**Параметри:**
- `$name` - Ім'я тому

**Винятки:**
- `InvalidParameterValueException` - якщо ім'я тому порожнє або недійсне.

```php
$volumeConfig->setName('my-volume');
```

### setDriver(string $driver): self

Встановлює драйвер тому.

**Параметри:**
- `$driver` - Драйвер тому (наприклад, 'local', 'nfs', 'cifs' тощо)

**Винятки:**
- `InvalidParameterValueException` - якщо параметр драйвера порожній.

```php
$volumeConfig->setDriver('local');
```

### addDriverOpt(string $key, string $value): self

Додає опцію драйвера.

**Параметри:**
- `$key` - Ключ опції
- `$value` - Значення опції

```php
$volumeConfig->addDriverOpt('type', 'nfs');
$volumeConfig->addDriverOpt('device', ':/path/to/dir');
$volumeConfig->addDriverOpt('o', 'addr=192.168.1.1,rw');
```

### setupNfs(string $serverAddress, string $serverPath, string $options = 'addr={server},rw'): self

Налаштовує NFS том. Автоматично встановлює драйвер на 'local' і додає відповідні опції.

**Параметри:**
- `$serverAddress` - IP-адреса або хост сервера NFS
- `$serverPath` - Шлях на сервері NFS для монтування
- `$options` - Опції монтування, які будуть додані до рядка параметрів

**Винятки:**
- `InvalidParameterValueException` - якщо порожня адреса сервера або шлях.

```php
$volumeConfig->setupNfs('192.168.1.100', '/exports/data');
```

### addLabel(string $key, string $value): self

Додає мітку до тому.

**Параметри:**
- `$key` - Ключ мітки
- `$value` - Значення мітки

```php
$volumeConfig->addLabel('environment', 'production');
$volumeConfig->addLabel('backup', 'weekly');
```

### toArray(): array

Перетворює конфігурацію у масив для API Docker.

**Винятки:**
- `InvalidConfigurationException` - якщо конфігурація недійсна.

**Повертає:**
- `array<string, mixed>` - Масив конфігурації для API Docker.

```php
$configArray = $volumeConfig->toArray();
```

## Приклад використання

### Базовий локальний том

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Створення конфігурації тому
$volumeConfig = VolumeConfig::create()
    ->setName('app-data')
    ->setDriver('local')
    ->addLabel('application', 'my-app')
    ->addLabel('environment', 'development');

// Створення тому за допомогою API клієнта
$dockerClient->volumes()->create($volumeConfig);
```

### Налаштування NFS тому

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Створення NFS тому
$volumeConfig = VolumeConfig::create()
    ->setName('shared-data')
    ->setupNfs('192.168.1.100', '/exports/shared', 'addr={server},rw,nolock,soft')
    ->addLabel('type', 'shared-storage');

// Створення тому за допомогою API клієнта
$dockerClient->volumes()->create($volumeConfig);
```

### Розширені опції

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Створення тому з розширеними опціями
$volumeConfig = VolumeConfig::create()
    ->setName('database-data')
    ->setDriver('local')
    ->addDriverOpt('type', 'tmpfs')
    ->addDriverOpt('device', 'tmpfs')
    ->addDriverOpt('o', 'size=100m,uid=1000')
    ->addLabel('service', 'database')
    ->addLabel('backup', 'hourly');

// Перетворення на масив для API Docker
$configArray = $volumeConfig->toArray();

// Створення тому за допомогою API клієнта
$dockerClient->volumes()->create($volumeConfig);
``` 