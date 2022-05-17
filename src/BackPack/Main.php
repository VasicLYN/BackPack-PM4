<?php

namespace BackPack;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;

class Main extends PluginBase
{

    use SingletonTrait;

    public Config $config;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        $this->config = new Config($this->getDataFolder()."backups.yml", Config::YAML);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() === "backpack"){
            if ($sender->hasPermission("backpack.use")){
                if ($sender instanceof Player) {
                    $this->openBackPack($sender);
                }
            }
        }
        return true;
    }

    public function openBackPack(Player $player): void
    {
        $items = [];
        $items2 = [];
        $alldata = $this->config->getAll();
        if (isset($alldata[$player->getName()])) {
            foreach ($alldata[$player->getName()] as $slot => $item) {
                $items[$slot] = Item::jsonDeserialize($item);
            }
        }
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName(TextFormat::colorize("&c&lBackPack"));
        $menu->getInventory()->setContents($items);
        $menu->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory) use ($items2): void {
            $contents = $inventory->getContents();
            foreach ($contents as $slot => $content){
                $items2[$slot] = $content->jsonSerialize();
            }
            $this->config->set($player->getName(), $items2);
            $this->config->save();
        });
        $menu->send($player);
    }

}