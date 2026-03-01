<?php

declare(strict_types=1);

namespace Company\ReviewSync\Service;

use Company\ReviewSync\Config\Option;

final class AutoReplyService
{
    public function generate(): string
    {
        $templates = Option::getReplyTemplates();
        if ($templates === []) {
            return 'Спасибо за ваш отзыв! Мы ценим обратную связь и работаем над качеством сервиса.';
        }

        return $templates[random_int(0, count($templates) - 1)];
    }
}
