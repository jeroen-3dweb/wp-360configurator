<?php

class DWeb_PS_ADMIN_OPTIONS extends DWeb_PS_ADMIN_PAGE_ABSTRACT
{
    const PATH = '3dweb-ps-options';

    protected $pageTitle = 'Options';
    protected $menuTitle = 'Options';
    protected $template = '3dweb-ps-admin-options';

    const CONFIGURATOR_USE_THREESIXTY = 'DWEBPS_use_threesixty';
    const CONFIGURATOR_DEBUG = 'DWEBPS_configurator_debug';

    protected $fields = [
        self::CONFIGURATOR_USE_THREESIXTY,
        self::CONFIGURATOR_DEBUG
    ];
}