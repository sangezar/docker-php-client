# NetworkConfig

Клас `NetworkConfig` є частиною простору імен `Sangezar\DockerClient\Config` і надає зручний інтерфейс для налаштування Docker мереж.

## Простір імен

```php
namespace Sangezar\DockerClient\Config;
```

## Опис

Клас `NetworkConfig` дозволяє створювати та налаштовувати конфігурації мереж Docker, які можуть бути використані API клієнтом для створення нових мереж. 

## Створення екземпляру

```php
$networkConfig = NetworkConfig::create();
```

## Методи

### create(): self

Статичний метод для створення нового екземпляра конфігурації мережі.

```php
$networkConfig = NetworkConfig::create();
```

### setName(string $name): self

Встановлює ім'я мережі.

**Параметри:**
- `$name` - Ім'я мережі

**Винятки:**
- `InvalidParameterValueException` - якщо ім'я мережі порожнє або недійсне.

```php
$networkConfig->setName('my-network');
```

### setDriver(string $driver): self

Встановлює драйвер мережі.

**Параметри:**
- `$driver` - Драйвер мережі. Допустимі значення:
  - `bridge` - стандартний міст
  - `host` - використовує мережевий стек хоста
  - `overlay` - оверлейна мережа
  - `macvlan` - мережа MAC VLAN
  - `ipvlan` - мережа IP VLAN
  - `none` - без мережі

**Винятки:**
- `InvalidParameterValueException` - якщо драйвер невідомий.

```php
$networkConfig->setDriver('bridge');
```

### setEnableIPv6(bool $enable = true): self

Вмикає або вимикає підтримку IPv6.

**Параметри:**
- `$enable` - `true` для ввімкнення IPv6, `false` для вимкнення.

```php
$networkConfig->setEnableIPv6(true);
```

### setInternal(bool $internal = true): self

Встановлює, чи повинна мережа бути внутрішньою.

**Параметри:**
- `$internal` - `true` для внутрішньої мережі, `false` для зовнішньої.

```php
$networkConfig->setInternal(true);
```

### setAttachable(bool $attachable = true): self

Встановлює, чи можуть контейнери бути підключені до мережі.

**Параметри:**
- `$attachable` - `true`, якщо контейнери можуть приєднуватися, інакше `false`.

```php
$networkConfig->setAttachable(true);
```

### setScope(string $scope): self

Встановлює область видимості мережі.

**Параметри:**
- `$scope` - Область видимості мережі. Допустимі значення:
  - `local` - локальна мережа
  - `swarm` - мережа рою
  - `global` - глобальна мережа

**Винятки:**
- `InvalidParameterValueException` - якщо область видимості невідома.

```php
$networkConfig->setScope('local');
```

### addSubnet(string $subnet, ?string $gateway = null, ?string $ipRange = null): self

Додає підмережу до конфігурації IPAM.

**Параметри:**
- `$subnet` - Підмережа у форматі CIDR (наприклад, 192.168.0.0/24)
- `$gateway` - IP-адреса шлюзу (опціонально)
- `$ipRange` - Діапазон IP у форматі CIDR (опціонально)

**Винятки:**
- `InvalidParameterValueException` - якщо параметри недійсні.

```php
$networkConfig->addSubnet('192.168.1.0/24', '192.168.1.1');
```

### setIpamDriver(string $driver): self

Встановлює драйвер IPAM.

**Параметри:**
- `$driver` - Драйвер IPAM. Допустимі значення:
  - `default` - стандартний драйвер
  - `null` - нульовий драйвер

**Винятки:**
- `InvalidParameterValueException` - якщо драйвер невідомий.

```php
$networkConfig->setIpamDriver('default');
```

### addOption(string $key, string $value): self

Додає опцію драйвера.

**Параметри:**
- `$key` - Ключ опції
- `$value` - Значення опції

```php
$networkConfig->addOption('com.docker.network.bridge.name', 'my-bridge');
```

### addLabel(string $key, string $value): self

Додає мітку до мережі.

**Параметри:**
- `$key` - Ключ мітки
- `$value` - Значення мітки

```php
$networkConfig->addLabel('com.example.description', 'Мережа для веб-додатків');
```

### toArray(): array

Перетворює конфігурацію у масив для API Docker.

**Винятки:**
- `InvalidConfigurationException` - якщо конфігурація недійсна.

**Повертає:**
- `array<string, mixed>` - Масив конфігурації для API Docker.

```php
$configArray = $networkConfig->toArray();
```

## Приклад використання

```php
use Sangezar\DockerClient\Config\NetworkConfig;

// Створення конфігурації мережі
$networkConfig = NetworkConfig::create()
    ->setName('my-web-network')
    ->setDriver('bridge')
    ->setEnableIPv6(true)
    ->addSubnet('192.168.0.0/24', '192.168.0.1')
    ->addLabel('environment', 'production')
    ->addOption('com.docker.network.bridge.enable_icc', 'true');

// Перетворення на масив для API Docker
$configArray = $networkConfig->toArray();

// Створення мережі за допомогою API клієнта
$dockerClient->networks()->create($networkConfig);
``` 