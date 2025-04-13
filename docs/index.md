# 🐳 Docker PHP Client

[![Latest Version](https://img.shields.io/github/release/sangezar/docker-php-client.svg)](https://github.com/sangezar/docker-php-client/releases)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](https://github.com/sangezar/docker-php-client/blob/main/LICENSE)

## 📖 About the Project

Docker PHP Client is a modern, object-oriented library for interacting with the Docker API using PHP. The library provides a convenient and type-safe interface for working with Docker containers, images, networks, volumes, and system functions.

### ✨ Features

- 🔄 Complete support for all Docker API endpoints
- 🚀 Support for clusters with multiple Docker nodes
- 🔐 Secure connection via Unix socket and TCP with TLS
- 🛠️ Type-safe configuration builders for all Docker objects
- 📋 Comprehensive exception handling and error reporting
- 🧪 Thoroughly tested with high test coverage
- 📚 Extensive documentation

## 🌐 Documentation

Choose your language:

- [🇬🇧 English documentation](en/index.md)
- [🇺🇦 Українська документація](ua/index.md)

## 🚀 Quick Start

```php
use Sangezar\DockerClient\DockerClient;

// Create a client connecting to local Docker daemon
$client = DockerClient::createUnix();

// List all containers
$containers = $client->container()->list(['all' => true]);

// Get Docker system information
$info = $client->system()->info();
```

## 📦 Installation

```bash
composer require sangezar/docker-php-client
```

## 🛠️ Requirements

- PHP 8.1 or higher
- Docker Engine API v1.47 or higher
- Composer

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Feel free to submit a pull request.

## 🙏 Acknowledgements

Special thanks to the Docker team for creating an excellent API and documentation. 