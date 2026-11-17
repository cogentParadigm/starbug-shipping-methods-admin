<?php
namespace Starbug\ShippingMethods\Admin\Db\Query;

use Starbug\Display\Collection\FormCollection;

class ShippingMethodsFormCollection extends FormCollection {
  protected $model = "shipping_methods";
  public function filterRows($rows) {
    foreach ($rows as &$row) {
      $settings = $this->db->query("shipping_methods_settings")
        ->condition("shipping_methods_id", $row["id"])
        ->all();
      foreach ($settings as $setting) {
        $row[$setting["name"]] = $setting["value"];
      }
    }
    return $rows;
  }
}
