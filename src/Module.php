<?php
namespace verbi\yii2ExtendedModule;

use yii\base\BootstrapInterface;
use yii\helpers\Inflector;
use verbi\yii2Helpers\behaviors\base\ComponentBehavior;

/**
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @link https://github.com/verbi/yii2-extended-activerecord/
 * @license https://opensource.org/licenses/GPL-3.0
 */
class Module extends \yii\base\Module implements BootstrapInterface {
    protected $childAction = false;
    public $parentView;
    protected $pluralizeRoute = true;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            ComponentBehavior::className(),
        ]);
    }

    public function getChildAction() {
        return $this->childAction;
    }

    public function runChildAction($route, $params = [], &$parentView = null) {
        $this->childAction = true;
        $this->parentView = &$parentView;
        $return = $this->runAction($route, $params);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app) {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules($this->getUrlRules(), false);
        }
        foreach ($this->getModules() as $name => $value) {
            if ($this->getModule($name) && method_exists($this->getModule($name), 'bootstrap')) {
                $this->getModule($name)->bootstrap($app);
            }
        }
    }

    public function getPath() {
        if ($this->module && method_exists($this->module, 'getPath')) {
            return $this->module->getPath() . '/' . $this->id;
        }
        return $this->id;
    }

    public function getRoute() {
        $id = $this->pluralizeRoute ? Inflector::pluralize($this->id) : $this->id;
        if ($this->module && method_exists($this->module, 'getRoute')) {
            return $this->module->getRoute() . '/' . $id;
        }
        return $id;
    }

    protected function getPrimaryKeyTag($prefix = null) {
        
        if ($prefix === null) {
            $prefix = $this->id . '_';
            
        }
        if($prefix) {
            return '<' . $prefix . 'id:\d>/';
        }
        return '<id:\d>/';
    }

    protected function getUrlRules() {
        $path = null;
        if ($this->module && method_exists($this->module, 'getPath')) {
            $path = $this->module->getPath() . '/';
        }
        $route = null;
        if ($this->module && method_exists($this->module, 'getRoute')) {
            $route = $this->module->getRoute() . '/';
            if (method_exists($this->module, 'getPrimaryKeyTag')) {
                $route .= $this->module->getPrimaryKeyTag();
            }
        }
        $id = $this->pluralizeRoute ? Inflector::pluralize($this->id) : $this->id;
        return [
                    $route . $id => $path . $this->id . '/' . $this->defaultRoute,
                    $route . $id . '/' . $this->getPrimaryKeyTag('') => $path . $this->id . '/' . $this->defaultRoute . '/view',
                    $route . $id . '/<action>' => $path . $this->id . '/' . $this->defaultRoute . '/<action>',
                    $route . $id . '/' . $this->getPrimaryKeyTag('') . '/<action>' => $path . $this->id . '/' . $this->defaultRoute . '/<action>',
                    $route . $id . '/<controller>/<action>' => $path . $this->id . '/<controller>/<action>',
                    $route . $id . '/<controller>/<action>' . $this->getPrimaryKeyTag('') => $path . $this->id . '/<controller>/<action>',
        ];
    }
}
