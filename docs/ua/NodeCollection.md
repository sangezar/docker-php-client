# Документація класу NodeCollection

## Опис
`NodeCollection` - це клас, що представляє колекцію вузлів Docker для виконання операцій у кластері. Він дозволяє виконувати однакові операції на декількох Docker-вузлах одночасно.

## Простір імен
`Sangezar\DockerClient\Cluster`

## Методи

### __construct
```php
public function __construct(array $nodes)
```
Створює нову колекцію вузлів.

#### Параметри:
- `$nodes` - Асоціативний масив вузлів, де ключі - імена вузлів, а значення - екземпляри `DockerClient`

### containers
```php
public function containers(): ContainerOperations
```
Отримує об'єкт для роботи з контейнерами на всіх вузлах колекції.

#### Повертає:
- Екземпляр класу `ContainerOperations` для роботи з контейнерами

### images
```php
public function images(): ImageOperations
```
Отримує об'єкт для роботи з образами на всіх вузлах колекції.

#### Повертає:
- Екземпляр класу `ImageOperations` для роботи з образами

### networks
```php
public function networks(): NetworkOperations
```
Отримує об'єкт для роботи з мережами на всіх вузлах колекції.

#### Повертає:
- Екземпляр класу `NetworkOperations` для роботи з мережами

### volumes
```php
public function volumes(): VolumeOperations
```
Отримує об'єкт для роботи з томами на всіх вузлах колекції.

#### Повертає:
- Екземпляр класу `VolumeOperations` для роботи з томами

### system
```php
public function system(): SystemOperations
```
Отримує об'єкт для роботи з системними функціями на всіх вузлах колекції.

#### Повертає:
- Екземпляр класу `SystemOperations` для роботи з системними функціями

### filter
```php
public function filter(callable $callback): self
```
Фільтрує вузли в колекції за допомогою функції зворотного виклику.

#### Параметри:
- `$callback` - Функція-колбек для фільтрації, яка приймає вузол як аргумент і повертає булеве значення

#### Повертає:
- Нову колекцію вузлів `NodeCollection` з відфільтрованими вузлами

### getNodes
```php
public function getNodes(): array
```
Отримує всі вузли в колекції.

#### Повертає:
- Асоціативний масив вузлів, де ключі - імена вузлів, а значення - екземпляри `DockerClient`

### count
```php
public function count(): int
```
Підраховує кількість вузлів у колекції.

#### Повертає:
- Кількість вузлів у колекції

### isEmpty
```php
public function isEmpty(): bool
```
Перевіряє, чи колекція порожня.

#### Повертає:
- `true`, якщо колекція не містить вузлів, `false` - якщо містить

## Приклади використання

### Створення колекції вузлів та виконання операцій
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\NodeCollection;

// Створення клієнтів Docker для різних вузлів
$node1 = DockerClient::createTcp('tcp://192.168.1.10:2375');
$node2 = DockerClient::createTcp('tcp://192.168.1.11:2375');

// Створення колекції вузлів
$nodes = [
    'node1' => $node1,
    'node2' => $node2,
];
$collection = new NodeCollection($nodes);

// Отримання списку контейнерів на всіх вузлах
$containersMap = $collection->containers()->list(['all' => true]);

// Результат - масив, де ключі - імена вузлів, а значення - масиви контейнерів
foreach ($containersMap as $nodeName => $containers) {
    echo "Вузол: $nodeName\n";
    foreach ($containers as $container) {
        echo "  Контейнер: {$container['Names'][0]}\n";
    }
}
```

### Фільтрація колекції вузлів
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\NodeCollection;

// Створення колекції вузлів
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
    'node3' => DockerClient::createTcp('tcp://192.168.1.12:2375'),
];
$collection = new NodeCollection($nodes);

// Фільтрація вузлів за ім'ям
$filteredCollection = $collection->filter(function ($client, $name) {
    return strpos($name, 'node1') === 0 || strpos($name, 'node2') === 0;
});

// Перевірка кількості вузлів після фільтрації
echo "Кількість вузлів після фільтрації: " . $filteredCollection->count() . "\n";

// Виконання операцій тільки на відфільтрованих вузлах
$imagesMap = $filteredCollection->images()->list();
``` 