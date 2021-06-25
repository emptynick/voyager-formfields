<?php

namespace Emptynick\Formfields;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

use Inertia\Inertia;

use Voyager\Admin\Classes\MenuItem;
use Voyager\Admin\Contracts\Plugins\GenericPlugin;
use Voyager\Admin\Contracts\Plugins\Features\Provider\MenuItems;
use Voyager\Admin\Contracts\Plugins\Features\Provider\JS;
use Voyager\Admin\Contracts\Plugins\Features\Provider\ProtectedRoutes;
use Voyager\Admin\Manager\Menu as MenuManager;
use Voyager\Admin\Manager\Breads as BreadManager;

class Formfields implements GenericPlugin, ProtectedRoutes, MenuItems, JS
{
    public $name = 'Formfields';
    public $description = 'Demonstrates all formfields in Voyager II';
    public $repository = 'emptynick/voyager-formfields';
    public $website = 'https://github.com/emptynick/voyager-formfields';

    public function provideJS(): string
    {
        return file_get_contents(realpath(dirname(__DIR__, 1).'/dist/formfields.umd.js'));
    }

    public function provideProtectedRoutes(): void
    {
        Route::get('formfields', function () {
            return Inertia::render('voyager-formfields', [
                'formfields'    => $this->getValuesForAction('add'),
            ])->withViewData('title', 'Formfields');
        })->name('voyager-formfields');

        Route::put('formfields', function (Request $request) {
            $breadmanager = resolve(BreadManager::class);
            foreach ($request->get('data', []) as $type => $content) {
                $value = $content['value'] ?? null;
                /*if ($request->get('action') == 'add') {
                    $value = $breadmanager->getFormfield($type)->store($value);
                } elseif ($request->get('action') == 'edit') {
                    $value = $breadmanager->getFormfield($type)->update(null, $value, $this->getValuesForAction('edit')[$type]['value']);
                }*/
                $this->preferences->set($type.'_value', $value);
                $this->preferences->set($type.'_list_options', $content['list_options'] ?? []);
                $this->preferences->set($type.'_view_options', $content['view_options'] ?? []);
            }
        })->name('voyager-formfields');

        Route::post('formfields', function (Request $request) {
            return $this->getValuesForAction($request->get('action', 'add'));
        })->name('voyager-formfields');

        Route::post('formfields-clear', function (Request $request) {
            return $this->preferences->removeAll();
        })->name('voyager-formfields-clear');
    }

    public function provideMenuItems(MenuManager $menumanager): void
    {
        $item = (new MenuItem('Formfields', 'chip'))->route('voyager.voyager-formfields');

        $menumanager->addItems(
            (new MenuItem())->divider(),
            $item
        );
    }

    private function getValuesForAction($action = 'add')
    {
        $formfields = [];
        $breadmanager = resolve(BreadManager::class);

        $breadmanager->getFormfields()->each(function ($formfield) use (&$formfields, $breadmanager, $action) {
            $add = $breadmanager->getFormfield($formfield['type'])->add();
            $set = $this->preferences->get($formfield['type'].'_value', 'NO_VALUE', false);
            $value = null;
            if ($action !== 'add') {
                $value = $breadmanager->getFormfield($formfield['type'])->{$action}($set == 'NO_VALUE' ? $add : $set);
            } else {
                $value = $set == 'NO_VALUE' ? $add : $set;
            }
            $formfields[$formfield['type']] = [
                'value'         => $value,
                'formfield'     => $formfield,
                'list_options'  => $this->preferences->get($formfield['type'].'_list_options', [], false),
                'view_options'  => $this->preferences->get($formfield['type'].'_view_options', [], false),
            ];
        });

        return $formfields;
    }
}
