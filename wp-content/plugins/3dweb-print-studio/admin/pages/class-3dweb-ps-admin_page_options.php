<?php

class DWeb_PS_ADMIN_OPTIONS extends DWeb_PS_ADMIN_PAGE_ABSTRACT
{
    const PATH = '3dweb-ps-options';

    protected $pageTitle = 'Options';
    protected $menuTitle = 'Options';
    protected $template = '3dweb-ps-admin-options';

    const CONFIGURATOR_USE_THREESIXTY = 'DWEB_PS_use_threesixty';
    const CONFIGURATOR_DEBUG = 'DWEB_PS_configurator_debug';
    const CONFIGURATOR_START_BUTTON_TEXT = 'DWEB_PS_start_button_text';
    const CONFIGURATOR_SESSION_CLOSED_TEXT = 'DWEB_PS_session_closed_text';

    protected $fields = [
        self::CONFIGURATOR_USE_THREESIXTY,
        self::CONFIGURATOR_DEBUG,
        self::CONFIGURATOR_START_BUTTON_TEXT,
        self::CONFIGURATOR_SESSION_CLOSED_TEXT
    ];
}