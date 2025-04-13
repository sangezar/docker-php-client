# Документація класу DockerClient

## Опис
`DockerClient` - це основний клас для взаємодії з Docker API. Він надає зручний інтерфейс для роботи з різними компонентами Docker, такими як контейнери, образи, мережі, томи та системні функції.

## Простір імен
`Sangezar\DockerClient`

## Методи

### Конструктор
```php
public function __construct(
    ?ClientConfig $config = null,
    ?ClientInterface $httpClient = null,
    string $apiVersion = 'v1.47'
)
```

#### Параметри:
- `$config` - (необов'язковий) Об'єкт конфігурації клієнта `ClientConfig`
- `$httpClient` - (необов'язковий) HTTP клієнт, що реалізує інтерфейс `ClientInterface`
- `$apiVersion` - Версія Docker API (за замовчуванням `v1.47`)

### Статичні методи створення

#### create
```php
public static function create(?ClientConfig $config = null): self
```
Створює новий екземпляр клієнта.

##### Параметри:
- `$config` - (необов'язковий) Об'єкт конфігурації клієнта `ClientConfig`

##### Повертає:
- Новий екземпляр `DockerClient`

#### createTcp
```php
public static function createTcp(
    string $host,
    ?string $certPath = null,
    ?string $keyPath = null,
    ?string $caPath = null
): self
```
Створює клієнт для TCP-з'єднання з Docker API.

##### Параметри:
- `$host` - Хост Docker API
- `$certPath` - (необов'язковий) Шлях до сертифіката
- `$keyPath` - (необов'язковий) Шлях до ключа
- `$caPath` - (необов'язковий) Шлях до CA сертифіката

##### Повертає:
- Новий екземпляр `DockerClient` налаштований для TCP-з'єднання

#### createUnix
```php
public static function createUnix(string $socketPath = '/var/run/docker.sock'): self
```
Створює клієнт для з'єднання через Unix-сокет.

##### Параметри:
- `$socketPath` - Шлях до Unix-сокета (за замовчуванням `/var/run/docker.sock`)

##### Повертає:
- Новий екземпляр `DockerClient` налаштований для Unix-сокет з'єднання

### Методи доступу до API

#### container
```php
public function container(): ContainerInterface
```
Повертає API для роботи з контейнерами.

##### Повертає:
- Об'єкт, що реалізує інтерфейс `ContainerInterface`

#### image
```php
public function image(): ImageInterface
```
Повертає API для роботи з образами.

##### Повертає:
- Об'єкт, що реалізує інтерфейс `ImageInterface`

#### system
```php
public function system(): SystemInterface
```
Повертає API для роботи з системними функціями Docker.

##### Повертає:
- Об'єкт, що реалізує інтерфейс `SystemInterface`

#### network
```php
public function network(): NetworkInterface
```
Повертає API для роботи з мережами Docker.

##### Повертає:
- Об'єкт, що реалізує інтерфейс `NetworkInterface`

#### volume
```php
public function volume(): VolumeInterface
```
Повертає API для роботи з томами Docker.

##### Повертає:
- Об'єкт, що реалізує інтерфейс `VolumeInterface`

## Приклади використання

### Підключення через Unix-сокет (найпоширеніший спосіб)
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
```

### Підключення через TCP
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createTcp('tcp://docker-host:2375');
```

### Підключення через TCP з TLS
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createTcp(
    'tcp://docker-host:2376',
    '/path/to/cert.pem',
    '/path/to/key.pem',
    '/path/to/ca.pem'
);
```

### Використання різних API клієнта
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

// Отримання списку контейнерів
$containers = $client->container()->list();

// Отримання списку образів
$images = $client->image()->list();

// Отримання інформації про Docker-систему
$info = $client->system()->info();

// Робота з мережами
$networks = $client->network()->list();

// Робота з томами
$volumes = $client->volume()->list();
``` 