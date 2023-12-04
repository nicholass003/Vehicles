<?php

/*
 *       _      _           _                ___   ___ ____
 *      (_)    | |         | |              / _ \ / _ \___ \
 * _ __  _  ___| |__   ___ | | __ _ ___ ___| | | | | | |__) |
 *| '_ \| |/ __| '_ \ / _ \| |/ _` / __/ __| | | | | | |__ <
 *| | | | | (__| | | | (_) | | (_| \__ \__ \ |_| | |_| |__) |
 *|_| |_|_|\___|_| |_|\___/|_|\__,_|___/___/\___/ \___/____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author nicholass003
 * @link https://github.com/nicholass003/
 *
 */

declare(strict_types=1);

namespace nicholass003\vehicles;

use nicholass003\vehicles\entity\vehicle\VehicleBoat;
use nicholass003\vehicles\entity\vehicle\VehicleChestBoat;
use nicholass003\vehicles\event\EventListener;
use nicholass003\vehicles\item\ChestBoat;
use nicholass003\vehicles\item\ExtraVanillaItem;
use nicholass003\vehicles\task\RegisterItemsTask;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\BoatType;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\World;

class Main extends PluginBase{
    use SingletonTrait;

    protected function onLoad() : void{
        $this->registerEntities();

        self::setInstance($this);
        
        $this->getServer()->getAsyncPool()->submitTask(new RegisterItemsTask());
    }

    protected function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function registerEntities() : void{
        EntityFactory::getInstance()->register(VehicleBoat::class, function(World $world, CompoundTag $nbt) : VehicleBoat{
            return new VehicleBoat(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['Boat', 'minecraft:boat']);
        EntityFactory::getInstance()->register(VehicleChestBoat::class, function(World $world, CompoundTag $nbt) : VehicleChestBoat{
            return new VehicleChestBoat(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['Chest Boat', 'minecraft:chest_boat']);
    }

    public static function registerItems() : void{
        $items = ExtraVanillaItem::getAll();
        foreach($items as $item){
            if($item instanceof ChestBoat){
                self::registerItem((match($item->getType()){
                    BoatType::OAK() => ItemTypeNames::OAK_CHEST_BOAT,
                    BoatType::SPRUCE() => ItemTypeNames::SPRUCE_CHEST_BOAT,
                    BoatType::BIRCH() => ItemTypeNames::BIRCH_CHEST_BOAT,
                    BoatType::JUNGLE() => ItemTypeNames::JUNGLE_CHEST_BOAT,
                    BoatType::ACACIA() => ItemTypeNames::ACACIA_CHEST_BOAT,
                    BoatType::DARK_OAK() => ItemTypeNames::DARK_OAK_CHEST_BOAT,
                    BoatType::MANGROVE() => ItemTypeNames::MANGROVE_CHEST_BOAT
                }), $item, (match($item->getType()){
                    BoatType::OAK() => ["oak_chest_boat"],
                    BoatType::SPRUCE() => ["spruce_chest_boat"],
                    BoatType::BIRCH() => ["birch_chest_boat"],
                    BoatType::JUNGLE() => ["jungle_chest_boat"],
                    BoatType::ACACIA() => ["acacia_chest_boat"],
                    BoatType::DARK_OAK() => ["dark_oak_chest_boat"],
                    BoatType::MANGROVE() => ["mangrove_chest_boat"]
                }));
            }
        }
    }

    private static function registerItem(string $id, Item $item, array $stringToItemParserNames) : void{
        GlobalItemDataHandlers::getDeserializer()->map($id, fn() => clone $item);
        GlobalItemDataHandlers::getSerializer()->map($item, fn() => new SavedItemData($id));
        foreach($stringToItemParserNames as $name){
            StringToItemParser::getInstance()->register($name, fn() => clone $item);
        }
    }
}