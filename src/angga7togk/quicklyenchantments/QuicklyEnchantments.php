<?php

namespace angga7togk\quicklyenchantments;

use pocketmine\plugin\PluginBase;

class QuicklyEnchantments extends PluginBase
{

  public bool $piggyCustomEnchantsSuported = false;

  public function onEnable(): void
  {
    if ($this->getServer()->getPluginManager()->getPlugin('PiggyCustomEnchants') !== null) $this->piggyCustomEnchantsSuported = true;
    $this->getServer()->getCommandMap()->register('quicklyenchantments', new QECommand($this));
  }
}
