<?php

namespace angga7togk\quicklyenchantments;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class QuicklyEnchantments extends PluginBase
{
  /** @var array<string, Enchantment> $vanillaEnchantments */
  private array $vanillaEnchantments = [];
  private bool $piggyCustomEnchantsSuported = false;

  public function onEnable(): void
  {
    if ($this->getServer()->getPluginManager()->getPlugin('PiggyCustomEnchants') !== null) $this->piggyCustomEnchantsSuported = true;
    $this->vanillaEnchantments = VanillaEnchantments::getAll();
  }

  public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
  {
    if ($command->getName() === 'quicklyenchantments') {
      if (!$sender instanceof Player) {
        $sender->sendMessage(TextFormat::RED . 'Please run this command in-game');
        return false;
      }

      if (count($args) === 0) {
        $sender->sendMessage(TextFormat::RED . $command->getUsage());
        return false;
      }
      if ($args[0] === 'help') {
        $sender->sendMessage(TextFormat::GREEN . $command->getUsage());
        $sender->sendMessage(TextFormat::GREEN . 'Example: /qe unbreaking:3 protection:5 vampire:1');
        return false;
      }

      $item = $sender->getInventory()->getItemInHand();
      if ($item->isNull()) {
        $sender->sendMessage(TextFormat::RED . 'You must hold an item in your hand to use this command');
        return false;
      }

      /** @var string[] $enchantErrors */
      $enchantErrors = [];

      foreach ($args as $enchantString) {
        $e = explode(':', $enchantString);
        if (count($e) !== 2) {
          $enchantErrors[] = $enchantString;
          continue;
        }
        $enchantName = strtoupper(str_replace(' ', '_', $e[0]));
        $enchantLevel = $e[1];
        $enchant = $this->getEnchantmentByName($enchantName);
        if ($enchant === null) {
          $enchantErrors[] = $enchantString;
          continue;
        }
        try {
          $item->addEnchantment(new EnchantmentInstance($enchant, $enchantLevel));
        } catch (\InvalidArgumentException $e) {
          $enchantErrors[] = $enchantString;
          continue;
        }
      }
      $sender->getInventory()->setItemInHand($item);
      if (count($enchantErrors) > 0) $sender->sendMessage(TextFormat::RED . 'Invalid enchantments: ' . implode(', ', $enchantErrors));
      $sender->sendMessage(TextFormat::GREEN . 'Enchanted item in your hand with ' . count($args) . ' enchantments');
    }
    return true;
  }

  private function getEnchantmentByName(string $enchantName): ?Enchantment
  {
    $enchant = null;
    if ($this->piggyCustomEnchantsSuported) {
      if (CustomEnchantManager::getEnchantmentByName($enchantName) !== null) {
        $enchant = CustomEnchantManager::getEnchantmentByName($enchantName);
      }
    }
    if ($enchant === null && isset($this->vanillaEnchantments[$enchantName])) {
      $enchant = $this->vanillaEnchantments[$enchantName];
    }
    return $enchant;
  }
}
