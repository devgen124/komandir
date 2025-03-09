# Metrics - Утилиты для создания метрик в CMS и отправки в YooKassa

## Установка

Установите приватный репозиторий composer в composer.json

```json
{
    ...
    "repositories": [
        {"type": "composer", "url": "https://nexus.yooteam.ru/repository/composer-proxy-packagist-org/"},
        {"type": "composer", "url": "https://nexus.yooteam.ru/repository/cmssdk-libraries/"},
        {"packagist.org": false}
    ],
    ...
}
```

Установите последнюю версию с помощью

```bash
$ composer require yoomoney/metrics
```
