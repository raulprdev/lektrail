<?php

namespace Completionist;

class WordPressLocale implements Locale {

    public function getCode(): string {
        return get_locale();
    }
}