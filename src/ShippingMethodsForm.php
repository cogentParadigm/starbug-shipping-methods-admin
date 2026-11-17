<?php
namespace Starbug\ShippingMethods\Admin;

use Psr\Http\Message\ServerRequestInterface;
use Starbug\Db\CollectionFactoryInterface;
use Starbug\Display\DisplayFactoryInterface;
use Starbug\Display\FormDisplay;
use Starbug\Display\Hook\FormHookFactoryInterface;
use Starbug\ShippingMethods\Admin\Db\Query\ShippingMethodsFormCollection;
use Starbug\ShippingMethods\Rate\StrategyFactoryInterface;
use Starbug\Templates\TemplateInterface;

class ShippingMethodsForm extends FormDisplay {
  public $model = "shipping_methods";
  public $collection = ShippingMethodsFormCollection::class;
  public function __construct(
    TemplateInterface $output,
    CollectionFactoryInterface $collections,
    FormHookFactoryInterface $hooks,
    DisplayFactoryInterface $displays,
    ServerRequestInterface $request,
    protected StrategyFactoryInterface $strategyFactory
  ) {
    parent::__construct($output, $collections, $hooks, $displays, $request);
  }
  public function buildDisplay($options) {
    $this->add("name");
    $this->add(["description", "input_type" => "textarea"]);
    $this->add([
      "strategy",
      "input_type" => "select",
      "data-dojo-type" => "starbug/form/Dependency"
    ] + $this->getStrategyOptions());
    $strategies = $this->strategyFactory->getStrategies();
    foreach ($strategies as $type => $strategy) {
      if (empty($strategy["settings"])) {
        continue;
      }
      foreach ($strategy["settings"] as $setting) {
        $field = [$type."_".$setting["name"]];
        $field["id"] = $field[0];
        $field = array_merge($field, $setting);
        if (empty($field["data-dojo-type"])) {
          $field["data-dojo-type"] = "starbug/form/Dependent";
        } elseif (empty($field["data-dojo-mixins"])) {
          $field["data-dojo-mixins"] = "starbug/form/Dependent";
        } else {
          $field["data-dojo-mixins"] .= ",starbug/form/Dependent";
        }
        if (empty($field["data-dojo-props"])) {
          $field["data-dojo-props"] = "";
        } else {
          $field["data-dojo-props"] .= ",";
        }
        $field["data-dojo-props"] .= "values: {'strategy': ['{$type}']}";
        $field["data-depend"] = "strategy";
        $this->add($field);
      }
    }
    $this->add(["position", "info" => "Leave empty to position automatically"]);
    $this->add([
      "minimum_weight",
      "input_type" => "text",
      "data-dojo-type" => "sb/form/Number",
      "data-dojo-props" => "decimals:1, suffix:'lb'"
    ]);
    $this->add([
      "maximum_weight",
      "input_type" => "text",
      "data-dojo-type" => "sb/form/Number",
      "data-dojo-props" => "decimals:1, suffix:'lb'"
    ]);
  }
  protected function getStrategyOptions() {
    $strategies = $this->strategyFactory->getStrategies();
    return [
      "options" => array_column($strategies, "name"),
      "values" => array_keys($strategies)
    ];
  }
}
