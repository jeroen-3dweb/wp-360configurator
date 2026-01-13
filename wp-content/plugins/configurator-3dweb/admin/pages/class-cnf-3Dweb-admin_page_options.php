<?php

class CNF_3DWeb_ADMIN_OPTIONS extends CNF_3DWeb_ADMIN_PAGE_ABSTRACT
{
    const PATH = 'cnf-3dweb-options';

    protected $pageTitle = 'Options';
    protected $menuTitle = 'Options';
    protected $template = 'cnf-3dweb-admin-options';

    const CONFIGURATOR_USE_THREESIXTY = 'CNF3DWEB_use_threesixty';

    protected $fields = [
        self::CONFIGURATOR_USE_THREESIXTY
    ];
}