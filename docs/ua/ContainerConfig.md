# Документація класу ContainerConfig

## Опис
`ContainerConfig` - це клас для налаштування конфігурації контейнерів Docker. Він дозволяє встановлювати образ, ім'я, змінні середовища, команди, томи, порти, мітки та інші параметри контейнера.

## Простір імен
`Sangezar\DockerClient\Config`

## Методи

### create
```php
public static function create(): self
```
Створює новий екземпляр конфігурації контейнера.

#### Повертає:
- Новий екземпляр `ContainerConfig`

### setImage
```php
public function setImage(string $image): self
```
Встановлює образ для контейнера.

#### Параметри:
- `$image` - Назва образу (з тегом)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо назва образу порожня

### setName
```php
public function setName(string $name): self
```
Встановлює ім'я для контейнера.

#### Параметри:
- `$name` - Ім'я контейнера

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо ім'я неприпустиме

### addEnv
```php
public function addEnv(string $name, string $value): self
```
Додає змінну середовища.

#### Параметри:
- `$name` - Ім'я змінної
- `$value` - Значення змінної

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setCmd
```php
public function setCmd(array $cmd): self
```
Встановлює команду для запуску.

#### Параметри:
- `$cmd` - Команда у вигляді масиву аргументів

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### addVolume
```php
public function addVolume(string $hostPath, string $containerPath, string $mode = 'rw'): self
```
Додає том.

#### Параметри:
- `$hostPath` - Шлях на хості
- `$containerPath` - Шлях у контейнері
- `$mode` - Режим доступу ('ro', 'rw')

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### addPort
```php
public function addPort(int $hostPort, int $containerPort, string $protocol = 'tcp'): self
```
Додає відображення порту.

#### Параметри:
- `$hostPort` - Порт на хості
- `$containerPort` - Порт у контейнері
- `$protocol` - Протокол ('tcp', 'udp')

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### addLabel
```php
public function addLabel(string $name, string $value): self
```
Додає мітку.

#### Параметри:
- `$name` - Ім'я мітки
- `$value` - Значення мітки

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### toArray
```php
public function toArray(): array
```
Перетворює конфігурацію в масив для Docker API.

#### Повертає:
- Масив з конфігурацією для Docker API

#### Винятки:
- `InvalidConfigurationException` - якщо конфігурація неприпустима

### setWorkingDir
```php
public function setWorkingDir(string $dir): self
```
Встановлює робочий каталог для контейнера.

#### Параметри:
- `$dir` - Шлях до робочого каталогу

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setUser
```php
public function setUser(string $user): self
```
Встановлює користувача для контейнера.

#### Параметри:
- `$user` - Ім'я користувача або UID

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setNetworkMode
```php
public function setNetworkMode(string $mode): self
```
Встановлює режим мережі для контейнера.

#### Параметри:
- `$mode` - Режим мережі ('bridge', 'host', 'none', 'container:[name|id]')

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### addNetworkConnection
```php
public function addNetworkConnection(string $networkName, array $config = []): self
```
Додає підключення до мережі.

#### Параметри:
- `$networkName` - Ім'я мережі
- `$config` - Конфігурація підключення (аліаси, IPAddress тощо)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setTty
```php
public function setTty(bool $enable = true): self
```
Встановлює опцію TTY.

#### Параметри:
- `$enable` - Увімкнути TTY (за замовчуванням true)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setOpenStdin
```php
public function setOpenStdin(bool $enable = true): self
```
Встановлює опцію відкриття stdin.

#### Параметри:
- `$enable` - Увімкнути відкриття stdin (за замовчуванням true)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setMemoryLimit
```php
public function setMemoryLimit(int $memoryBytes): self
```
Встановлює обмеження пам'яті для контейнера.

#### Параметри:
- `$memoryBytes` - Кількість байт пам'яті

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setCpuShares
```php
public function setCpuShares(int $cpuShares): self
```
Встановлює частку CPU для контейнера.

#### Параметри:
- `$cpuShares` - Частка CPU (відносний вага)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### setRestartPolicy
```php
public function setRestartPolicy(string $policy, int $maxRetryCount = 0): self
```
Встановлює політику перезапуску контейнера.

#### Параметри:
- `$policy` - Політика перезапуску ('no', 'always', 'unless-stopped', 'on-failure')
- `$maxRetryCount` - Максимальна кількість спроб (для 'on-failure')

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо політика неприпустима

## Приклади використання

### Створення базової конфігурації
```php
use Sangezar\DockerClient\Config\ContainerConfig;

// Створення конфігурації контейнера
$config = ContainerConfig::create()
    ->setImage('nginx:latest')
    ->setName('my-nginx')
    ->addEnv('NGINX_HOST', 'example.com')
    ->addEnv('NGINX_PORT', '80')
    ->addPort(8080, 80)
    ->addPort(8443, 443, 'tcp');
```

### Налаштування томів та міток
```php
use Sangezar\DockerClient\Config\ContainerConfig;

$config = ContainerConfig::create()
    ->setImage('php:8.2-fpm')
    ->setName('php-app');

// Додавання томів
$config->addVolume('/local/path/app', '/var/www/app', 'rw')
       ->addVolume('/local/path/config', '/etc/nginx/conf.d', 'ro');

// Додавання міток
$config->addLabel('com.example.environment', 'production')
       ->addLabel('com.example.version', '1.0.0')
       ->addLabel('maintainer', 'team@example.com');
```

### Налаштування ресурсів та мережі
```php
use Sangezar\DockerClient\Config\ContainerConfig;

$config = ContainerConfig::create()
    ->setImage('mysql:8.0')
    ->setName('db-server');

// Налаштування ресурсів
$config->setMemoryLimit(512 * 1024 * 1024) // 512 МБ
       ->setCpuShares(512)
       ->setRestartPolicy('always');

// Налаштування мережі
$config->setNetworkMode('bridge')
       ->addNetworkConnection('app-network', [
           'Aliases' => ['database', 'mysql'],
           'IPAddress' => '172.18.0.10'
       ]);
```

### Налаштування користувача та робочого каталогу
```php
use Sangezar\DockerClient\Config\ContainerConfig;

$config = ContainerConfig::create()
    ->setImage('node:18')
    ->setName('node-app')
    ->setUser('node')
    ->setWorkingDir('/app')
    ->setCmd(['npm', 'start'])
    ->setTty(true)
    ->setOpenStdin(true);

// Перетворення конфігурації в масив для Docker API
$apiConfig = $config->toArray();
```

### Комплексний приклад
```php
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\DockerClient;

// Створення клієнта Docker
$client = DockerClient::createUnix();

// Створення конфігурації контейнера
$config = ContainerConfig::create()
    ->setImage('wordpress:latest')
    ->setName('my-wordpress')
    ->addEnv('WORDPRESS_DB_HOST', 'db-server')
    ->addEnv('WORDPRESS_DB_USER', 'wordpress')
    ->addEnv('WORDPRESS_DB_PASSWORD', 'secret')
    ->addPort(8000, 80)
    ->addVolume('/local/path/wordpress', '/var/www/html', 'rw')
    ->setRestartPolicy('unless-stopped')
    ->setNetworkMode('bridge')
    ->addNetworkConnection('wordpress-network');

// Створення контейнера
$container = $client->container()->create($config);
echo "Контейнер створено з ID: " . $container['Id'] . "\n";

// Запуск контейнера
$client->container()->start($container['Id']);
echo "Контейнер запущено\n";
``` 