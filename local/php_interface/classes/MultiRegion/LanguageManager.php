<?php

declare(strict_types=1);

namespace MultiRegion;

final class LanguageManager
{
    /**
     * Базовые UI-переводы + переводы полей форм/контента.
     */
    public function dictionary(): array
    {
        return [
            'choose_region' => [
                'ru' => 'Выберите регион',
                'en' => 'Choose your region',
                'de' => 'Wählen Sie Ihre Region',
            ],
            'confirm_region' => [
                'ru' => 'Подтвердить',
                'en' => 'Confirm',
                'de' => 'Bestätigen',
            ],
            'change_region' => [
                'ru' => 'Сменить регион',
                'en' => 'Change region',
                'de' => 'Region ändern',
            ],

            // Переводы названий полей
            'field_name' => [
                'ru' => 'Имя',
                'en' => 'Name',
                'de' => 'Name',
            ],
            'field_phone' => [
                'ru' => 'Телефон',
                'en' => 'Phone',
                'de' => 'Telefon',
            ],
            'field_email' => [
                'ru' => 'E-mail',
                'en' => 'E-mail',
                'de' => 'E-Mail',
            ],
            'field_message' => [
                'ru' => 'Сообщение',
                'en' => 'Message',
                'de' => 'Nachricht',
            ],
            'field_region' => [
                'ru' => 'Регион',
                'en' => 'Region',
                'de' => 'Region',
            ],
            'field_city' => [
                'ru' => 'Город',
                'en' => 'City',
                'de' => 'Stadt',
            ],

            // Переводы placeholder-полей
            'placeholder_name' => [
                'ru' => 'Введите ваше имя',
                'en' => 'Enter your name',
                'de' => 'Geben Sie Ihren Namen ein',
            ],
            'placeholder_phone' => [
                'ru' => 'Введите телефон',
                'en' => 'Enter phone number',
                'de' => 'Telefonnummer eingeben',
            ],
            'placeholder_email' => [
                'ru' => 'Введите e-mail',
                'en' => 'Enter e-mail',
                'de' => 'E-Mail eingeben',
            ],
            'placeholder_message' => [
                'ru' => 'Ваше сообщение',
                'en' => 'Your message',
                'de' => 'Ihre Nachricht',
            ],
        ];
    }

    public function translate(string $key, string $lang): string
    {
        $dictionary = $this->dictionary();
        return $dictionary[$key][$lang] ?? $dictionary[$key]['en'] ?? $key;
    }

    /**
     * Перевод набора полей формата: [FIELD_CODE => dictionary_key].
     */
    public function translateFields(array $fieldMap, string $lang): array
    {
        $translated = [];

        foreach ($fieldMap as $fieldCode => $dictionaryKey) {
            $translated[$fieldCode] = $this->translate($dictionaryKey, $lang);
        }

        return $translated;
    }
}
