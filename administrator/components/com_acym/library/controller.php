<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class acymController extends acymObject
{
    var $pkey = '';
    var $table = '';
    var $name = '';
    var $defaulttask = 'listing';
    var $breadcrumb = [];
    var $loadScripts = [];
    var $currentClass = null;
    var $authorizedFrontTasks = [];
    var $urlFrontMenu = '';

    public function __construct()
    {
        parent::__construct();

        $classname = get_class($this);
        $ctrlpos = strpos($classname, 'Controller');
        $this->name = strtolower(substr($classname, 0, $ctrlpos));
        $this->currentClass = acym_get('class.'.rtrim(str_replace('front', '', $this->name), 's'));

        $this->breadcrumb['AcyMailing'] = acym_completeLink('dashboard');
    }

    public function loadScripts($task)
    {
        if (empty($this->loadScripts)) return;

        $scripts = [];
        if (!empty($this->loadScripts['all'])) {
            $scripts = $this->loadScripts['all'];
        }

        if (!empty($task) && !empty($this->loadScripts[$task])) {
            $scripts = array_merge($scripts, $this->loadScripts[$task]);
        }

        if (empty($scripts)) return;

        if (in_array('colorpicker', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/spectrum.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'spectrum.min.js'));
            acym_addStyle(false, ACYM_CSS.'libraries/spectrum.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'spectrum.min.css'));
        }

        if (in_array('datepicker', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/moment.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'moment.min.js'));
            acym_addScript(false, ACYM_JS.'libraries/rome.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'rome.min.js'));
            acym_addScript(false, ACYM_JS.'libraries/material-datetime-picker.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'material-datetime-picker.min.js'));
            acym_addStyle(false, ACYM_CSS.'libraries/material-datetime-picker.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'material-datetime-picker.min.css'));
        }

        if (in_array('thumbnail', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/dom-to-image.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'dom-to-image.min.js'));
        }

        if (in_array('foundation-email', $scripts)) {
            acym_addStyle(false, ACYM_CSS.'libraries/foundation_email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css'));
            acym_addStyle(true, acym_getEmailCssFixes());
        }

        if (in_array('introjs', $scripts)) {
            acym_addStyle(false, ACYM_CSS.'libraries/introjs.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'introjs.min.css'));
            acym_addScript(false, ACYM_JS.'libraries/intro.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'intro.min.js'));
        }

        if (in_array('parse-css', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/parse-css.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'parse-css.min.js'));
        }

        if (in_array('editor-wysid', $scripts)) {
            acym_addStyle(false, ACYM_CSS.'editorWYSID.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'editorWYSID.min.css'));
            acym_addScript(false, ACYM_JS.'editor_wysid_utils.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'editor_wysid_utils.min.js'));
        }

        if (!empty($scripts['vue-applications'])) {
            acym_addScript(false, ACYM_JS.'libraries/vuejs.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'vuejs.min.js'));
            acym_addScript(false, ACYM_JS.'libraries/vue-infinite-scroll.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'vue-infinite-scroll.min.js'));
            acym_addScript(false, ACYM_JS.'vue/vue_components.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'vue'.DS.'vue_components.min.js'));
            foreach ($scripts['vue-applications'] as $script) {
                acym_addScript(false, ACYM_JS.'vue/'.$script.'.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'vue'.DS.$script.'.min.js'));
            }
        }

        if (in_array('vue-prism-editor', $scripts)) {
            acym_addScript(false, ACYM_JS.'libraries/prism-editor.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'prism-editor.min.js'));
            acym_addScript(false, ACYM_JS.'libraries/prism.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'prism.min.js'));
            acym_addScript(false, 'https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.10.2/beautify-html.js');
            acym_addStyle(false, ACYM_CSS.'libraries/prism.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'prism.min.css'));
        }
    }

    public function setDefaultTask($task)
    {
        $this->defaulttask = $task;
    }

    public function getName()
    {
        return $this->name;
    }

    public function display($data = [])
    {
        $viewFolder = 'view';
        if (acym_isAdmin()) {
            if (!acym_isNoTemplate()) {
                $header = acym_get('helper.header');
                $data['header'] = $header->display($this->breadcrumb);
            }
        } else {
            $viewFolder = 'view_front';
        }

        acym_prepareFrontViewDisplay($this->name);

        $view = acym_get($viewFolder.'.'.$this->getName());
        $view->display($data);
    }

    public function cancel()
    {
        acym_setVar('layout', 'listing');
        $this->display();
    }

    public function listing()
    {
        acym_setVar('layout', 'listing');

        return $this->display();
    }

    public function edit()
    {
        $nextstep = acym_getVar('string', 'nextstep', '');
        $step = acym_getVar('string', 'step', '');
        if (empty($nextstep)) {
            $nextstep = $step;
        }

        if (empty($nextstep)) {
            acym_setVar('layout', 'edit');

            return $this->display();
        } else {
            acym_setVar('step', $nextstep);

            return $this->$nextstep();
        }
    }

    public function apply()
    {
        $this->store();

        return $this->edit();
    }

    public function add()
    {
        acym_setVar('cid', []);
        acym_setVar('layout', 'form');

        return $this->display();
    }

    public function save()
    {
        $step = acym_getVar('string', 'step', '');

        if (!empty($step)) {
            $saveMethod = 'save'.ucfirst($step);
            if (!method_exists($this, $saveMethod)) {
                die('Save method '.$saveMethod.' not found');
            }

            return $this->$saveMethod();
        }

        if (method_exists($this, 'store')) $this->store();

        return $this->listing();
    }

    public function delete()
    {
        acym_checkToken();
        $ids = acym_getVar('array', 'elements_checked', []);
        $allChecked = acym_getVar('string', 'checkbox_all');
        $currentPage = explode('_', acym_getVar('string', 'page'));
        $pageNumber = acym_getVar('int', end($currentPage).'_pagination_page');

        if (!empty($ids) && !empty($this->currentClass)) {
            $this->currentClass->delete($ids);
            if ($allChecked == 'on') {
                acym_setVar(end($currentPage).'_pagination_page', $pageNumber - 1);
            }
        }

        $this->listing();
    }

    public function setActive()
    {
        acym_checkToken();
        $ids = acym_getVar('array', 'elements_checked', []);

        if (!empty($ids)) {
            $this->currentClass->setActive($ids);
        }

        $this->listing();
    }

    public function setInactive()
    {
        acym_checkToken();
        $ids = acym_getVar('array', 'elements_checked', []);

        if (!empty($ids)) {
            $this->currentClass->setInactive($ids);
        }

        $this->listing();
    }

    protected function getMatchingElementsFromData($requestData, &$status, &$page, $class = '')
    {
        $classElement = empty($class) ? $this->currentClass : acym_get('class.'.$class);
        $matchingElement = $classElement->getMatchingElements($requestData);

        if (empty($matchingElement['elements'])) {
            if (!empty($status) && empty($requestData['search']) && empty($requestData['tag'])) {
                $status = '';
                $requestData['status'] = $status;
            } elseif (!empty($requestData['offset'])) {
                $page = 1;
                $requestData['offset'] = 0;
            }
            $matchingElement = $classElement->getMatchingElements($requestData);
        }

        return $matchingElement;
    }

    public function checkTaskFront($task)
    {
        if (!in_array($task, $this->authorizedFrontTasks)) {
            acym_menuOnly($this->urlFrontMenu);
            $currentUserid = acym_currentUserId();
            if (empty($currentUserid)) {
                acym_askLog(true, 'ACYM_ONLY_LOGGED', 'info');

                return;
            }
        }
        $this->$task();
    }
}

