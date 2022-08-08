<?php

namespace Drupal\plugindear\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
* Provides a Feature News block.
* @Block(
*   id = "learning_featurenews",
*   admin_label = @Translation("Feature News"),
*   category = @Translation("Custom")
* )
*/ 

class Employee extends BlockBase
{
    /**
     * {@inheritdoc}
     * 
     */

     public function build()
     {
         $build['content']  = [
             '#markup' => $this->t('Rohit Rawat'),
         ];
         return $build;
     }
}

?>