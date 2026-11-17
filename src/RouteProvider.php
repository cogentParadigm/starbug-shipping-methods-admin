<?php
namespace Starbug\ShippingMethods\Admin;

use Starbug\Admin\RouteProvider as AdminRouteProvider;
use Starbug\ShippingMethods\Admin\Operation\SaveShippingMethod;
use Starbug\Routing\Route;

class RouteProvider extends AdminRouteProvider {
  public function configure(Route $routes) {
    $methods = $this->addCrud($routes, "shipping_methods", [
      "grid" => ShippingMethodsGrid::class,
      "form" => ShippingMethodsForm::class
    ]);
    $this->addCrud($routes, "shipping_rates", [
      "form" => ShippingRatesForm::class
    ]);
    $this->addCrud($routes, "shipping_rates_product_options", [
      "form" => ShippingRatesProductOptionsForm::class
    ]);
    $methods["create"]->onPost(SaveShippingMethod::class);
    $methods["update"]->onPost(SaveShippingMethod::class);
    $methods["adminApi"]->setOption("collection", ShippingMethodsAdminCollection::class);
    // the shipping-methods package registers its own route for the cart selection
    $routes->getRoute("api")->removeRoute("/shipping-methods/select.{format:json}");
  }
}
