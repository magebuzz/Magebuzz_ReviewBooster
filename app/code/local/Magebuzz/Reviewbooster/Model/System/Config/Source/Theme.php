<?php

class Magebuzz_Reviewbooster_Model_System_Config_Source_Theme {
  public function toOptionArray() {
    return array(
      array(
        'value' => 'red',
        'label' => 'Red (default)',
      ),
      array(
        'value' => 'white',
        'label' => 'White',
      ),
      array(
        'value' => 'blackglass',
        'label' => 'Blackglass',
      ),
      array(
        'value' => 'clean',
        'label' => 'Clean',
      ),
    );
  }
}