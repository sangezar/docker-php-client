# Документація класу Volume

## Опис
`Volume` - це клас для роботи з томами Docker через API. Він надає методи для створення, інспектування, видалення та управління томами Docker.

## Простір імен
`Sangezar\DockerClient\Api`

## Успадкування
Клас `Volume` успадковується від `AbstractApi` та реалізує інтерфейс `VolumeInterface`.

## Константи

### Типи драйверів томів
```php
public const DRIVER_LOCAL = 'local';
public const DRIVER_NFS = 'nfs';
public const DRIVER_TMPFS = 'tmpfs';
public const DRIVER_BTRFS = 'btrfs';
public const DRIVER_VIEUX_BRIDGE = 'vieux-bridge';
public const DRIVER_VFS = 'vfs';
public const DRIVER_CIFS = 'cifs';
```

### Константи для опцій драйверів
```php
public const OPT_TYPE = 'type';
public const OPT_DEVICE = 'device';
public const OPT_O = 'o';
public const OPT_SIZE = 'size';
```

## Методи

### list
```php
public function list(array $filters = []): array
```
Отримує список томів.

#### Параметри:
- `$filters` - Фільтри для застосування у форматі JSON або масиву:
  - `driver` (string) - Фільтрація за драйвером тому
  - `label` (string) - Фільтрація за міткою тому
  - `name` (string) - Фільтрація за іменем тому
  - `dangling` (bool) - Фільтрація томів, які не використовуються жодним контейнером

#### Повертає:
- Масив з інформацією про томи

#### Винятки:
- `InvalidParameterValueException` - якщо передані неприпустимі фільтри

### create
```php
public function create(VolumeConfig $config): array
```
Створює новий том.

#### Параметри:
- `$config` - Об'єкт `VolumeConfig` з конфігурацією тому

#### Повертає:
- Масив з інформацією про створений том

#### Винятки:
- `OperationFailedException` - якщо створення не вдалося

### inspect
```php
public function inspect(string $name): array
```
Отримує детальну інформацію про том.

#### Параметри:
- `$name` - Ім'я тому

#### Повертає:
- Масив з детальною інформацією про том

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я тому порожнє
- `NotFoundException` - якщо том не знайдений

### remove
```php
public function remove(string $name, bool $force = false): bool
```
Видаляє том.

#### Параметри:
- `$name` - Ім'я тому
- `$force` - Примусово видалити том, навіть якщо він використовується (за замовчуванням `false`)

#### Повертає:
- `true`, якщо том успішно видалений

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я тому порожнє
- `NotFoundException` - якщо том не знайдений
- `OperationFailedException` - якщо видалення не вдалося

### prune
```php
public function prune(array $filters = []): array
```
Видаляє невикористані томи.

#### Параметри:
- `$filters` - Фільтри для вибору томів для очищення:
  - `label` (string) - Видалити тільки томи з вказаними мітками
  - `all` (bool) - Видалити всі невикористані томи, а не тільки анонімні

#### Повертає:
- Масив з інформацією про видалені томи, включаючи звільнене місце

#### Винятки:
- `InvalidParameterValueException` - якщо фільтри неприпустимі
- `OperationFailedException` - якщо очищення не вдалося

### exists
```php
public function exists(string $name): bool
```
Перевіряє, чи існує том.

#### Параметри:
- `$name` - Ім'я тому

#### Повертає:
- `true`, якщо том існує, `false` - якщо ні

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я тому порожнє

## Приклади використання

### Отримання списку томів
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$volumes = $client->volume()->list();

foreach ($volumes['Volumes'] as $volume) {
    echo "Том: {$volume['Name']}, драйвер: {$volume['Driver']}\n";
}
```

### Створення нового тому
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Config\VolumeConfig;

$client = DockerClient::createUnix();

$config = new VolumeConfig();
$config->setName('my-volume')
       ->setDriver(Volume::DRIVER_LOCAL)
       ->setLabels([
           'environment' => 'development',
           'application' => 'my-app'
       ]);

$volume = $client->volume()->create($config);
echo "Том створено з іменем: {$volume['Name']}\n";
```

### Інспектування тому
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$volumeName = 'my-volume';

if ($client->volume()->exists($volumeName)) {
    $volumeInfo = $client->volume()->inspect($volumeName);
    echo "Том: {$volumeInfo['Name']}\n";
    echo "Драйвер: {$volumeInfo['Driver']}\n";
    echo "Точка монтування: {$volumeInfo['Mountpoint']}\n";
    
    if (!empty($volumeInfo['Labels'])) {
        echo "Мітки:\n";
        foreach ($volumeInfo['Labels'] as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    }
}
```

### Видалення тому
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$volumeName = 'my-volume';

if ($client->volume()->exists($volumeName)) {
    $client->volume()->remove($volumeName);
    echo "Том {$volumeName} видалено\n";
}
```

### Очищення невикористаних томів
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

$pruneResult = $client->volume()->prune();
echo "Видалено томів: " . count($pruneResult['VolumesDeleted'] ?? []) . "\n";
echo "Звільнено місця: " . ($pruneResult['SpaceReclaimed'] ?? 0) . " байт\n";
``` 