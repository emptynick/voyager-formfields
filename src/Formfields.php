<?php

namespace Emptynick\Formfields;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Voyager\Admin\Classes\Formfield;
use Voyager\Admin\Contracts\Plugins\FormfieldPlugin;

class Formfields implements FormfieldPlugin
{
    public $name = 'Formfields';
    public $description = 'Demonstrates all formfields in Voyager II';
    public $repository = 'emptynick/voyager-formfields';
    public $website = 'https://github.com/emptynick/voyager-formfields';

    public function provideJS(): string
    {
        return file_get_contents(realpath(dirname(__DIR__, 1).'/dist/formfields.umd.js'));
    }
}
