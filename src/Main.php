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
use nicholass003\vehicles\event\EventListener;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

class Main extends PluginBase{

    protected function onLoad() : void{
        $this->registerEntities();
    }

    protected function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function registerEntities() : void{
        EntityFactory::getInstance()->register(VehicleBoat::class, function(World $world, CompoundTag $nbt) : VehicleBoat{
            return new VehicleBoat(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['Boat', 'minecraft:boat']);
    }
}