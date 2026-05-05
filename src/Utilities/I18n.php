<?php

namespace App\Utilities;

class I18n
{
    private static ?self $instance = null;
    private string $locale = 'en';
    private array $translations = [];
    private const SUPPORTED_LOCALES = ['en', 'fr'];
    private const DEFAULT_LOCALE = 'en';

    private function __construct()
    {
        $this->loadTranslations();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load translations for all supported locales
     */
    private function loadTranslations(): void
    {
        foreach (self::SUPPORTED_LOCALES as $locale) {
            $file = __DIR__ . '/../../locales/' . $locale . '.json';
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->translations[$locale] = json_decode($content, true) ?? [];
            } else {
                $this->translations[$locale] = [];
            }
        }
    }

    /**
     * Set the current locale
     */
    public function setLocale(string $locale): bool
    {
        if (in_array($locale, self::SUPPORTED_LOCALES)) {
            $this->locale = $locale;
            return true;
        }
        return false;
    }

    /**
     * Get the current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get all supported locales
     */
    public static function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * Check if a locale is supported
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES);
    }

    /**
     * Translate a key
     * 
     * @param string $key Dot-separated key (e.g., 'nav.home')
     * @param array $params Optional parameters for sprintf-style replacement
     * @return string Translated text or key if not found
     */
    public function trans(string $key, array $params = []): string
    {
        $keys = explode('.', $key);
        $translation = $this->translations[$this->locale] ?? [];

        foreach ($keys as $k) {
            if (!isset($translation[$k])) {
                // Fallback to default locale
                $translation = $this->translations[self::DEFAULT_LOCALE] ?? [];
                foreach ($keys as $k2) {
                    if (!isset($translation[$k2])) {
                        return $key; // Key not found
                    }
                    $translation = $translation[$k2];
                }
                break;
            }
            $translation = $translation[$k];
        }

        if (!is_string($translation)) {
            return $key;
        }

        if (!empty($params)) {
            return vsprintf($translation, $params);
        }

        return $translation;
    }

    /**
     * Alias for trans()
     */
    public function t(string $key, array $params = []): string
    {
        return $this->trans($key, $params);
    }

    /**
     * Get all translations for current locale
     */
    public function getAllTranslations(): array
    {
        return $this->translations[$this->locale] ?? [];
    }

    /**
     * Detect locale from Accept-Language header
     */
    public function detectLocaleFromHeader(?string $acceptLanguage): string
    {
        if (!$acceptLanguage) {
            return self::DEFAULT_LOCALE;
        }

        // Parse Accept-Language header
        $languages = [];
        $languagePieces = explode(',', $acceptLanguage);

        foreach ($languagePieces as $piece) {
            $piece = trim($piece);
            if (strpos($piece, ';') !== false) {
                list($lang, $quality) = explode(';', $piece);
                $languages[trim($lang)] = (float) str_replace('q=', '', trim($quality));
            } else {
                $languages[$piece] = 1.0;
            }
        }

        arsort($languages);

        foreach ($languages as $lang => $quality) {
            $shortLang = substr($lang, 0, 2);
            if (self::isSupported($shortLang)) {
                return $shortLang;
            }
        }

        return self::DEFAULT_LOCALE;
    }

    /**
     * Set locale from session or cookie
     */
    public function setLocaleFromSession(?string $sessionLocale): void
    {
        if ($sessionLocale && self::isSupported($sessionLocale)) {
            $this->locale = $sessionLocale;
        }
    }
}