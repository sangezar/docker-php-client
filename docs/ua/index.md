# 🐳 Документація Docker PHP Client

## 📖 Про проект

Docker PHP Client — це сучасна, об'єктно-орієнтована бібліотека для взаємодії з Docker API через PHP. Бібліотека надає зручний та типобезпечний інтерфейс для роботи з контейнерами Docker, образами, мережами, томами та системними функціями.

## 📚 Зміст документації

### Основні класи

- [DockerClient](DockerClient.md) - Головний клас для взаємодії з Docker API
- [System](System.md) - Робота з системними функціями Docker
- [Container](Container.md) - Управління контейнерами
- [Image](Image.md) - Робота з образами
- [Network](Network.md) - Управління мережами
- [Volume](Volume.md) - Управління томами

### Конфігурації

- [ContainerConfig](ContainerConfig.md) - Конфігурація контейнерів
- [NetworkConfig](NetworkConfig.md) - Конфігурація мереж
- [VolumeConfig](VolumeConfig.md) - Конфігурація томів
- [ImageBuildOptions](ImageBuildOptions.md) - Опції збірки образів
- [ClusterConfig](ClusterConfig.md) - Конфігурація кластера

### Кластер Docker

- [DockerCluster](DockerCluster.md) - Управління кластером Docker
- [NodeCollection](NodeCollection.md) - Колекція вузлів Docker
- [AbstractOperations](AbstractOperations.md) - Базовий клас для операцій кластера
- [ContainerOperations](ContainerOperations.md) - Операції з контейнерами в кластері
- [ImageOperations](ImageOperations.md) - Операції з образами в кластері
- [NetworkOperations](NetworkOperations.md) - Операції з мережами в кластері

## 🚀 Початок роботи

### Встановлення

```bash
composer require sangezar/docker-php-client
```

### Базовий приклад

```php
use Sangezar\DockerClient\DockerClient;

// Створення клієнта для підключення до локального Docker
$client = DockerClient::createUnix();

// Отримання списку контейнерів
$containers = $client->container()->list(['all' => true]);

// Отримання інформації про систему Docker
$info = $client->system()->info();
```

## 🛠️ Вимоги

- PHP 8.1 або вище
- Docker Engine API v1.47 або вище
- Composer

## 🌐 Вибрати мову

- [🇺🇦 Українська документація](index.md)
- [🇬🇧 English documentation](../en/index.md) 