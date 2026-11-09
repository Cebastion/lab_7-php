# Лабораторна робота № 7

**Тема:** Laravel. Моделі, міграції та зв'язки
**Проєкт:** REST API для системи управління завданнями (Task Tracker)

## Мета

Ознайомитися з основами проєктування бази даних у Laravel: створити міграції зі зв'язками зовнішніх ключів, налаштувати Eloquent-моделі, визначити зв'язки між ними та згенерувати тестові дані за допомогою фабрик і сідерів.

## Структура файлів

```
app/Models/
    User.php  Project.php  Task.php  Comment.php  File.php
database/migrations/
    2025_01_01_000001_create_projects_table.php
    2025_01_01_000002_create_tasks_table.php
    2025_01_01_000003_create_comments_table.php
    2025_01_01_000004_create_files_table.php
database/factories/
    ProjectFactory.php  TaskFactory.php  CommentFactory.php
database/seeders/
    DatabaseSeeder.php
```

Таблиця `users` та `UserFactory` — стандартні для Laravel, змінено лише модель `User` (додано зв'язки).

## Хід роботи

### Крок 0. Підготовка

```bash
composer create-project laravel/laravel task-tracker
cd task-tracker
```

Налаштувати підключення до БД у `.env` (за замовчуванням SQLite):

```
DB_CONNECTION=sqlite
```

### Крок 1. Міграції

Створення файлів:

```bash
php artisan make:migration create_projects_table
php artisan make:migration create_tasks_table
php artisan make:migration create_comments_table
php artisan make:migration create_files_table
```

Таблиця `users` використовує стандартну міграцію Laravel.

| Таблиця | Поля | Особливості |
|---|---|---|
| `projects` | id, name, description (nullable), user_id, timestamps | `user_id` → `users`, `cascadeOnDelete` |
| `tasks` | id, title, description (nullable), status, project_id, author_id, assigned_to (nullable), due_date (nullable), timestamps | `status` — enum `new / in_progress / done`, default `new`; `project_id` — cascade; `author_id` і `assigned_to` → `users` |
| `comments` | id, task_id, user_id, content, timestamps | `task_id` і `user_id` — cascade |
| `files` | id, file_path, original_name, fileable_id, fileable_type, timestamps | поліморфна таблиця через `$table->morphs('fileable')` |

Ключові фрагменти:

```php
// tasks
$table->enum('status', ['new', 'in_progress', 'done'])->default('new');
$table->foreignId('project_id')->constrained()->cascadeOnDelete();
$table->foreignId('author_id')->constrained('users');
$table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
$table->dateTime('due_date')->nullable();

// files
$table->morphs('fileable');
```

Примітка: для `assigned_to` додано `nullOnDelete()` — при видаленні виконавця задача не видаляється, а стає непризначеною. Для `author_id` каскад не задавався, оскільки в завданні він не вимагається.

Запуск:

```bash
php artisan migrate
```

### Крок 2. Моделі та зв'язки

Створення моделей:

```bash
php artisan make:model Project
php artisan make:model Task
php artisan make:model Comment
php artisan make:model File
```

У кожній моделі задано `$fillable`.

| Модель | Зв'язок | Метод | Тип |
|---|---|---|---|
| **User** | створені проєкти | `projects()` | `hasMany(Project::class)` |
| | створені задачі | `authoredTasks()` | `hasMany(Task::class, 'author_id')` |
| | призначені задачі | `assignedTasks()` | `hasMany(Task::class, 'assigned_to')` |
| | коментарі | `comments()` | `hasMany(Comment::class)` |
| | файли/аватари | `files()` | `morphMany(File::class, 'fileable')` |
| **Project** | творець | `user()` | `belongsTo(User::class)` |
| | задачі | `tasks()` | `hasMany(Task::class)` |
| | файли | `files()` | `morphMany(File::class, 'fileable')` |
| **Task** | проєкт | `project()` | `belongsTo(Project::class)` |
| | автор | `author()` | `belongsTo(User::class, 'author_id')` |
| | виконавець | `assignee()` | `belongsTo(User::class, 'assigned_to')` |
| | коментарі | `comments()` | `hasMany(Comment::class)` |
| | файли | `files()` | `morphMany(File::class, 'fileable')` |
| **Comment** | задача | `task()` | `belongsTo(Task::class)` |
| | автор | `user()` | `belongsTo(User::class)` |
| **File** | власник файлу | `fileable()` | `morphTo()` |

Оскільки `Task` має два зв'язки з `User`, для них використано різні назви методів (`author`, `assignee`) та явно вказано зовнішні ключі.

### Крок 3. Фабрики та сідери

```bash
php artisan make:factory ProjectFactory --model=Project
php artisan make:factory TaskFactory --model=Task
php artisan make:factory CommentFactory --model=Comment
```

Генерація даних через Faker:

- **ProjectFactory** — `name` (sentence), `description` (paragraph, optional), `user_id` (User::factory()).
- **TaskFactory** — `title`, `description`, випадковий `status`, `project_id`, `author_id`, `assigned_to` (nullable), `due_date`.
- **CommentFactory** — `task_id`, `user_id`, `content` (paragraph).

Логіка `DatabaseSeeder`:

1. Створюється 3 користувачі.
2. Для кожного користувача створюється 2 проєкти.
3. Для кожного проєкту створюється 5 задач; статуси чергуються `new → in_progress → done → new → in_progress`, тож присутні всі три статуси. Автор задачі — власник проєкту, виконавець — випадковий користувач.
4. До кожної задачі додається 2–3 коментарі від різних користувачів (`$users->random(rand(2, 3))` повертає унікальних).

Запуск:

```bash
php artisan db:seed
# або з чистої бази:
php artisan migrate:fresh --seed
```

### Перевірка результату

Очікувана кількість записів після сідування:

| Таблиця | Кількість |
|---|---|
| users | 3 |
| projects | 3 × 2 = 6 |
| tasks | 6 × 5 = 30 |
| comments | від 30 × 2 = 60 до 30 × 3 = 90 |

Перевірка зв'язків через `php artisan tinker`:

```php
User::count();                                   // 3
Project::count();                                // 6
Task::count();                                   // 30
Comment::count();                                // 60..90

$task = Task::with(['project', 'author', 'assignee', 'comments.user'])->first();
$task->project->name;
$task->author->name;
$task->assignee?->name;
$task->comments->count();

User::first()->projects()->count();              // 2
Task::where('status', 'done')->count();

// поліморфний зв'язок
$project = Project::first();
$project->files()->create(['file_path' => 'files/spec.pdf', 'original_name' => 'spec.pdf']);
File::first()->fileable;                         // повертає Project
```

## Висновки

У ході роботи створено структуру БД для Task Tracker з чотирьох власних таблиць із зовнішніми ключами та каскадним видаленням, реалізовано поліморфну таблицю `files` через `morphs()`, описано Eloquent-моделі з усіма зв'язками (`hasMany`, `belongsTo`, `morphMany`, `morphTo`) та `$fillable`, а також налаштовано фабрики й сідер, що генерують узгоджені тестові дані для перевірки зв'язків.
