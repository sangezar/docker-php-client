# 🐳 Docker PHP Client Documentation

## 📖 About the Project

Docker PHP Client is a modern, object-oriented library for interacting with the Docker API using PHP. The library provides a convenient and type-safe interface for working with Docker containers, images, networks, volumes, and system functions.

## 📚 Documentation Contents

### Core Classes

- [DockerClient](DockerClient.md) - Main class for interacting with the Docker API
- [System](System.md) - Working with Docker system functions
- [Container](Container.md) - Managing containers
- [Image](Image.md) - Working with images
- [Network](Network.md) - Managing networks
- [Volume](Volume.md) - Managing volumes

### Configurations

- [ContainerConfig](ContainerConfig.md) - Container configuration
- [NetworkConfig](NetworkConfig.md) - Network configuration
- [VolumeConfig](VolumeConfig.md) - Volume configuration
- [ImageBuildOptions](ImageBuildOptions.md) - Image build options
- [ClusterConfig](ClusterConfig.md) - Cluster configuration

### Docker Cluster

- [DockerCluster](DockerCluster.md) - Managing Docker clusters
- [NodeCollection](NodeCollection.md) - Collection of Docker nodes
- [AbstractOperations](AbstractOperations.md) - Base class for cluster operations
- [ContainerOperations](ContainerOperations.md) - Container operations in a cluster
- [ImageOperations](ImageOperations.md) - Image operations in a cluster
- [NetworkOperations](NetworkOperations.md) - Network operations in a cluster

## 🚀 Getting Started

### Installation

```bash
composer require sangezar/docker-php-client
```

### Basic Example

```php
use Sangezar\DockerClient\DockerClient;

// Create a client connecting to local Docker daemon
$client = DockerClient::createUnix();

// List all containers
$containers = $client->container()->list(['all' => true]);

// Get Docker system information
$info = $client->system()->info();
```

## 🛠️ Requirements

- PHP 8.1 or higher
- Docker Engine API v1.47 or higher
- Composer

## 🌐 Choose Language

- [🇺🇦 Українська документація](../ua/index.md)
- [🇬🇧 English documentation](index.md) 