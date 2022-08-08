<?php
namespace Drupal\my_module\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\my_module\MyService;
use Symfony\Component\DependencyInjection\ContainerInterface;
class MyController extends ControllerBase {
    protected $my_service;
    public static function create(ContainerInterface $container) {
        return new static (
            $container->get('my_module.myservice')
        );
    }
    public function __construct(MyService $my_service) {
        $this->my_service = $my_service;
    }
    public function index() {
        return [
            '#type' => 'markup',
            '#markup' => $this->my_service->myLogic()
        ];
    }
}
