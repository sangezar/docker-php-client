<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api\Interface;

use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Інтерфейс для роботи з образами Docker
 */
interface ImageInterface extends ApiInterface
{
    /**
     * Отримує список образів
     *
     * @param array<string, mixed> $parameters Параметри фільтрації
     * @return array<int, array<string, mixed>> Список образів
     * @throws InvalidParameterValueException якщо передані невалідні параметри
     */
    public function list(array $parameters = []): array;

    /**
     * Створює образ з Dockerfile
     *
     * @param array<string, mixed> $parameters Параметри зборки
     * @param array<string, mixed> $config Додаткова конфігурація
     * @return array<string, mixed> Результат операції
     * @throws InvalidParameterValueException якщо передані невалідні параметри
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function build(array $parameters = [], array $config = []): array;

    /**
     * Створює образ з Dockerfile використовуючи об'єкт налаштувань
     *
     * @param \Sangezar\DockerClient\Config\ImageBuildOptions $options Об'єкт налаштувань збірки
     * @return array<string, mixed> Результат операції
     * @throws InvalidParameterValueException якщо передані невалідні параметри
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array;

    /**
     * Завантажує образ з реєстру
     *
     * @param string $fromImage Назва образу для завантаження
     * @param string|null $tag Тег образу
     * @return array<string, mixed> Результат операції
     * @throws MissingRequiredParameterException якщо назва образу порожня
     * @throws InvalidParameterValueException якщо тег невалідний
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function create(string $fromImage, ?string $tag = null): array;

    /**
     * Отримує детальну інформацію про образ
     *
     * @param string $name Назва або ID образу
     * @return array<string, mixed> Детальна інформація про образ
     * @throws MissingRequiredParameterException якщо назва образу порожня
     * @throws NotFoundException якщо образ не знайдено
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function inspect(string $name): array;

    /**
     * Отримує історію образу
     *
     * @param string $name Назва або ID образу
     * @return array<int, array<string, mixed>> Історія образу
     * @throws MissingRequiredParameterException якщо назва образу порожня
     * @throws NotFoundException якщо образ не знайдено
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function history(string $name): array;

    /**
     * Відправляє образ у реєстр
     *
     * @param string $name Назва образу
     * @param array<string, mixed> $parameters Додаткові параметри
     * @return array<string, mixed> Результат операції
     * @throws MissingRequiredParameterException якщо назва образу порожня
     * @throws InvalidParameterValueException якщо передані невалідні параметри
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function push(string $name, array $parameters = []): array;

    /**
     * Створює тег для образу
     *
     * @param string $name Назва або ID образу
     * @param string $repo Репозиторій
     * @param string|null $tag Тег
     * @return bool Успішність операції
     * @throws MissingRequiredParameterException якщо назва образу або репозиторій порожні
     * @throws InvalidParameterValueException якщо передані невалідні параметри
     * @throws NotFoundException якщо образ не знайдено
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function tag(string $name, string $repo, ?string $tag = null): bool;

    /**
     * Перевіряє існування образу
     *
     * @param string $name Назва або ID образу
     * @return bool Чи існує образ
     * @throws MissingRequiredParameterException якщо назва образу порожня
     */
    public function exists(string $name): bool;

    /**
     * Видаляє образ
     *
     * @param string $name Назва або ID образу
     * @param bool $force Примусове видалення
     * @param bool $noprune Не видаляти ненатеговані батьківські образи
     * @return bool Успішність операції
     * @throws MissingRequiredParameterException якщо назва образу порожня
     * @throws NotFoundException якщо образ не знайдено
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function remove(string $name, bool $force = false, bool $noprune = false): bool;

    /**
     * Шукає образи в Docker Hub
     *
     * @param string $term Пошуковий запит
     * @return array<int, array<string, mixed>> Результати пошуку
     * @throws MissingRequiredParameterException якщо пошуковий запит порожній
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function search(string $term): array;

    /**
     * Видаляє невикористовувані образи
     *
     * @param array<string, mixed> $filters Фільтри для видалення
     * @return array<string, mixed> Результат операції
     * @throws InvalidParameterValueException якщо передані невалідні параметри
     * @throws OperationFailedException якщо операція не вдалася
     */
    public function prune(array $filters = []): array;
}
