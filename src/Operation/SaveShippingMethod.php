<?php
namespace Starbug\ShippingMethods\Admin\Operation;

use Starbug\Bundle\BundleInterface;
use Starbug\Db\Operation\Save;
use Starbug\Db\DatabaseInterface;
use Starbug\ShippingMethods\Rate\StrategyFactoryInterface;

class SaveShippingMethod extends Save {
  protected $model = "shipping_methods";
  public function __construct(
    protected DatabaseInterface $db,
    protected StrategyFactoryInterface $strategyFactory
  ) {
  }
  public function handle(array $data, BundleInterface $state): BundleInterface {
    $record = [];
    $values = [];
    $settings = $this->getSettings($data["strategy"]);
    foreach ($data as $column => $value) {
      if (in_array($column, $settings)) {
        $values[$column] = $value;
      } else {
        $record[$column] = $value;
      }
    }
    $state = parent::handle($record, $state);
    $id = $record["id"] ?? $this->db->getInsertId($this->model);
    $this->saveSettings($id, $values);
    return $state;
  }
  protected function getSettings($strategy) {
    $strategies = $this->strategyFactory->getStrategies();
    $strategy = $strategies[$strategy];
    if (!empty($strategy["settings"])) {
      return array_column($strategy["settings"], "name");
    }
    return [];
  }
  protected function saveSettings($id, $values) {
    foreach ($values as $column => $value) {
      $conditions = [
        "shipping_methods_id" => $id,
        "name" => $column
      ];
      $upsert = $this->db->query("shipping_methods_settings")
        ->set($conditions + ["value" => $value]);
      $exists = $this->db->query("shipping_methods_settings")
        ->condition($conditions);
      if ($row = $exists->one()) {
        $upsert->condition("id", $row["id"])->update();
      } else {
        $upsert->insert();
      }
    }
  }
}
