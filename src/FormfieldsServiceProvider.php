<?php

namespace Emptynick\Formfields;

use Illuminate\Support\ServiceProvider;
use Voyager\Admin\Facades\Voyager as Voyager;
use Voyager\Admin\Manager\Plugins as PluginManager;

class FormfieldsServiceProvider extends ServiceProvider
{
    public function boot(PluginManager $pluginmanager)
    {
        $pluginmanager->addPlugin(\Emptynick\Formfields\Formfields::class);
        $this->loadTranslationsFrom(realpath(__DIR__.'/../resources/lang'), 'formfields');
        Voyager::addTranslations('formfields', 'formfields');
    }

    public function register()
    {
        
    }
}