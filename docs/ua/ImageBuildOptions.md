# Клас ImageBuildOptions

`ImageBuildOptions` - це клас для типобезпечного налаштування параметрів збірки Docker-образів. Цей клас дозволяє створювати та валідувати конфігурацію для API збірки образів Docker.

## Призначення

Клас `ImageBuildOptions` вирішує наступні задачі:

- **Типізація параметрів** - забезпечує чітко визначені типи для всіх параметрів збірки
- **Валідація параметрів** - перевіряє коректність значень на етапі їх встановлення
- **Документація** - надає повний опис всіх можливих опцій та їх призначення
- **Гнучкість** - підтримує всі функції Docker API для збірки образів
- **Безпека** - запобігає помилкам при формуванні запитів до Docker API

## Основні можливості

- Підтримка всіх параметрів API Docker для збірки образів
- Можливість встановлювати як шлях до Dockerfile, так і його вміст безпосередньо
- Повна валідація всіх параметрів з детальними повідомленнями про помилки
- Можливість динамічного створення Dockerfile на основі шаблонів або програмної логіки

## Приклади використання

### Базовий приклад

```php
use Sangezar\DockerClient\Config\ImageBuildOptions;
use Sangezar\DockerClient\DockerClient;

$docker = DockerClient::createUnix();

// Базові налаштування для збірки образу
$options = ImageBuildOptions::create()
    ->setTag('myapp:latest')
    ->setContext('./app')
    ->setDockerfilePath('Dockerfile')
    ->setNoCache(true);

// Збірка образу
$result = $docker->image()->buildWithOptions($options);
```

### Використання власного вмісту Dockerfile

```php
$dockerfileContent = <<<DOCKERFILE
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
COPY . /var/www/html/
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 9000
CMD ["php-fpm"]
DOCKERFILE;

$options = ImageBuildOptions::create()
    ->setTag('myphpapp:1.0')
    ->setContext('./app')
    ->setDockerfileContent($dockerfileContent)
    ->addBuildArg('PHP_VERSION', '8.2')
    ->addLabel('maintainer', 'devops@example.com');

$result = $docker->image()->buildWithOptions($options);
```

### Розширений приклад з багатьма параметрами

```php
$options = ImageBuildOptions::create()
    ->setTag('myproject/webapp:2.0')
    ->setContext('./project')
    ->setDockerfilePath('docker/Dockerfile.production')
    ->setNoCache(true)
    ->setQuiet(false)
    ->setRemoveIntermediateContainers(true)
    ->setForceRemoveIntermediateContainers(false)
    ->setPlatform('linux/amd64')
    ->addBuildArg('NODE_ENV', 'production')
    ->addBuildArg('APP_VERSION', '2.0.0')
    ->addLabel('org.opencontainers.image.source', 'https://github.com/example/project')
    ->addLabel('org.opencontainers.image.created', date('c'))
    ->setTarget('production')
    ->setSquash(true)
    ->setPullPolicy(ImageBuildOptions::PULL_ALWAYS)
    ->setNetwork('host')
    ->addExtraHost('db.local', '172.17.0.2')
    ->addCacheFrom('myproject/webapp:1.0')
    ->addSecret('npmrc', '/path/to/.npmrc')
    ->addSshSource('default', '/path/to/ssh-agent.sock');

$result = $docker->image()->buildWithOptions($options);
```

### Використання в кластері Docker

```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\Cluster\Operations\ImageOperations;
use Sangezar\DockerClient\Config\ImageBuildOptions;

// Ініціалізація кластера
$cluster = new DockerCluster();
$cluster->addNode('node1', DockerClient::createUnix());
$cluster->addNode('node2', new DockerClient($remoteConfig));

// Створення операцій для образів
$imageOps = new ImageOperations($cluster->getNodes());

// Налаштування збірки
$options = ImageBuildOptions::create()
    ->setTag('myservice:1.0')
    ->setContext('./service')
    ->setDockerfilePath('Dockerfile')
    ->addBuildArg('VERSION', '1.0.0');

// Збірка образу на всіх вузлах кластера
$results = $imageOps->buildWithOptions($options);
```

## Доступні методи

| Метод | Опис |
|-------|------|
| `setTag(string $tag)` | Встановлює ім'я та тег образу |
| `setContext(string $contextPath)` | Встановлює шлях до контексту збірки |
| `setDockerfilePath(string $path)` | Встановлює шлях до Dockerfile в контексті |
| `setDockerfileContent(string $content)` | Встановлює вміст Dockerfile безпосередньо |
| `setNoCache(bool $noCache)` | Вимикає використання кешу при збірці |
| `setQuiet(bool $quiet)` | Вимикає детальний вивід інформації про збірку |
| `setRemoveIntermediateContainers(bool $remove)` | Вмикає видалення проміжних контейнерів |
| `setForceRemoveIntermediateContainers(bool $force)` | Вмикає примусове видалення проміжних контейнерів |
| `addBuildArg(string $name, string $value)` | Додає аргумент збірки |
| `addLabel(string $name, string $value)` | Додає мітку до образу |
| `setTarget(string $target)` | Встановлює цільову стадію багатоетапного Dockerfile |
| `setPlatform(string $platform)` | Встановлює платформу для збірки (os/arch) |
| `addExtraHost(string $hostname, string $ip)` | Додає запис до /etc/hosts в контейнері |
| `setSquash(bool $squash)` | Вмикає об'єднання шарів образу |
| `setNetwork(string $network)` | Встановлює мережу для контейнерів збірки |
| `addSecret(string $id, string $source)` | Додає секрет для збірки |
| `addSshSource(string $id, string $source)` | Додає SSH-джерело для збірки |
| `addExtraContext(string $name, string $source)` | Додає додатковий контекст збірки |
| `setPullPolicy(string $pull)` | Встановлює стратегію витягування базових образів |
| `addCacheFrom(string $image)` | Додає образ для використання як кеш |
| `setOutputType(string $outputType)` | Встановлює тип виводу збірки |
| `toArrays()` | Конвертує налаштування в масиви для API Docker |

## Обробка помилок

Клас використовує систему винятків для повідомлення про помилки:

- `InvalidParameterValueException` - невалідне значення параметра
- `InvalidConfigurationException` - невалідна конфігурація (наприклад, відсутні обов'язкові параметри)

Приклад:

```php
try {
    $options = ImageBuildOptions::create()
        ->setTag('myapp:latest')
        ->setContext('./app')
        ->setPlatform('invalid-platform'); // Викличе виняток
} catch (InvalidParameterValueException $e) {
    echo "Помилка параметра: " . $e->getMessage();
}
```

## Інтеграція з Docker API

Клас `ImageBuildOptions` інтегрується з API Docker через методи:

- `Image::buildWithOptions()` для одиночного клієнта Docker
- `ImageOperations::buildWithOptions()` для операцій в кластері

Ці методи приймають об'єкт `ImageBuildOptions` і використовують його для формування правильних запитів до API Docker.