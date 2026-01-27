<?php

class DWeb_PS_ADMIN_OPTIONS extends DWeb_PS_ADMIN_PAGE_ABSTRACT
{
    const PATH = '3dweb-ps-options';

    protected $pageTitle = 'Options';
    protected $menuTitle = 'Options';
    protected $template = '3dweb-ps-admin-options';

    const CONFIGURATOR_USE_THREESIXTY = 'DWEBPS_use_threesixty';

    protected $fields = [
        self::CONFIGURATOR_USE_THREESIXTY
    ];
}