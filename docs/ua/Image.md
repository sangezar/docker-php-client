# Документація класу Image

## Опис
`Image` - це клас для роботи з образами Docker через API. Він надає методи для створення, завантаження, отримання інформації, маркування (тегування) та видалення образів Docker.

## Простір імен
`Sangezar\DockerClient\Api`

## Успадкування
Клас `Image` успадковується від `AbstractApi` та реалізує інтерфейс `ImageInterface`.

## Методи

### list
```php
public function list(array $parameters = []): array
```
Отримує список образів.

#### Параметри:
- `$parameters` - Масив параметрів для фільтрації результатів:
  - `all` (bool) - Показати всі образи (за замовчуванням приховує проміжні образи)
  - `filters` (array|string) - Фільтри для застосування у форматі JSON або масиву
  - `digests` (bool) - Показати інформацію про дайджести

#### Повертає:
- Масив образів

#### Винятки:
- `InvalidParameterValueException` - якщо передані неприпустимі параметри

### build
```php
public function build(array $parameters = [], array $config = []): array
```
Будує новий образ.

#### Параметри:
- `$parameters` - Параметри запиту для побудови:
  - `t` (string) - Ім'я та тег для побудованого образу
  - `q` (bool) - Пригнічувати детальний вивід побудови
  - `nocache` (bool) - Не використовувати кеш при побудові образу
  - `pull` (bool|string) - Завантажити образ перед побудовою
  - `rm` (bool) - Видалити проміжні контейнери після успішної побудови
  - `forcerm` (bool) - Завжди видаляти проміжні контейнери
  - інші параметри
- `$config` - Конфігурація для контексту побудови

#### Повертає:
- Масив з результатом побудови

#### Винятки:
- `InvalidParameterValueException` - якщо передані неприпустимі параметри
- `OperationFailedException` - якщо побудова не вдалася

### buildWithOptions
```php
public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array
```
Будує образ за допомогою об'єкта ImageBuildOptions.

#### Параметри:
- `$options` - Конфігураційні опції для побудови образу

#### Повертає:
- Масив з результатом побудови

#### Винятки:
- `InvalidParameterValueException` - якщо передані неприпустимі параметри
- `OperationFailedException` - якщо побудова не вдалася

### create
```php
public function create(string $fromImage, ?string $tag = null): array
```
Створює образ завантаженням його з реєстру.

#### Параметри:
- `$fromImage` - Ім'я вихідного образу
- `$tag` - Тег для завантаження (за замовчуванням 'latest')

#### Повертає:
- Масив з результатом створення

#### Винятки:
- `MissingRequiredParameterException` - якщо fromImage порожній
- `InvalidParameterValueException` - якщо формат імені образу або тегу неприпустимий
- `OperationFailedException` - якщо завантаження не вдалося

### inspect
```php
public function inspect(string $name): array
```
Отримує детальну інформацію про образ.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- Масив з детальною інформацією про образ

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє
- `NotFoundException` - якщо образ не знайдений

### history
```php
public function history(string $name): array
```
Отримує історію образу.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- Масив з історією образу

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє
- `NotFoundException` - якщо образ не знайдений

### push
```php
public function push(string $name, array $parameters = []): array
```
Відправляє образ до реєстру.

#### Параметри:
- `$name` - Ім'я образу
- `$parameters` - Додаткові параметри:
  - `tag` (string) - Тег для відправки

#### Повертає:
- Масив з результатом відправки

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє
- `OperationFailedException` - якщо відправлення не вдалося

### tag
```php
public function tag(string $name, string $repo, ?string $tag = null): bool
```
Маркує образ новим іменем і тегом.

#### Параметри:
- `$name` - Ім'я або ID образу
- `$repo` - Нове ім'я репозиторію для образу
- `$tag` - Новий тег (за замовчуванням 'latest')

#### Повертає:
- `true`, якщо образ успішно помічений

#### Винятки:
- `MissingRequiredParameterException` - якщо обов'язкові параметри порожні
- `InvalidParameterValueException` - якщо формат репозиторію або тегу неприпустимий
- `OperationFailedException` - якщо маркування не вдалося
- `NotFoundException` - якщо образ не знайдений

### exists
```php
public function exists(string $name): bool
```
Перевіряє, чи існує образ.

#### Параметри:
- `$name` - Ім'я або ID образу

#### Повертає:
- `true`, якщо образ існує, `false` - якщо ні

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє

### remove
```php
public function remove(string $name, bool $force = false, bool $noprune = false): bool
```
Видаляє образ.

#### Параметри:
- `$name` - Ім'я або ID образу
- `$force` - Форсувати видалення (за замовчуванням `false`)
- `$noprune` - Не видаляти невикористані батьківські шари (за замовчуванням `false`)

#### Повертає:
- `true`, якщо образ успішно видалений

#### Винятки:
- `MissingRequiredParameterException` - якщо ім'я образу порожнє
- `OperationFailedException` - якщо видалення не вдалося
- `NotFoundException` - якщо образ не знайдений

### search
```php
public function search(string $term): array
```
Шукає образи в реєстрі Docker Hub.

#### Параметри:
- `$term` - Пошуковий запит

#### Повертає:
- Масив з результатами пошуку

#### Винятки:
- `MissingRequiredParameterException` - якщо пошуковий запит порожній
- `OperationFailedException` - якщо пошук не вдався

### prune
```php
public function prune(array $filters = []): array
```
Видаляє невикористані образи.

#### Параметри:
- `$filters` - Фільтри для вибору образів для очищення:
  - `dangling` (bool) - Видалити тільки висячі образи (образи без тегів)
  - `until` (string) - Видалити образи, створені до вказаного часу
  - `label` (string) - Видалити образи з вказаними мітками

#### Повертає:
- Масив з результатом очищення, включаючи звільнене місце

#### Винятки:
- `InvalidParameterValueException` - якщо фільтри неприпустимі
- `OperationFailedException` - якщо очищення не вдалося

## Приклади використання

### Отримання списку образів
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$images = $client->image()->list(['all' => true]);

foreach ($images as $image) {
    $tags = $image['RepoTags'] ?? ['<none>:<none>'];
    echo "Образ: " . implode(', ', $tags) . ", розмір: " . $image['Size'] . " байт\n";
}
```

### Завантаження образу з Docker Hub
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$result = $client->image()->create('nginx', 'latest');
echo "Образ nginx:latest успішно завантажений\n";
```

### Маркування та видалення образу
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$imageName = 'nginx:latest';

if ($client->image()->exists($imageName)) {
    // Маркуємо образ новим іменем
    $client->image()->tag($imageName, 'my-nginx', 'v1');
    
    // Видаляємо оригінальний образ
    $client->image()->remove($imageName);
    
    echo "Образ {$imageName} перейменовано в my-nginx:v1 та видалено оригінал\n";
} 
```