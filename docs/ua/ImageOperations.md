# Документація класу ImageOperations

## Опис
`ImageOperations` - це клас, що забезпечує операції з образами Docker на всіх вузлах кластера одночасно. Клас дозволяє отримувати список образів, будувати, створювати (витягувати), інспектувати, відправляти в реєстр, тегувати та видаляти образи Docker на всіх вузлах кластера.

## Простір імен
`Sangezar\DockerClient\Cluster\Operations`

## Успадкування
Клас розширює `AbstractOperations` і успадковує всі його методи та властивості.

## Методи

### list
```php
public function list(array $parameters = []): array
```
Отримує список образів з усіх вузлів кластера.

#### Параметри:
- `$parameters` - Масив параметрів фільтрації:
  - `all` (bool): Показати всі образи (за замовчуванням false)
  - `filters` (array): Фільтри для пошуку образів

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `InvalidParameterValueException` - якщо надані неприпустимі параметри фільтрації

### build
```php
public function build(array $parameters = [], array $config = []): array
```
Будує образ на всіх вузлах кластера.

#### Параметри:
- `$parameters` - Параметри збірки:
  - `t` або `tag` (string): Тег образу (обов'язково)
  - Інші параметри Docker API для збірки
- `$config` - Додаткова конфігурація:
  - `context` (string): Шлях до контексту збірки

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `InvalidParameterValueException` - якщо надані неприпустимі параметри
- `InvalidConfigurationException` - якщо конфігурація недійсна
- `MissingRequiredParameterException` - якщо відсутній обов'язковий параметр тега

### buildWithOptions
```php
public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array
```
Будує образ на всіх вузлах кластера, використовуючи об'єкт параметрів збірки.

#### Параметри:
- `$options` - Об'єкт параметрів збірки образу

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `InvalidParameterValueException` - якщо надані неприпустимі параметри
- `InvalidConfigurationException` - якщо конфігурація недійсна

### create
```php
public function create(string $fromImage, ?string $tag = null): array
```
Створює образ, витягуючи його з реєстру, на всіх вузлах кластера.

#### Параметри:
- `$fromImage` - Ім'я образу для витягування
- `$tag` - Тег образу (за замовчуванням "latest")

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє
- `InvalidParameterValueException` - якщо надані неприпустимі параметри

### inspect
```php
public function inspect(string $name): array
```
Отримує детальну інформацію про образ на всіх вузлах кластера.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### history
```php
public function history(string $name): array
```
Отримує історію образу на всіх вузлах кластера.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### push
```php
public function push(string $name, array $parameters = []): array
```
Відправляє образ у реєстр з усіх вузлів кластера.

#### Параметри:
- `$name` - Ім'я образу для відправки
- `$parameters` - Додаткові параметри

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### tag
```php
public function tag(string $name, string $repo, ?string $tag = null): array
```
Тегує образ на всіх вузлах кластера.

#### Параметри:
- `$name` - Ім'я або ID вихідного образу
- `$repo` - Репозиторій для нового тега
- `$tag` - Новий тег (якщо null, використовується "latest")

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу або репозиторію порожнє
- `InvalidParameterValueException` - якщо надані неприпустимі параметри

### remove
```php
public function remove(string $name, bool $force = false, bool $noprune = false): array
```
Видаляє образ на всіх вузлах кластера.

#### Параметри:
- `$name` - Ім'я або ID образу
- `$force` - Примусово видалити образ (за замовчуванням false)
- `$noprune` - Не видаляти проміжні образи (за замовчуванням false)

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### search
```php
public function search(string $term): array
```
Шукає образи у Docker Hub зі всіх вузлів кластера.

#### Параметри:
- `$term` - Термін пошуку

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо термін пошуку порожній

### prune
```php
public function prune(array $parameters = []): array
```
Видаляє невикористовувані образи на всіх вузлах кластера.

#### Параметри:
- `$parameters` - Параметри для видалення:
  - `filters` (array): Фільтри для вибору образів для видалення

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

### exists
```php
public function exists(string $name): array
```
Перевіряє, чи існує образ на всіх вузлах кластера.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - булеві результати (true, якщо образ існує, false - якщо ні)

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### existsOnAllNodes
```php
public function existsOnAllNodes(string $name): bool
```
Перевіряє, чи існує образ на всіх вузлах кластера.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- `true`, якщо образ існує на всіх вузлах, `false` - якщо хоча б на одному вузлі образ відсутній

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### getNodesWithImage
```php
public function getNodesWithImage(string $name): array
```
Отримує список вузлів, на яких існує образ.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- Масив з іменами вузлів, на яких існує образ

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### pull
```php
public function pull(string $name, array $parameters = []): array
```
Витягує образ з реєстру на всі вузли кластера.

#### Параметри:
- `$name` - Ім'я образу для витягування
- `$parameters` - Додаткові параметри для витягування

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє
- `InvalidParameterValueException` - якщо надані неприпустимі параметри

### load
```php
public function load(string $imageArchive): array
```
Завантажує образ з архіву на всі вузли кластера.

#### Параметри:
- `$imageArchive` - Шлях до архіву образу

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо шлях до архіву порожній
- `FileNotFoundException` - якщо файл архіву не знайдено

### save
```php
public function save($names, string $outputFile): array
```
Зберігає образи в архів з усіх вузлів кластера.

#### Параметри:
- `$names` - Ім'я або масив імен образів для збереження
- `$outputFile` - Шлях до файлу виводу

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу або шлях виводу порожній
- `InvalidParameterValueException` - якщо надані неприпустимі параметри

## Приклади використання

### Отримання списку образів з усіх вузлів
```php
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\DockerClient;

// Створення кластера
$cluster = new DockerCluster();
$cluster->addNode('node1', DockerClient::createTcp('tcp://192.168.1.10:2375'));
$cluster->addNode('node2', DockerClient::createTcp('tcp://192.168.1.11:2375'));

// Отримання списку всіх образів
$images = $cluster->images()->list(['all' => true]);

// Перевірка результатів
foreach ($images as $nodeName => $result) {
    echo "Образи на вузлі $nodeName:\n";
    foreach ($result as $image) {
        echo "  - {$image['RepoTags'][0]} (ID: {$image['Id']})\n";
    }
}
```

### Витягування образу на всі вузли кластера
```php
use Sangezar\DockerClient\Cluster\NodeCollection;
use Sangezar\DockerClient\DockerClient;

// Створення колекції вузлів
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
];
$collection = new NodeCollection($nodes);

// Витягування образу на всі вузли
$results = $collection->images()->pull('nginx:latest');

// Перевірка, чи існує образ на всіх вузлах
$exists = $collection->images()->existsOnAllNodes('nginx:latest');
if ($exists) {
    echo "Образ 'nginx:latest' існує на всіх вузлах\n";
} else {
    // Отримання списку вузлів, де образ існує
    $nodesWithImage = $collection->images()->getNodesWithImage('nginx:latest');
    echo "Образ 'nginx:latest' існує тільки на вузлах: " . implode(', ', $nodesWithImage) . "\n";
}
```

### Збірка та відправка образу
```php
// Збірка образу на всіх вузлах
$buildResults = $collection->images()->build([
    't' => 'myapp:latest',
    'dockerfile' => 'Dockerfile',
], [
    'context' => '/path/to/build/context',
]);

// Тегування образу для відправки
$collection->images()->tag('myapp:latest', 'registry.example.com/myapp', 'latest');

// Відправка образу в реєстр
$pushResults = $collection->images()->push('registry.example.com/myapp:latest');

// Видалення локального образу
$collection->images()->remove('myapp:latest', true);
``` 