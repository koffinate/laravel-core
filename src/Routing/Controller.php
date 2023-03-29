<?php

namespace Koffin\Core\Routing;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use JetBrains\PhpStorm\Pure;

/**
 * Controller base class.
 *
 * @author      Yusron Arif <yusron.arif4@gmail.com>
 */
class Controller extends BaseController
{
    /**
     * Authenticated User.
     */
    protected Request $request;

    /**
     * Controller data.
     */
    private array $controllerData = [];

    /**
     * Active menu indicator.
     */
    private array $activeMenu = [];
    private array $activeMenuPack = [];

    /**
     * Page title.
     */
    protected string $pageTitle;

    /**
     * Page Meta.
     */
    private array $pageMeta = [
        'description' => '',
        'keywords' => 'yusronarif, koffinate, laravel',
        'author' => 'Yusron Arif <yusron.arif4::at::gmail.com',
        'generator' => 'Koffinate',
    ];

    /**
     * Breadcrumbs Collection.
     *
     * @var Collection
     */
    private Collection $breadCrumbs;

    /**
     * Reserved variable for the controller.
     */
    private array $reservedVariables = ['activeMenu', 'activeMenuPack', 'pageTitle', 'pageMeta'];

    /**
     * Prefix View Path.
     */
    protected string $prefixView = '';
    protected string $viewPath = '';

    /**
     * type of crud form.
     */
    protected string $crudType = '';

    /**
     * main route name.
     */
    protected string $route = '';

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        auth()->setDefaultDriver('web');
        $this->request = request();
        $this->setBreadCrumb([]);
    }

    /**
     * Serve blade template.
     *
     * @param  string  $view
     *
     * @return View
     */
    protected function view(string $view): View
    {
        $this->share();

        if ($this->prefixView) {
            $view = preg_replace('/(\.)+$/i', '', $this->prefixView).'.'.$view;
        }

        return view($view, $this->controllerData);
    }

    /**
     * Share Blade View.
     *
     * @return void
     */
    private function share(): void
    {
        if (false === array_key_exists('pageTitle', $this->controllerData)) {
            $this->setPageTitle($this->pageTitle ?? 'Untitled');
        }

        $this->setPageMeta('csrf_token', csrf_token());

        $this->controllerData['activeUser'] = auth()->user();
        $this->controllerData['pageMeta'] = $this->pageMeta;
        $this->controllerData['breadCrumbs'] = $this->breadCrumbs;

        $this->controllerData['crudType'] = $this->crudType;
        $this->controllerData['viewPath'] = ($this->viewPath ?: $this->prefixView).'.';
        $this->controllerData['route'] = $this->route;
    }

    /**
     * Set Default Value for Request Input.
     *
     * @param  string|array  $name
     * @param  mixed  $value
     *
     * @return void
     */
    protected function setDefault(string|array $name, mixed $value = null): void
    {
        if (! $this->request->input()) {
            setDefaultRequest($name, $value);
        }
    }

    /**
     * Set controller data.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return \Koffin\Core\Routing\Controller
     *
     * @throws \Exception
     */
    protected function setData(string $name, mixed $value): self
    {
        if (in_array($name, $this->reservedVariables)) {
            throw new Exception("Variable [$name] is reserved by this controller");
        }
        $this->controllerData[$name] = $value;

        return $this;
    }

    /**
     * Set page meta.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return \Koffin\Core\Routing\Controller
     */
    protected function setPageMeta(string $key, mixed $value): self
    {
        $this->pageMeta[$key] = $value;

        return $this;
    }

    /**
     * Set Page title.
     *
     * @param string $title
     *
     * @return \Koffin\Core\Routing\Controller
     */
    protected function setPageTitle(string $title): self
    {
        $this->controllerData['pageTitle'] = $title;

        return $this;
    }

    /**
     * Set Back Link.
     *
     * @param string $link
     *
     * @return \Koffin\Core\Routing\Controller
     */
    protected function setBackLink(string $link): self
    {
        $this->controllerData['backLink'] = $link;

        return $this;
    }

    /**
     * Set Active Menu.
     *
     * @param  string|array  $menu
     *
     * @return void
     */
    protected function setActiveMenu(string|array $menu): void
    {
        $this->activeMenu = (array) $menu;
    }

    /**
     * Add Active Menu.
     *
     * @param  string|array  $menu
     *
     * @return void
     */
    protected function addActiveMenu(string|array $menu): void
    {
        $this->activeMenu = array_merge($this->activeMenu, (array) $menu);
    }

    /**
     * Set BreadCrumb.
     *
     * @param string|array $breadcrumb
     *
     * @return void
     */
    protected function setBreadCrumb(string|array $breadcrumb): void
    {
        $bc = collect();
        if (is_string($breadcrumb)) {
            $bc->add($this->breadCrumbFormat(['title' => $breadcrumb, 'url' => '#']));
        } else {
            foreach ((array) $breadcrumb as $k => $v) {
                if (is_string($v)) {
                    $bc->add($this->breadCrumbFormat($breadcrumb));
                    break;
                }
                $bc->add($this->breadCrumbFormat($v));
            }
        }

        $this->breadCrumbs = $bc;
    }

    /**
     * Add BreadCrumb.
     *
     * @param  string|array  $breadcrumb
     *
     * @return void
     */
    protected function addBreadCrumb(string|array $breadcrumb): void
    {
        if (is_string($breadcrumb)) {
            $this->breadCrumbs->add($this->breadCrumbFormat(['title' => $breadcrumb, 'url' => '#']));
        } else {
            foreach ((array) $breadcrumb as $k => $v) {
                if (is_string($v)) {
                    $this->breadCrumbs->add($this->breadCrumbFormat($breadcrumb));
                    break;
                }
                $this->breadCrumbs->add($this->breadCrumbFormat($v));
            }
        }
    }

    /**
     * Breadcrumb formatter.
     *
     * @param  array  $breadcrumb
     *
     * @return object
     */
    #[Pure]
    private function breadCrumbFormat(array $breadcrumb): object
    {
        $def = ['title' => '', 'url' => '#'];

        return (object) array_merge($def, Arr::only($breadcrumb, ['title', 'url']));
    }
}
