# Документація класу Container

## Опис
`Container` - це клас для роботи з контейнерами Docker через API. Він надає методи для створення, запуску, зупинки, перезапуску, видалення та управління контейнерами Docker.

## Простір імен
`Sangezar\DockerClient\Api`

## Успадкування
Клас `Container` успадковується від `AbstractApi` та реалізує інтерфейс `ContainerInterface`.

## Константи

### Сигнали для контейнерів
```php
public const SIGNAL_HUP = 'SIGHUP';
public const SIGNAL_INT = 'SIGINT';
public const SIGNAL_QUIT = 'SIGQUIT';
public const SIGNAL_ILL = 'SIGILL';
public const SIGNAL_TRAP = 'SIGTRAP';
public const SIGNAL_ABRT = 'SIGABRT';
public const SIGNAL_BUS = 'SIGBUS';
public const SIGNAL_FPE = 'SIGFPE';
public const SIGNAL_KILL = 'SIGKILL';
public const SIGNAL_USR1 = 'SIGUSR1';
public const SIGNAL_SEGV = 'SIGSEGV';
public const SIGNAL_USR2 = 'SIGUSR2';
public const SIGNAL_PIPE = 'SIGPIPE';
public const SIGNAL_ALRM = 'SIGALRM';
public const SIGNAL_TERM = 'SIGTERM';
public const SIGNAL_STKFLT = 'SIGSTKFLT';
public const SIGNAL_CHLD = 'SIGCHLD';
public const SIGNAL_CONT = 'SIGCONT';
public const SIGNAL_STOP = 'SIGSTOP';
public const SIGNAL_TSTP = 'SIGTSTP';
public const SIGNAL_TTIN = 'SIGTTIN';
public const SIGNAL_TTOU = 'SIGTTOU';
public const SIGNAL_URG = 'SIGURG';
public const SIGNAL_XCPU = 'SIGXCPU';
public const SIGNAL_XFSZ = 'SIGXFSZ';
public const SIGNAL_VTALRM = 'SIGVTALRM';
public const SIGNAL_PROF = 'SIGPROF';
public const SIGNAL_WINCH = 'SIGWINCH';
public const SIGNAL_IO = 'SIGIO';
public const SIGNAL_PWR = 'SIGPWR';
public const SIGNAL_SYS = 'SIGSYS';
public const SIGNAL_POLL = 'SIGPOLL';
```

## Методи

### list
```php
public function list(array $parameters = []): array
```
Отримує список контейнерів.

#### Параметри:
- `$parameters` - Масив параметрів для фільтрації результатів:
  - `all` (bool) - Показати всі контейнери (за замовчуванням показуються тільки запущені)
  - `limit` (int) - Обмежити кількість результатів
  - `size` (bool) - Показати розміри контейнерів
  - `filters` (array|string) - Фільтри у форматі JSON

#### Повертає:
- Масив контейнерів

#### Винятки:
- `InvalidParameterValueException` - якщо передані неприпустимі параметри

### create
```php
public function create(ContainerConfig $config): array
```
Створює новий контейнер.

#### Параметри:
- `$config` - Об'єкт `ContainerConfig` з конфігурацією контейнера

#### Повертає:
- Масив з інформацією про створений контейнер

#### Винятки:
- `MissingRequiredParameterException` - якщо відсутні обов'язкові параметри
- `InvalidParameterValueException` - якщо передані неприпустимі параметри
- `InvalidConfigurationException` - якщо конфігурація неприпустима
- `OperationFailedException` - якщо операція не вдалася

### inspect
```php
public function inspect(string $containerId): array
```
Отримує детальну інформацію про контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- Масив з детальною інформацією про контейнер

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `NotFoundException` - якщо контейнер не знайдений

### start
```php
public function start(string $containerId): bool
```
Запускає контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- `true`, якщо контейнер успішно запущений

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `OperationFailedException` - якщо операція не вдалася
- `NotFoundException` - якщо контейнер не знайдений

### stop
```php
public function stop(string $containerId, int $timeout = 10): bool
```
Зупиняє контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$timeout` - Таймаут у секундах перед примусовим зупиненням (за замовчуванням 10 секунд)

#### Повертає:
- `true`, якщо контейнер успішно зупинений

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `OperationFailedException` - якщо операція не вдалася
- `NotFoundException` - якщо контейнер не знайдений

### restart
```php
public function restart(string $containerId, int $timeout = 10): bool
```
Перезапускає контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$timeout` - Таймаут у секундах перед примусовим перезапуском (за замовчуванням 10 секунд)

#### Повертає:
- `true`, якщо контейнер успішно перезапущений

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `OperationFailedException` - якщо операція не вдалася
- `NotFoundException` - якщо контейнер не знайдений

### kill
```php
public function kill(string $id, string $signal = null): bool
```
Вбиває контейнер, надсилаючи сигнал.

#### Параметри:
- `$id` - ID або ім'я контейнера
- `$signal` - Сигнал для надсилання (за замовчуванням SIGKILL)

#### Повертає:
- `true`, якщо контейнер успішно вбитий

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `InvalidParameterValueException` - якщо сигнал неприпустимий
- `OperationFailedException` - якщо операція не вдалася
- `NotFoundException` - якщо контейнер не знайдений

### remove
```php
public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): bool
```
Видаляє контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$force` - Форсувати видалення, навіть якщо контейнер запущений (за замовчуванням `false`)
- `$removeVolumes` - Видалити пов'язані томи (за замовчуванням `false`)

#### Повертає:
- `true`, якщо контейнер успішно видалений

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `OperationFailedException` - якщо операція не вдалася
- `NotFoundException` - якщо контейнер не знайдений

### logs
```php
public function logs(string $containerId, array $parameters = []): array
```
Отримує логи контейнера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$parameters` - Масив параметрів для фільтрації логів:
  - `follow` (bool) - Слідкувати за логами
  - `stdout` (bool) - Включити stdout (за замовчуванням `true`)
  - `stderr` (bool) - Включити stderr (за замовчуванням `true`)
  - `since` (int) - Мітка часу початку логів
  - `until` (int) - Мітка часу кінця логів
  - `timestamps` (bool) - Включити мітки часу
  - `tail` (string|int) - Кількість рядків для виведення з кінця логів

#### Повертає:
- Масив з логами контейнера

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `InvalidParameterValueException` - якщо передані неприпустимі параметри
- `NotFoundException` - якщо контейнер не знайдений

### stats
```php
public function stats(string $containerId, bool $stream = false): array
```
Отримує статистику використання ресурсів контейнера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$stream` - Отримувати статистику в реальному часі (за замовчуванням `false`)

#### Повертає:
- Масив з статистикою використання ресурсів

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `NotFoundException` - якщо контейнер не знайдений

### exists
```php
public function exists(string $containerId): bool
```
Перевіряє, чи існує контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- `true`, якщо контейнер існує, `false` - якщо ні

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

## Приклади використання

### Отримання списку контейнерів
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$containers = $client->container()->list(['all' => true]);

foreach ($containers as $container) {
    echo "Контейнер: {$container['Names'][0]}, статус: {$container['Status']}\n";
}
```

### Створення та запуск контейнера
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Config\ContainerConfig;

$client = DockerClient::createUnix();

$config = new ContainerConfig();
$config->setName('my-container')
       ->setImage('nginx:latest')
       ->setExposedPorts(['80/tcp' => []])
       ->setHostConfig([
           'PortBindings' => [
               '80/tcp' => [['HostPort' => '8080']]
           ]
       ]);

$container = $client->container()->create($config);
$client->container()->start($container['Id']);

echo "Контейнер створено з ID: {$container['Id']}\n";
```

### Зупинка та видалення контейнера
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$containerId = 'my-container';

if ($client->container()->exists($containerId)) {
    $client->container()->stop($containerId);
    $client->container()->remove($containerId);
    echo "Контейнер {$containerId} зупинено та видалено\n";
} 
```