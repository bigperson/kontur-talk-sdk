# Участие в разработке

## Настройка окружения разработки

1. Клонируйте репозиторий:
```bash
git clone git@github.com:bigperson/kontur-talk-sdk.git
cd kontur-talk-sdk
```

2. Установите зависимости:
```bash
composer install
```

3. Запустите тесты:
```bash
vendor/bin/phpunit
```

## Рабочий процесс

1. Создайте ветку для вашей задачи:
```bash
git checkout -b feature/название-функции
```

2. Внесите необходимые изменения, следуя стандартам кодирования.

3. Запустите проверки:
```bash
# Запуск тестов
vendor/bin/phpunit

# Проверка стиля кодирования
vendor/bin/phpcs

# Статический анализ кода
vendor/bin/phpstan analyse
```

4. Исправьте все ошибки и предупреждения.

5. Закоммитьте ваши изменения и отправьте их в репозиторий:
```bash
git add .
git commit -m "Добавлена функция X"
git push origin feature/название-функции
```

6. Создайте Pull Request на GitHub.

## Стандарты кодирования

Код должен соответствовать стандарту PSR-12. Проверить стиль кодирования можно с помощью:
```bash
vendor/bin/phpcs
```

Исправить ошибки автоматически:
```bash
vendor/bin/phpcbf
```

## Публикация новых версий

1. Обновите номер версии в соответствии с [Semantic Versioning](https://semver.org/).

2. Обновите файл CHANGELOG.md, описав изменения.

3. Создайте новый тег и релиз на GitHub:
```bash
git tag v1.0.0
git push origin v1.0.0
```

4. Создайте новый релиз на GitHub через веб-интерфейс с описанием изменений.

После этого GitHub Actions автоматически отправит уведомление на Packagist для обновления пакета. 