<?php

namespace angga7togk\quicklyenchantments;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class QECommand extends Command
{
  /** @var array<string, Enchantment> $vanillaEnchantments */
  private array $vanillaEnchantments = [];
  public function __construct(private readonly QuicklyEnchantments $plugin)
  {
    parent::__construct('quicklyenchantments', 'enchant item in your hand with lots of enchantments quickly', '/qe <enchantment:level>[]', ['qe', 'enchants']);
    $this->setPermission('quicklyenchantments.command');

    $this->vanillaEnchantments = VanillaEnchantments::getAll();
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args)
  {
    if (!$this->testPermission($sender)) {
      return;
    }
    if (!$sender instanceof Player) {
      $sender->sendMessage(TextFormat::RED . 'Please run this command in-game');
      return;
    }

    if (count($args) === 0) {
      $sender->sendMessage(TextFormat::RED . $this->getUsage());
      return;
    }
    if ($args[0] === 'help') {
      $sender->sendMessage(TextFormat::GREEN . $this->getUsage());
      $sender->sendMessage(TextFormat::GREEN . 'Example: /qe unbreaking:3 protection:5 vampire:1');
      return;
    }

    $item = $sender->getInventory()->getItemInHand();
    if ($item->isNull()) {
      $sender->sendMessage(TextFormat::RED . 'You must hold an item in your hand to use this command');
      return;
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

  private function getEnchantmentByName(string $enchantName): ?Enchantment
  {
    $enchant = null;
    if ($this->plugin->piggyCustomEnchantsSuported) {
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
